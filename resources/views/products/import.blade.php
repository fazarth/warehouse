@extends('layouts.app')

@section('title', 'Import Produk')
@section('page-title', 'Import Produk')
@section('page-subtitle', 'Upload CSV/XLSX master produk (tidak mengubah stok)')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-sm text-indigo-900">
        <p class="font-semibold mb-1">Aturan import</p>
        <ul class="list-disc pl-5 space-y-1">
            <li>Kolom wajib: <code class="bg-indigo-100 px-1 rounded">sku</code>, <code class="bg-indigo-100 px-1 rounded">name</code>, <code class="bg-indigo-100 px-1 rounded">unit</code></li>
            <li>Opsional: barcode, description, category, min_stock, is_active</li>
            <li><strong>Tidak</strong> menambah stok — master data only</li>
            <li>SKU duplikat: di-skip, kecuali centang “Update jika SKU sudah ada”</li>
        </ul>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.import.template') }}"
               class="inline-flex px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium">
                ⬇ Template CSV
            </a>
            <a href="{{ route('products.index') }}"
               class="inline-flex px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium">
                ← Kembali ke daftar
            </a>
        </div>

        <form method="POST" action="{{ route('products.import.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">File CSV / Excel</label>
                <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
                       class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('file')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="update_existing" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('update_existing') ? 'checked' : '' }}>
                Update jika SKU sudah ada
            </label>
            <div>
                <button type="submit"
                        class="inline-flex px-4 py-2 rounded-xl text-white text-sm font-semibold"
                        style="background:#4f46e5;">
                    Jalankan Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
