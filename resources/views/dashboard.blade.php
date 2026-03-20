@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Warehouse')
@section('page-subtitle', 'Ringkasan aktivitas gudang')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-teal-500 via-teal-600 to-emerald-600 text-white rounded-2xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative z-10 p-6 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-teal-100 text-sm font-medium">Selamat datang,</p>
                    <h2 class="text-2xl md:text-3xl font-bold mt-1">{{ auth()->user()->name }} 👋</h2>
                    <p class="text-teal-100 mt-2 text-sm">
                        {{ now()->format('l, d F Y') }} • {{ now()->format('H:i') }} WIB
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('goods-receipts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur hover:bg-white/30 text-white rounded-xl border border-white/20 transition">
                        <span>📥</span> Terima Barang
                    </a>
                    <a href="{{ route('goods-issues.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur hover:bg-white/30 text-white rounded-xl border border-white/20 transition">
                        <span>📤</span> Kirim Barang
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Products -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Total Produk</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($stats['products']) }}</p>
                    <p class="text-xs text-teal-600 mt-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-teal-100 text-teal-700 text-[10px] font-semibold">
                            {{ $stats['suppliers'] }} Supplier
                        </span>
                    </p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-teal-400 to-teal-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform">
                    📦
                </div>
            </div>
        </div>

        <!-- Total Stock -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Total Stok</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($stats['totalStock']) }}</p>
                    <p class="text-xs text-blue-600 mt-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-[10px] font-semibold">
                            {{ $stats['warehouses'] }} Lokasi
                        </span>
                    </p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform">
                    🏭
                </div>
            </div>
        </div>

        <!-- Today Receipts -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Penerimaan Hari Ini</p>
                    <p class="text-3xl font-bold text-green-700 mt-2">{{ number_format($stats['todayReceipts']) }}</p>
                    <p class="text-xs text-green-600 mt-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700 text-[10px] font-semibold">
                            +{{ number_format($stats['todayReceiptQty']) }} unit
                        </span>
                    </p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform">
                    📥
                </div>
            </div>
        </div>

        <!-- Today Issues -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Pengiriman Hari Ini</p>
                    <p class="text-3xl font-bold text-orange-700 mt-2">{{ number_format($stats['todayIssues']) }}</p>
                    <p class="text-xs text-orange-600 mt-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-orange-100 text-orange-700 text-[10px] font-semibold">
                            -{{ number_format($stats['todayIssueQty']) }} unit
                        </span>
                    </p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform">
                    📤
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Alerts -->
    @if($stats['pendingReceipts'] > 0 || $stats['pendingStockopnames'] > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($stats['pendingReceipts'] > 0)
        <a href="{{ route('goods-receipts.index', ['status' => 'pending']) }}" class="flex items-center gap-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl hover:bg-yellow-100 transition">
            <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center text-xl">⏳</div>
            <div>
                <p class="font-semibold text-yellow-800">{{ $stats['pendingReceipts'] }} Penerimaan Pending</p>
                <p class="text-sm text-yellow-600">Menunggu verifikasi</p>
            </div>
        </a>
        @endif
        
        @if($stats['pendingStockopnames'] > 0)
        <a href="{{ route('stockopnames.index', ['status' => 'pending_approval']) }}" class="flex items-center gap-4 p-4 bg-purple-50 border border-purple-200 rounded-xl hover:bg-purple-100 transition">
            <div class="w-12 h-12 bg-purple-400 rounded-xl flex items-center justify-center text-xl text-white">📋</div>
            <div>
                <p class="font-semibold text-purple-800">{{ $stats['pendingStockopnames'] }} Stock Opname Pending</p>
                <p class="text-sm text-purple-600">Menunggu approval</p>
            </div>
        </a>
        @endif
    </div>
    @endif

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Receipts -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-green-800 flex items-center gap-2">
                    <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-sm">📥</span>
                    Penerimaan Terbaru
                </h3>
                <a href="{{ route('goods-receipts.index') }}" class="text-xs font-medium text-green-600 hover:text-green-700">
                    Lihat Semua →
                </a>
            </div>
            <div class="p-5">
                <div class="space-y-3">
                    @forelse($recentReceipts as $receipt)
                    <a href="{{ route('goods-receipts.show', $receipt) }}" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-green-50 border border-gray-100 hover:border-green-200 transition-all group">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold text-xs shadow">
                            {{ substr($receipt->receipt_number, -4) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 truncate group-hover:text-green-700 transition-colors">
                                {{ $receipt->receipt_number }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $receipt->supplier->name ?? '-' }} • {{ $receipt->receipt_date->format('d M Y') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $receipt->status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($receipt->status) }}
                        </span>
                    </a>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        <div class="text-4xl mb-2">📭</div>
                        <p class="text-sm">Belum ada penerimaan</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Issues -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-orange-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-orange-800 flex items-center gap-2">
                    <span class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center text-sm">📤</span>
                    Pengiriman Terbaru
                </h3>
                <a href="{{ route('goods-issues.index') }}" class="text-xs font-medium text-orange-600 hover:text-orange-700">
                    Lihat Semua →
                </a>
            </div>
            <div class="p-5">
                <div class="space-y-3">
                    @forelse($recentIssues as $issue)
                    <a href="{{ route('goods-issues.show', $issue) }}" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-orange-50 border border-gray-100 hover:border-orange-200 transition-all group">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-xs shadow">
                            {{ substr($issue->issue_number, -4) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 truncate group-hover:text-orange-700 transition-colors">
                                {{ $issue->issue_number }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $issue->destination }} • {{ $issue->issue_date->format('d M Y') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ $issue->status === 'delivered' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $issue->status === 'shipped' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $issue->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                            {{ ucfirst($issue->status) }}
                        </span>
                    </a>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        <div class="text-4xl mb-2">📭</div>
                        <p class="text-sm">Belum ada pengiriman</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Mutations -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-violet-50 border-b border-purple-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-purple-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-sm">🔄</span>
                Mutasi Stok Terbaru
            </h3>
            <a href="{{ route('reports.mutations') }}" class="text-xs font-medium text-purple-600 hover:text-purple-700">
                Lihat Semua →
            </a>
        </div>
        <div class="p-5">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Dari</th>
                            <th class="px-4 py-3">Ke</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Referensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentMutations as $mut)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $mut->created_at->format('d/m H:i') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $mut->product->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $mut->fromWarehouse->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $mut->toWarehouse->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold {{ $mut->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mut->quantity > 0 ? '+' : '' }}{{ number_format($mut->quantity) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $mut->reference_type)) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                <div class="text-3xl mb-2">📊</div>
                                <p class="text-sm">Belum ada mutasi stok</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
