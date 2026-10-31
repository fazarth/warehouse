<?php

namespace App\Http\Controllers;

use App\Imports\WarehousesImport;
use App\Models\AuditLog;
use App\Models\Warehouse;
use App\Support\TabularExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->paginate(15);
        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:warehouses',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $warehouse = Warehouse::create($validated);

        AuditLog::log(
            'master.warehouse.create',
            Warehouse::class,
            $warehouse->id,
            'Gudang dibuat: '.$warehouse->code,
            null,
            $warehouse->only(['code', 'name', 'description', 'is_active'])
        );

        return redirect()->route('warehouses.index')
            ->with('success', 'Lokasi gudang berhasil ditambahkan.');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:warehouses,code,' . $warehouse->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $before = $warehouse->only(['code', 'name', 'description', 'is_active']);
        $warehouse->update($validated);

        AuditLog::log(
            'master.warehouse.update',
            Warehouse::class,
            $warehouse->id,
            'Gudang diubah: '.$warehouse->code,
            $before,
            $warehouse->only(['code', 'name', 'description', 'is_active'])
        );

        return redirect()->route('warehouses.index')
            ->with('success', 'Lokasi gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->productStocks()->exists()) {
            return redirect()->route('warehouses.index')
                ->with('error', 'Tidak dapat menghapus gudang yang memiliki stok.');
        }

        $snapshot = $warehouse->only(['code', 'name', 'description', 'is_active']);
        $id = $warehouse->id;
        $warehouse->delete();

        AuditLog::log(
            'master.warehouse.delete',
            Warehouse::class,
            $id,
            'Gudang dihapus: '.$snapshot['code'],
            $snapshot,
            null
        );

        return redirect()->route('warehouses.index')
            ->with('success', 'Lokasi gudang berhasil dihapus.');
    }

    public function importForm()
    {
        abort_unless(auth()->user()->can('warehouse.create'), 403);

        return view('warehouses.import');
    }

    public function importTemplate()
    {
        abort_unless(auth()->user()->can('warehouse.view') || auth()->user()->can('warehouse.create'), 403);

        return TabularExport::download(
            'warehouses-import-template',
            ['code', 'name', 'description', 'location', 'is_active'],
            [
                ['WH-DEMO', 'Gudang Demo', 'Import template', 'Area A', 1],
            ],
            'csv',
            'Template Import Gudang'
        );
    }

    public function import(Request $request)
    {
        abort_unless(auth()->user()->can('warehouse.create'), 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            'update_existing' => 'nullable|boolean',
        ]);

        $import = new WarehousesImport($request->boolean('update_existing'));

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }

        AuditLog::log(
            'master.warehouse.import',
            null,
            null,
            "Import gudang selesai: +{$import->created} / ~{$import->updated} / skip {$import->skipped}",
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
            return redirect()->route('warehouses.index')
                ->with('success', $msg)
                ->with('import_errors', array_slice($import->errors, 0, 30));
        }

        return redirect()->route('warehouses.index')->with('success', $msg);
    }
}
