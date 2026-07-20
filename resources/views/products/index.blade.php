@extends('layouts.app')

@section('title', 'Produk')
@section('page-title', 'Master Produk')
@section('page-subtitle', 'Kelola data produk')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..." 
                   class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition">
            <select name="category" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition">🔍</button>
        </form>
        @can('product.create')
                <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl transition">
                    <span>➕</span> Tambah Produk
                </a>
                @endcan
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">SKU</th>
                        <th class="px-6 py-4">Nama Produk</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Unit</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-semibold text-teal-600">{{ $product->sku }}</span>
                            @if($product->barcode)
                            <p class="text-xs text-gray-400">{{ $product->barcode }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('products.show', $product) }}" class="font-semibold text-gray-800 hover:text-teal-600">{{ $product->name }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $product->category ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $product->unit }}</td>
                        <td class="px-6 py-4">
                            @if($product->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('products.show', $product) }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">👁️</a>
                                                                @can('product.edit')
                                                                <a href="{{ route('products.edit', $product) }}" class="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition">✏️</a>
                                                                @endcan
                                                                @can('product.delete')
                                                                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">🗑️</button>
                                                                </form>
                                                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📦</div>
                            <p>Belum ada produk</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
