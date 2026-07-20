@extends('layouts.app')

@section('title', 'Laporan Stok')
@section('page-title', 'Laporan Posisi Stok')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Gudang</label>
                <select name="warehouse_id" class="px-4 py-2 rounded-xl border border-gray-200">
                    <option value="">Semua</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                <select name="category" class="px-4 py-2 rounded-xl border border-gray-200">
                    <option value="">Semua</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">🔍 Filter</button>
                        <a href="{{ route('reports.stock', array_merge(request()->query(), ['export' => 1])) }}"
                           class="px-6 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 transition">⬇ CSV</a>
                    </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-4">SKU</th>
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Unit</th>
                        <th class="px-6 py-4 text-right">Total Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-sm">{{ $p->sku }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('products.show', $p) }}" class="font-medium text-gray-800 hover:text-teal-600">{{ $p->name }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $p->category ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $p->unit }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $p->stocks_sum_quantity > 0 ? 'text-teal-600' : 'text-gray-400' }}">
                            {{ number_format($p->stocks_sum_quantity ?? 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
                @if($products->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-4 font-medium text-right">Total:</td>
                        <td class="px-6 py-4 text-right font-bold text-teal-600">{{ number_format($products->sum('stocks_sum_quantity')) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
