@extends('layouts.app')

@section('title', 'Kartu Stok - ' . $product->name)
@section('page-title', 'Kartu Stok')
@section('page-subtitle', $product->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('inventory.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">←</a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h2>
            <p class="text-gray-500">SKU: {{ $product->sku }}</p>
        </div>
    </div>

    <!-- Stock Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">Saldo Saat Ini</p>
            <p class="text-3xl font-bold text-teal-600">{{ number_format($currentStock) }} {{ $product->unit }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">Masuk (Period)</p>
            <p class="text-3xl font-bold text-green-600">+{{ number_format($totalIn) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-sm text-gray-500">Keluar (Period)</p>
            <p class="text-3xl font-bold text-red-600">-{{ number_format($totalOut) }}</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2 rounded-xl border border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2 rounded-xl border border-gray-200">
            </div>
            <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 transition">🔍 Filter</button>
        </form>
    </div>

    <!-- Mutation History -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-teal-100">
            <h3 class="font-bold text-teal-800">Riwayat Mutasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Referensi</th>
                        <th class="px-6 py-4">Dari</th>
                        <th class="px-6 py-4">Ke</th>
                        <th class="px-6 py-4 text-right">Qty</th>
                        <th class="px-6 py-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mutations as $mut)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $mut->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded bg-gray-100">{{ ucfirst(str_replace('_', ' ', $mut->reference_type)) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $mut->fromWarehouse->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $mut->toWarehouse->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $mut->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $mut->quantity > 0 ? '+' : '' }}{{ number_format($mut->quantity) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $mut->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <p>Tidak ada mutasi dalam periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mutations->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $mutations->links() }}</div>
        @endif
    </div>
</div>
@endsection
