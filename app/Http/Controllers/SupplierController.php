<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

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

        Supplier::create($validated);

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

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->goodsReceipts()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Tidak dapat menghapus supplier yang memiliki riwayat penerimaan.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
