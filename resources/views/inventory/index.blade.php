@extends('layouts.app')

@section('title', 'Stok Gudang')
@section('page-title', 'Stok Gudang')
@section('page-subtitle', 'Lihat stok di semua lokasi')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total Item</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-indigo-600">{{ number_format($summary['totalItems'] ?? $totalProducts ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total Qty</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ number_format($summary['totalQty'] ?? $totalQty ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Low Stock</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-amber-600">{{ number_format($summary['lowStock'] ?? $lowStockCount ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Expired Soon</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-red-600">{{ number_format($summary['expiringSoon'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="warehouse_id" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                <option value="">Semua Lokasi</option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                   class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            <button type="submit" class="px-3.5 py-2 rounded-lg text-sm font-semibold text-white" style="background:#4f46e5;">Filter</button>
        </form>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Lokasi</th>
                        <th class="px-4 py-3">Batch</th>
                        <th class="px-4 py-3">Lot</th>
                        <th class="px-4 py-3">Expired</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stocks as $stock)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-900">{{ $stock->product->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $stock->product->sku ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $stock->warehouse->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $stock->batch_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $stock->lot_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($stock->expired_date)
                                @if($stock->expired_date->isPast())
                                <span class="text-red-600 font-medium">{{ $stock->expired_date->format('d/m/Y') }} (Expired)</span>
                                @elseif($stock->expired_date->diffInDays(now()) < 30)
                                <span class="text-indigo-600">{{ $stock->expired_date->format('d/m/Y') }}</span>
                                @else
                                <span class="text-slate-600">{{ $stock->expired_date->format('d/m/Y') }}</span>
                                @endif
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">
                            {{ number_format($stock->quantity) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('inventory.stock-card', $stock->product) }}" class="text-xs font-medium text-indigo-600 hover:underline">Kartu Stok</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-400">
                            Tidak ada data stok
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $stocks->links() }}</div>
        @endif
    </div>
</div>
@endsection
