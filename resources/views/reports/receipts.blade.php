@extends('layouts.app')

@section('title', 'Laporan Penerimaan')
@section('page-title', 'Laporan Penerimaan')

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
                <label class="block text-xs text-gray-500 mb-1">Supplier</label>
                <select name="supplier_id" class="px-4 py-2 rounded-xl border border-gray-200">
                    <option value="">Semua</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition">🔍 Filter</button>
                        <a href="{{ route('reports.receipts', array_merge(request()->query(), ['export' => 1])) }}"
                           class="px-6 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 transition">⬇ CSV</a>
                    </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">No. Penerimaan</th>
                        <th class="px-6 py-4">Supplier</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Items</th>
                        <th class="px-6 py-4 text-right">Total Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($receipts as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $r->receipt_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('goods-receipts.show', $r) }}" class="font-mono text-green-600 hover:underline">{{ $r->receipt_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $r->supplier->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $r->status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">{{ $r->items_count }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-green-600">+{{ number_format($r->items_sum_quantity ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
                @if($receipts->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-6 py-4 font-medium text-right">Total:</td>
                        <td class="px-6 py-4 text-right font-bold text-green-600">+{{ number_format($receipts->sum('items_sum_quantity')) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($receipts->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $receipts->links() }}</div>
        @endif
    </div>
</div>
@endsection
