@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan Gudang')
@section('page-subtitle', 'Laporan aktivitas gudang')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('reports.receipts') }}" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition">
                    📥
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Penerimaan</h3>
                    <p class="text-sm text-gray-500">Laporan barang masuk</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('reports.issues') }}" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-orange-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition">
                    📤
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Pengiriman</h3>
                    <p class="text-sm text-gray-500">Laporan barang keluar</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('reports.stock') }}" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition">
                    📊
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Stok</h3>
                    <p class="text-sm text-gray-500">Laporan posisi stok</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('reports.mutations') }}" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-purple-200 transition group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition">
                    🔄
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Mutasi</h3>
                    <p class="text-sm text-gray-500">Laporan pergerakan stok</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
