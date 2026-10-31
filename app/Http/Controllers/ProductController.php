<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
use App\Models\AuditLog;
use App\Models\Product;
use App\Support\TabularExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('name')->paginate(15);
        $categories = Product::distinct()->pluck('category')->filter();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Product::distinct()->pluck('category')->filter();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products',
            'barcode' => 'nullable|string|max:50|unique:products',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'category' => 'nullable|string|max:100',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['min_stock'] = (int) ($validated['min_stock'] ?? 0);

        $product = Product::create($validated);

        AuditLog::log(
            'master.product.create',
            Product::class,
            $product->id,
            'Produk dibuat: '.$product->sku,
            null,
            $product->only(['sku', 'barcode', 'name', 'unit', 'category', 'min_stock', 'is_active'])
        );

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['stocks.warehouse']);
        $totalStock = $product->stocks->sum('quantity');

        return view('products.show', compact('product', 'totalStock'));
    }

    public function edit(Product $product)
    {
        $categories = Product::distinct()->pluck('category')->filter();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'category' => 'nullable|string|max:100',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['min_stock'] = (int) ($validated['min_stock'] ?? 0);

        $before = $product->only(['sku', 'barcode', 'name', 'unit', 'category', 'min_stock', 'is_active']);
        $product->update($validated);

        AuditLog::log(
            'master.product.update',
            Product::class,
            $product->id,
            'Produk diubah: '.$product->sku,
            $before,
            $product->only(['sku', 'barcode', 'name', 'unit', 'category', 'min_stock', 'is_active'])
        );

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->stocks()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Tidak dapat menghapus produk yang memiliki stok.');
        }

        $snapshot = $product->only(['sku', 'barcode', 'name', 'unit', 'category', 'min_stock', 'is_active']);
        $id = $product->id;
        $product->delete();

        AuditLog::log(
            'master.product.delete',
            Product::class,
            $id,
            'Produk dihapus: '.$snapshot['sku'],
            $snapshot,
            null
        );

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    public function importForm()
    {
        abort_unless(auth()->user()->can('product.create'), 403);

        return view('products.import');
    }

    public function importTemplate()
    {
        abort_unless(auth()->user()->can('product.view') || auth()->user()->can('product.create'), 403);

        return TabularExport::download(
            'products-import-template',
            ['sku', 'barcode', 'name', 'description', 'unit', 'category', 'min_stock', 'is_active'],
            [
                ['SKU-DEMO-001', '899000000001', 'Contoh Produk', 'Deskripsi opsional', 'PCS', 'General', 10, 1],
            ],
            'csv',
            'Template Import Produk'
        );
    }

    public function import(Request $request)
    {
        abort_unless(auth()->user()->can('product.create'), 403);

        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            'update_existing' => 'nullable|boolean',
        ]);

        $import = new ProductsImport($request->boolean('update_existing'));

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }

        AuditLog::log(
            'master.product.import',
            null,
            null,
            "Import produk selesai: +{$import->created} / ~{$import->updated} / skip {$import->skipped}",
            null,
            [
                'created' => $import->created,
                'updated' => $import->updated,
                'skipped' => $import->skipped,
                'errors' => array_slice($import->errors, 0, 20),
            ]
        );

        $msg = "Import selesai: {$import->created} dibuat, {$import->updated} diupdate, {$import->skipped} dilewati.";
        if ($import->errors) {
            return redirect()->route('products.index')
                ->with('success', $msg)
                ->with('import_errors', array_slice($import->errors, 0, 30));
        }

        return redirect()->route('products.index')->with('success', $msg);
    }
}
