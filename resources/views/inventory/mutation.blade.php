@extends('layouts.app')

@section('title', 'Mutasi Stok')
@section('page-title', 'Mutasi Stok')
@section('page-subtitle', 'Transfer stok antar gudang')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-violet-50 border-b border-purple-100">
            <h3 class="text-lg font-bold text-purple-800">Form Mutasi Stok</h3>
        </div>
        
        <form action="{{ route('inventory.mutation.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Produk *</label>
                <select name="product_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                    @endforeach
                </select>
                @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dari Gudang *</label>
                    <select name="from_warehouse_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                        <option value="">Pilih</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->code }} - {{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ke Gudang *</label>
                    <select name="to_warehouse_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                        <option value="">Pilih</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->code }} - {{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Batch Number</label>
                    <input type="text" name="batch_number" value="{{ old('batch_number') }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition"
                           placeholder="Opsional">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                    <input type="number" name="quantity" step="0.01" min="0.01" value="{{ old('quantity') }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                    @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">{{ old('notes') }}</textarea>
            </div>
            
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition">
                    🔄 Proses Mutasi
                </button>
                <a href="{{ route('inventory.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
