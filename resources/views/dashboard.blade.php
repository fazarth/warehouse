@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional gudang')

@section('content')
<div class="space-y-5">
    {{-- Header actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500">Halo, <span class="font-medium text-gray-900">{{ auth()->user()->name }}</span></p>
            <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }} · {{ now()->format('H:i') }} WIB</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('goods_receipt.create')
            <a href="{{ route('goods-receipts.create') }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                Terima barang
            </a>
            @endcan
            @can('goods_issue.create')
            <a href="{{ route('goods-issues.create') }}"
               class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg text-white hover:opacity-90"
               style="background: #4f46e5;">
                Kirim barang
            </a>
            @endcan
        </div>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs font-medium text-gray-500">Total produk</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['products']) }}</p>
            <p class="mt-2 text-[11px] text-gray-500">{{ $stats['suppliers'] }} supplier</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs font-medium text-gray-500">Total stok</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['totalStock']) }}</p>
            <p class="mt-2 text-[11px] text-gray-500">{{ $stats['warehouses'] }} lokasi</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs font-medium text-gray-500">Penerimaan hari ini</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['todayReceipts']) }}</p>
            <p class="mt-2 text-[11px] text-emerald-600">+{{ number_format($stats['todayReceiptQty']) }} unit</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs font-medium text-gray-500">Pengiriman hari ini</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['todayIssues']) }}</p>
            <p class="mt-2 text-[11px] text-indigo-600">-{{ number_format($stats['todayIssueQty']) }} unit</p>
        </div>
    </div>

    {{-- Attention strip --}}
    @if($stats['pendingReceipts'] > 0 || $stats['pendingStockopnames'] > 0 || $stats['lowStock'] > 0 || $stats['nearExpiry'] > 0 || $stats['expired'] > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        @if($stats['pendingReceipts'] > 0)
        <a href="{{ route('goods-receipts.index', ['status' => 'pending']) }}"
           class="flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 hover:bg-amber-100/80 transition">
            <div>
                <p class="text-sm font-medium text-amber-900">{{ $stats['pendingReceipts'] }} penerimaan pending</p>
                <p class="text-xs text-amber-700">Menunggu verifikasi</p>
            </div>
            <span class="text-xs font-medium text-amber-800">Buka →</span>
        </a>
        @endif

        @if($stats['pendingStockopnames'] > 0)
        <a href="{{ route('stockopnames.index', ['status' => 'pending_approval']) }}"
           class="flex items-center justify-between gap-3 rounded-lg border border-violet-200 bg-violet-50 px-4 py-3 hover:bg-violet-100/80 transition">
            <div>
                <p class="text-sm font-medium text-violet-900">{{ $stats['pendingStockopnames'] }} stock opname pending</p>
                <p class="text-xs text-violet-700">Menunggu approval</p>
            </div>
            <span class="text-xs font-medium text-violet-800">Buka →</span>
        </a>
        @endif

        @can('report.view')
        @if($stats['lowStock'] > 0)
        <a href="{{ route('reports.low-stock') }}"
           class="flex items-center justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 hover:bg-red-100/80 transition">
            <div>
                <p class="text-sm font-medium text-red-900">{{ $stats['lowStock'] }} low stock</p>
                <p class="text-xs text-red-700">On-hand ≤ min stock</p>
            </div>
            <span class="text-xs font-medium text-red-800">Buka →</span>
        </a>
        @endif
        @if($stats['nearExpiry'] > 0)
        <a href="{{ route('reports.aging') }}"
           class="flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 hover:bg-amber-100/80 transition">
            <div>
                <p class="text-sm font-medium text-amber-900">{{ number_format($stats['nearExpiry']) }} near expiry</p>
                <p class="text-xs text-amber-700">Qty ≤ 60 hari</p>
            </div>
            <span class="text-xs font-medium text-amber-800">Aging →</span>
        </a>
        @endif
        @if($stats['expired'] > 0)
        <a href="{{ route('reports.aging') }}"
           class="flex items-center justify-between gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 hover:bg-rose-100/80 transition">
            <div>
                <p class="text-sm font-medium text-rose-900">{{ number_format($stats['expired']) }} expired</p>
                <p class="text-xs text-rose-700">Prioritas alokasi by expiry (FEFO)</p>
            </div>
            <span class="text-xs font-medium text-rose-800">Aging →</span>
        </a>
        @endif
        @endcan
    </div>
    @endif

    {{-- Recent activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <section class="rounded-lg border border-gray-200 bg-white overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Penerimaan terbaru</h3>
                <a href="{{ route('goods-receipts.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentReceipts as $receipt)
                <a href="{{ route('goods-receipts.show', $receipt) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $receipt->receipt_number }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $receipt->supplier->name ?? '-' }} · {{ $receipt->receipt_date->format('d M Y') }}</p>
                    </div>
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[11px] font-medium
                        {{ $receipt->status === 'verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ ucfirst($receipt->status) }}
                    </span>
                </a>
                @empty
                <div class="px-4 py-10 text-center text-sm text-gray-400">Belum ada penerimaan</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Pengiriman terbaru</h3>
                <a href="{{ route('goods-issues.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentIssues as $issue)
                <a href="{{ route('goods-issues.show', $issue) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $issue->issue_number }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $issue->destination }} · {{ $issue->issue_date->format('d M Y') }}</p>
                    </div>
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[11px] font-medium border
                        {{ $issue->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                        {{ $issue->status === 'shipped' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                        {{ $issue->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                        {{ !in_array($issue->status, ['delivered','shipped','pending']) ? 'bg-gray-50 text-gray-600 border-gray-200' : '' }}">
                        {{ ucfirst($issue->status) }}
                    </span>
                </a>
                @empty
                <div class="px-4 py-10 text-center text-sm text-gray-400">Belum ada pengiriman</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="rounded-lg border border-gray-200 bg-white overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Mutasi stok terbaru</h3>
            <a href="{{ route('reports.mutations') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wide border-b border-gray-100 bg-gray-50/80">
                        <th class="px-4 py-2.5">Waktu</th>
                        <th class="px-4 py-2.5">Produk</th>
                        <th class="px-4 py-2.5">Dari</th>
                        <th class="px-4 py-2.5">Ke</th>
                        <th class="px-4 py-2.5">Qty</th>
                        <th class="px-4 py-2.5">Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentMutations as $mut)
                    <tr class="hover:bg-gray-50/80">
                        <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ $mut->created_at->format('d/m H:i') }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900">{{ $mut->product->name ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $mut->fromWarehouse->name ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $mut->toWarehouse->name ?? '-' }}</td>
                        <td class="px-4 py-2.5 font-semibold {{ $mut->quantity > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $mut->quantity > 0 ? '+' : '' }}{{ number_format($mut->quantity) }}
                        </td>
                        <td class="px-4 py-2.5 text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $mut->reference_type)) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada mutasi stok</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
