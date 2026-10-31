@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h3 class="text-lg font-bold text-slate-800">Edit: {{ $product->name }}</h3>
        </div>
        
        <form action="{{ route('products.update', $product) }}" method="POST" class="p-6 space-y-6">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SKU *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                    @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Produk *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">{{ old('description', $product->description) }}</textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit/Satuan *</label>
                                <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <input type="text" name="category" value="{{ old('category', $product->category) }}" list="categories"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                                <datalist id="categories">
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Min Stock</label>
                                <input type="number" min="0" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? 0) }}"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                                <p class="text-xs text-gray-400 mt-1">0 = tidak alert low stock</p>
                            </div>
                        </div>
            
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
            
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition">Simpan</button>
                <a href="{{ route('products.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
