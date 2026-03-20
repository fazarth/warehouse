<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

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

        Warehouse::create($validated);

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

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Lokasi gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        // Check if warehouse has stocks
        if ($warehouse->productStocks()->exists()) {
            return redirect()->route('warehouses.index')
                ->with('error', 'Tidak dapat menghapus gudang yang memiliki stok.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Lokasi gudang berhasil dihapus.');
    }
}
