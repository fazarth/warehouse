@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Order')
@section('page-subtitle', 'PO → GR → QC → Put Away')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="No. PO / supplier" class="rounded-xl border-gray-200 text-sm">
            <select name="status" class="rounded-xl border-gray-200 text-sm" onchange="this.form.submit()">
                <option value="">Semua status</option>
                @foreach(['draft','ordered','partial','received','cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 bg-gray-100 rounded-xl text-sm">Filter</button>
        </form>
        @can('purchase_order.create')
        <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl">➕ Buat PO</a>
        @endcan
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-4 text-left">PO</th>
                    <th class="px-6 py-4 text-left">Supplier</th>
                    <th class="px-6 py-4 text-left">Tanggal</th>
                    <th class="px-6 py-4 text-right">Ordered</th>
                    <th class="px-6 py-4 text-right">Received</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $po)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono font-semibold text-teal-700">{{ $po->po_number }}</td>
                    <td class="px-6 py-4">{{ $po->supplier?->name }}</td>
                    <td class="px-6 py-4 text-sm">{{ $po->order_date?->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($po->qty_ordered ?? 0) }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($po->qty_received ?? 0) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-teal-50 text-teal-700">{{ $po->status }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('purchase-orders.show', $po) }}" class="text-teal-600 hover:underline text-sm">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Belum ada PO.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
