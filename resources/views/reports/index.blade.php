@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan Gudang')
@section('page-subtitle', 'KPI manajemen + laporan operasional')

@section('content')
<div class="space-y-6">
    {{-- Management KPI --}}
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-500">Total Stok</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($kpi['total_stock']) }}</p>
            <p class="text-[11px] text-indigo-600 mt-1">{{ $kpi['products'] }} produk · {{ $kpi['warehouses'] }} gudang</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-500">Open PO</p>
            <p class="text-2xl font-bold text-indigo-700 mt-1">{{ number_format($kpi['open_po']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">draft / ordered / partial</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-500">Pending GR</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($kpi['pending_gr']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">menunggu verify</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-500">Open GI</p>
            <p class="text-2xl font-bold text-orange-700 mt-1">{{ number_format($kpi['open_gi']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">pick → pack → ship</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-500">7 hari GR / GI</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $kpi['gr_7d'] }} / {{ $kpi['gi_7d'] }}</p>
            <p class="text-[11px] text-gray-400 mt-1">inbound · outbound</p>
        </div>
        <a href="{{ route('reports.low-stock') }}" class="bg-white rounded-xl p-4 border border-red-100 shadow-sm hover:border-red-300 transition">
            <p class="text-xs text-gray-500">Low Stock</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ number_format($kpi['low_stock']) }}</p>
            <p class="text-[11px] text-red-500 mt-1">≤ min stock</p>
        </a>
        <div class="bg-white rounded-xl p-4 border border-amber-100 shadow-sm">
            <p class="text-xs text-gray-500">Near Expiry</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($kpi['near_expiry']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">qty ≤ 60 hari</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-rose-100 shadow-sm">
            <p class="text-xs text-gray-500">Expired</p>
            <p class="text-2xl font-bold text-rose-700 mt-1">{{ number_format($kpi['expired']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">qty kadaluarsa</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-500">Pending SO</p>
            <p class="text-2xl font-bold text-purple-700 mt-1">{{ number_format($kpi['pending_so']) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">butuh approve</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('reports.receipts') }}" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition">📥</div>
                <div>
                    <h3 class="font-bold text-gray-800">Penerimaan</h3>
                    <p class="text-sm text-gray-500">Laporan barang masuk · CSV / Excel / PDF</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.issues') }}" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-orange-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition">📤</div>
                <div>
                    <h3 class="font-bold text-gray-800">Pengiriman</h3>
                    <p class="text-sm text-gray-500">Laporan barang keluar · CSV / Excel / PDF</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.stock') }}" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition">📊</div>
                <div>
                    <h3 class="font-bold text-gray-800">Stok</h3>
                    <p class="text-sm text-gray-500">Posisi stok · CSV / Excel / PDF</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.mutations') }}" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-purple-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition">🔄</div>
                <div>
                    <h3 class="font-bold text-gray-800">Mutasi</h3>
                    <p class="text-sm text-gray-500">Ledger pergerakan · CSV / Excel / PDF</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.aging') }}" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-red-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-red-400 to-amber-500 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition">⏳</div>
                <div>
                    <h3 class="font-bold text-gray-800">Aging / Expiry</h3>
                    <p class="text-sm text-gray-500">Bucket FEFO · CSV / Excel / PDF</p>
                </div>
            </div>
        </a>

        <a href="{{ route('reports.low-stock') }}" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-rose-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-rose-400 to-red-600 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition">⚠️</div>
                <div>
                    <h3 class="font-bold text-gray-800">Low Stock</h3>
                    <p class="text-sm text-gray-500">Di bawah min stock · CSV / Excel / PDF</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
