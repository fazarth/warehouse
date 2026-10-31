@extends('layouts.app')

@section('title', 'Pengiriman Barang')
@section('page-title', 'Pengiriman Barang')
@section('page-subtitle', 'Kelola pengiriman barang ke tujuan')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="status" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                <option value="">Semua status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
            </select>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            <button type="submit" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>
        @can('goods_issue.create')
        <a href="{{ route('goods-issues.create') }}"
           class="inline-flex items-center justify-center px-3.5 py-2 rounded-lg text-sm font-semibold text-white"
           style="background:#4f46e5;">
            + Buat pengiriman
        </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-3">No. pengiriman</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Tujuan</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($issues as $issue)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3">
                            <a href="{{ route('goods-issues.show', $issue) }}" class="font-mono text-sm font-semibold text-indigo-600 hover:underline">
                                {{ $issue->issue_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $issue->issue_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $issue->destination }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-[11px] font-medium bg-slate-50 border border-slate-200 rounded-full text-slate-600">{{ $issue->items->count() }} item</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($issue->status === 'delivered')
                            <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Delivered</span>
                            @elseif($issue->status === 'shipped')
                            <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-200">Shipped</span>
                            @else
                            <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-amber-50 text-amber-700 border border-amber-200">{{ ucfirst($issue->status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('goods-issues.show', $issue) }}" class="text-xs font-medium text-slate-600 hover:text-indigo-600 px-2 py-1 rounded-md hover:bg-indigo-50">Detail</a>
                                @can('goods_issue.ship')
                                @if($issue->status === 'pending')
                                <form action="{{ route('goods-issues.ship', $issue) }}" method="POST" onsubmit="return confirm('Kirim barang ini?')">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-blue-600 hover:bg-blue-50 px-2 py-1 rounded-md">Ship</button>
                                </form>
                                @elseif($issue->status === 'shipped')
                                <form action="{{ route('goods-issues.deliver', $issue) }}" method="POST" onsubmit="return confirm('Konfirmasi delivered?')">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded-md">Deliver</button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400">Belum ada pengiriman</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($issues->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $issues->links() }}</div>
        @endif
    </div>
</div>
@endsection
