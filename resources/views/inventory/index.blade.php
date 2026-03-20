@extends('layouts.app')

@section('title', 'Stok Gudang')
@section('page-title', 'Stok Gudang')
@section('page-subtitle', 'Lihat stok di semua lokasi')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">Total Item</p>
            <p class="text-2xl font-bold text-teal-600">{{ number_format($summary['totalItems']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">Total Qty</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($summary['totalQty']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">Low Stock</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($summary['lowStock']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">Expired Soon</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($summary['expiringSoon']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <select name="warehouse_id" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500">
                <option value="">Semua Lokasi</option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                   class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500">
            <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 transition">🔍 Filter</button>
        </form>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Lokasi</th>
                        <th class="px-6 py-4">Batch</th>
                        <th class="px-6 py-4">Lot</th>
                        <th class="px-6 py-4">Expired</th>
                        <th class="px-6 py-4 text-right">Qty</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($stocks as $stock)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800">{{ $stock->product->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $stock->product->sku ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $stock->warehouse->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $stock->batch_number ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $stock->lot_number ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($stock->expired_date)
                                @if($stock->expired_date->isPast())
                                <span class="text-red-600 font-medium">{{ $stock->expired_date->format('d/m/Y') }} (Expired)</span>
                                @elseif($stock->expired_date->diffInDays(now()) < 30)
                                <span class="text-orange-600">{{ $stock->expired_date->format('d/m/Y') }}</span>
                                @else
                                <span class="text-gray-600">{{ $stock->expired_date->format('d/m/Y') }}</span>
                                @endif
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-800">
                            {{ number_format($stock->quantity) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('inventory.stock-card', $stock->product) }}" class="text-teal-600 hover:underline text-sm">Kartu Stok</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📊</div>
                            <p>Tidak ada data stok</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $stocks->links() }}</div>
        @endif
    </div>
</div>
@endsection
