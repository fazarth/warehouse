@extends('layouts.app')

@section('title', 'Produk')
@section('page-title', 'Master Produk')
@section('page-subtitle', 'Kelola data produk')

@section('content')
<div class="space-y-5">
    @if(session('import_errors'))
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">
        <p class="font-semibold mb-2">Beberapa baris import dilewati:</p>
        <ul class="list-disc pl-5 space-y-1">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                   class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 min-w-[12rem]">
            <select name="category" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                <option value="">Semua kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>
        @can('product.create')
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.import') }}"
               class="inline-flex items-center justify-center px-3.5 py-2 rounded-lg text-sm font-semibold border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                ⬆ Import CSV/XLSX
            </a>
            <a href="{{ route('products.create') }}"
               class="inline-flex items-center justify-center px-3.5 py-2 rounded-lg text-sm font-semibold text-white"
               style="background:#4f46e5;">
                + Tambah produk
            </a>
        </div>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Nama produk</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm font-semibold text-indigo-600">{{ $product->sku }}</span>
                            @if($product->barcode)
                            <p class="text-xs text-slate-400">{{ $product->barcode }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-slate-900 hover:text-indigo-600">{{ $product->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $product->category ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $product->unit }}</td>
                        <td class="px-4 py-3">
                            @if($product->is_active)
                            <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                            @else
                            <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-slate-50 text-slate-600 border border-slate-200">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('products.show', $product) }}" class="text-xs font-medium text-slate-600 hover:text-indigo-600 px-2 py-1 rounded-md hover:bg-indigo-50">Detail</a>
                                @can('product.edit')
                                <a href="{{ route('products.edit', $product) }}" class="text-xs font-medium text-slate-600 hover:text-indigo-600 px-2 py-1 rounded-md hover:bg-indigo-50">Edit</a>
                                @endcan
                                @can('product.delete')
                                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:bg-red-50 px-2 py-1 rounded-md">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400">Belum ada produk</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
