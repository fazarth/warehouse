@extends('layouts.app')

@section('title', 'Laporan Pengiriman')
@section('page-title', 'Laporan Pengiriman')

@section('content')
<div class="space-y-6">
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
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="px-4 py-2 rounded-xl border border-gray-200">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition">🔍 Filter</button>
                        <a href="{{ route('reports.issues', array_merge(request()->query(), ['export' => 1])) }}"
                           class="px-6 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 transition">⬇ CSV</a>
                    </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">No. Pengiriman</th>
                        <th class="px-6 py-4">Tujuan</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Items</th>
                        <th class="px-6 py-4 text-right">Total Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($issues as $i)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $i->issue_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('goods-issues.show', $i) }}" class="font-mono text-orange-600 hover:underline">{{ $i->issue_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $i->destination }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $i->status === 'delivered' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $i->status === 'shipped' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $i->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                {{ ucfirst($i->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">{{ $i->items_count }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-red-600">-{{ number_format($i->items_sum_quantity ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
                @if($issues->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-6 py-4 font-medium text-right">Total:</td>
                        <td class="px-6 py-4 text-right font-bold text-red-600">-{{ number_format($issues->sum('items_sum_quantity')) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($issues->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $issues->links() }}</div>
        @endif
    </div>
</div>
@endsection
