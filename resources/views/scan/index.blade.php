@extends('layouts.app')

@section('title', 'Scan QR')
@section('page-title', 'Scan QR')
@section('page-subtitle', 'Resolve entity dari kode QR / barcode')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <form method="POST" action="{{ route('scan.lookup') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        @csrf
        <label class="block text-sm font-medium text-gray-700 mb-2">Kode QR / barcode / SKU</label>
        <div class="flex gap-3">
            <input type="text" name="code" value="{{ $code }}" autofocus autocomplete="off"
                   class="flex-1 rounded-xl border-gray-200 font-mono text-lg tracking-wide uppercase"
                   placeholder="LOC-A-01-03 / ITEM-SKU-RICE-25 / SUP-001">
            <button class="px-5 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-medium">Resolve</button>
        </div>
        <p class="text-xs text-gray-400 mt-2">Scanner HID biasanya mengirim Enter setelah scan — fokus di field ini.</p>
    </form>

    @if($code !== '')
        @if($result && $entity)
            <div class="bg-white rounded-2xl border border-teal-100 shadow-sm p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-teal-600 font-semibold">Resolved</p>
                        <p class="font-mono text-xl font-bold text-gray-900">{{ $result->code }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $result->entity_type }} · {{ $result->label }}</p>
                    </div>
                    <img src="{{ $result->imageUrl(120) }}" width="120" height="120" class="rounded-xl border" alt="QR">
                </div>

                <div class="p-4 bg-gray-50 rounded-xl text-sm">
                    @if($result->entity_type === 'product')
                        <p class="font-semibold text-gray-800">{{ $entity->name }}</p>
                        <p class="text-gray-500 font-mono">SKU {{ $entity->sku }} · barcode {{ $entity->barcode ?? '—' }}</p>
                        <p class="mt-1">Total stok: <strong>{{ number_format($entity->total_stock) }}</strong> {{ $entity->unit }}</p>
                    @elseif($result->entity_type === 'location')
                        <p class="font-semibold text-gray-800">{{ $entity->name }}</p>
                        <p class="text-gray-500">{{ \App\Models\Location::typeLabel($entity->type) }} · path {{ $entity->fullPath() }}</p>
                        <p class="mt-1">Gudang: {{ $entity->warehouse?->name }} · isi {{ $entity->occupiedQuantity() }} / {{ $entity->capacity ?? '∞' }}</p>
                    @elseif($result->entity_type === 'warehouse')
                        <p class="font-semibold">{{ $entity->name }} ({{ $entity->code }})</p>
                    @elseif($result->entity_type === 'supplier')
                        <p class="font-semibold">{{ $entity->name }} ({{ $entity->code }})</p>
                    @else
                        <p class="font-semibold">{{ class_basename($entity) }} #{{ $entity->id }}</p>
                    @endif
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">Aksi cepat</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($actions as $action)
                            <a href="{{ $action['url'] }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-teal-200 hover:bg-teal-50 transition">
                                <span class="text-xl">{{ $action['icon'] }}</span>
                                <span class="font-medium text-gray-800">{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-100 text-amber-800 rounded-2xl p-6">
                <p class="font-semibold">Kode tidak ditemukan</p>
                <p class="text-sm mt-1 font-mono">{{ $code }}</p>
                <p class="text-sm mt-2">Pastikan QR sudah di-generate (lokasi/produk/supplier) atau gunakan format LOC- / ITEM- / SUP- / WH-.</p>
            </div>
        @endif
    @endif
</div>
@endsection
