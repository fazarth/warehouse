@extends('layouts.app')

@section('title', $product->name)
@section('page-title', 'Detail Produk')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('products.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">←</a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h2>
            <p class="text-gray-500">SKU: {{ $product->sku }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Info -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Informasi Produk</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-500">SKU</dt>
                    <dd class="font-mono font-semibold text-indigo-600">{{ $product->sku }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Barcode</dt>
                    <dd class="font-mono">{{ $product->barcode ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Kategori</dt>
                    <dd>{{ $product->category ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Unit</dt>
                    <dd>{{ $product->unit }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        @if($product->is_active)
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                        @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                        @endif
                    </dd>
                </div>
            </dl>
            
            <div class="mt-6 pt-4 border-t border-gray-100 flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-center rounded-xl transition">✏️ Edit</a>
                <a href="{{ route('inventory.stock-card', $product) }}" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-center rounded-xl transition">📊 Kartu Stok</a>
            </div>
        </div>

        <!-- Stock Summary -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Stok per Lokasi</h3>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total Stok</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($totalStock) }} {{ $product->unit }}</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-3">Lokasi</th>
                            <th class="px-4 py-3">Batch</th>
                            <th class="px-4 py-3">Lot</th>
                            <th class="px-4 py-3">Expired</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($product->stocks as $stock)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $stock->warehouse->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $stock->batch_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $stock->lot_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($stock->expired_date)
                                <span class="{{ $stock->expired_date->isPast() ? 'text-red-600' : ($stock->expired_date->diffInDays(now()) < 30 ? 'text-indigo-600' : 'text-gray-600') }}">
                                    {{ $stock->expired_date->format('d/m/Y') }}
                                </span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($stock->quantity) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                Belum ada stok
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
