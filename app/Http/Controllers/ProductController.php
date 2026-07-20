<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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

                    Product::create($validated);

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

                    $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->stocks()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Tidak dapat menghapus produk yang memiliki stok.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
