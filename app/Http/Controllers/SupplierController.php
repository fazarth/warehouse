<?php

namespace App\Http\Controllers;

use App\Imports\SuppliersImport;
use App\Models\AuditLog;
use App\Models\Supplier;
use App\Support\TabularExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(15);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:suppliers',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier = Supplier::create($validated);

        AuditLog::log(
            'master.supplier.create',
            Supplier::class,
            $supplier->id,
            'Supplier dibuat: '.$supplier->code,
            null,
            $supplier->only(['code', 'name', 'phone', 'email', 'is_active'])
        );

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:suppliers,code,' . $supplier->id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $before = $supplier->only(['code', 'name', 'address', 'phone', 'email', 'contact_person', 'is_active']);
        $supplier->update($validated);

        AuditLog::log(
            'master.supplier.update',
            Supplier::class,
            $supplier->id,
            'Supplier diubah: '.$supplier->code,
            $before,
            $supplier->only(['code', 'name', 'address', 'phone', 'email', 'contact_person', 'is_active'])
        );

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->goodsReceipts()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Tidak dapat menghapus supplier yang memiliki riwayat penerimaan.');
        }

        $snapshot = $supplier->only(['code', 'name', 'email', 'phone']);
        $id = $supplier->id;
        $supplier->delete();

        AuditLog::log(
            'master.supplier.delete',
            Supplier::class,
            $id,
            'Supplier dihapus: '.$snapshot['code'],
            $snapshot,
            null
        );

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    public function importForm()
    {
        abort_unless(auth()->user()->can('supplier.create'), 403);

        return view('suppliers.import');
    }

    public function importTemplate()
    {
        abort_unless(auth()->user()->can('supplier.view') || auth()->user()->can('supplier.create'), 403);

        return TabularExport::download(
            'suppliers-import-template',
            ['code', 'name', 'address', 'phone', 'email', 'contact_person', 'is_active'],
            [
                ['SUP-DEMO', 'Supplier Demo', 'Jl. Contoh', '08123456789', 'demo@supplier.test', 'Budi', 1],
            ],
            'csv',
            'Template Import Supplier'
        );
    }

    public function import(Request $request)
    {
        abort_unless(auth()->user()->can('supplier.create'), 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            'update_existing' => 'nullable|boolean',
        ]);

        $import = new SuppliersImport($request->boolean('update_existing'));

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }

        AuditLog::log(
            'master.supplier.import',
            null,
            null,
            "Import supplier selesai: +{$import->created} / ~{$import->updated} / skip {$import->skipped}",
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
            return redirect()->route('suppliers.index')
                ->with('success', $msg)
                ->with('import_errors', array_slice($import->errors, 0, 30));
        }

        return redirect()->route('suppliers.index')->with('success', $msg);
    }
}
