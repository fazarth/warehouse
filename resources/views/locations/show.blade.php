@extends('layouts.app')

@section('title', 'Lokasi '.$location->code)
@section('page-title', $location->name)
@section('page-subtitle', $location->fullPath())

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <p class="font-mono text-teal-600 font-semibold">{{ $location->code }}</p>
                    <h2 class="text-xl font-bold text-gray-800">{{ $location->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ \App\Models\Location::typeLabel($location->type) }} · {{ $location->warehouse?->name }}
                        @if($location->parent) · parent {{ $location->parent->code }} @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('locations.edit', $location) }}" class="px-3 py-2 bg-gray-100 rounded-xl text-sm">Edit</a>
                    <a href="{{ route('locations.label', $location) }}" class="px-3 py-2 bg-teal-600 text-white rounded-xl text-sm">Cetak Label</a>
                </div>
            </div>
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div class="p-3 bg-gray-50 rounded-xl">
                    <dt class="text-gray-500">Kapasitas</dt>
                    <dd class="font-semibold">{{ $location->capacity ?? '∞' }}</dd>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <dt class="text-gray-500">Terisi</dt>
                    <dd class="font-semibold">{{ $location->occupiedQuantity() }}</dd>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <dt class="text-gray-500">Sisa</dt>
                    <dd class="font-semibold">{{ $location->remainingCapacity() ?? '∞' }}</dd>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <dt class="text-gray-500">Status</dt>
                    <dd class="font-semibold">{{ $location->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
            </dl>
            @if($location->description)
            <p class="mt-4 text-sm text-gray-600">{{ $location->description }}</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b font-semibold text-gray-800">Stok di lokasi ini</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-left">Batch</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($location->productStocks as $stock)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('products.show', $stock->product) }}" class="text-teal-600 hover:underline font-medium">{{ $stock->product?->name }}</a>
                                <div class="text-xs text-gray-400 font-mono">{{ $stock->product?->sku }}</div>
                            </td>
                            <td class="px-4 py-3 font-mono">{{ $stock->batch_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($stock->quantity) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Belum ada stok di bin ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($location->children->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold mb-3">Child locations</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($location->children as $child)
                    <a href="{{ route('locations.show', $child) }}" class="px-3 py-2 bg-teal-50 text-teal-800 rounded-xl text-sm font-mono hover:bg-teal-100">
                        {{ $child->code }} <span class="text-xs opacity-70">({{ $child->type }})</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
        <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">QR Code</p>
        <p class="font-mono font-bold text-lg text-teal-700 mb-4">{{ $qr->code }}</p>
        <img src="{{ $qr->imageUrl(220) }}" alt="QR {{ $qr->code }}" class="mx-auto rounded-xl border border-gray-100" width="220" height="220">
        <a href="{{ route('scan.index', ['code' => $qr->code]) }}" class="mt-4 inline-block text-sm text-teal-600 hover:underline">Uji scan →</a>
    </div>
</div>
@endsection
