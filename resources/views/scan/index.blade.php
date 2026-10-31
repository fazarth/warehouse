@extends('layouts.app')

@section('title', 'Scan QR')
@section('page-title', 'Scan QR')
@section('page-subtitle', 'Resolve entity dari kode QR / barcode')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <form method="POST" action="{{ route('scan.lookup') }}" class="bg-white rounded-xl border border-slate-200 p-5">
        @csrf
        <label class="block text-sm font-medium text-slate-700 mb-2">Kode QR / barcode / SKU</label>
        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" name="code" value="{{ $code }}" autofocus autocomplete="off"
                   class="flex-1 rounded-lg border border-slate-200 font-mono text-base tracking-wide uppercase px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
                   placeholder="LOC-A-01-03 / ITEM-SKU-RICE-25 / SUP-001">
            <button class="px-5 py-2.5 text-white rounded-lg font-semibold text-sm" style="background:#4f46e5;">Resolve</button>
        </div>
        <p class="text-xs text-slate-400 mt-2">Scanner HID biasanya mengirim Enter setelah scan — fokus di field ini.</p>
    </form>

    @if($code !== '')
        @if($result && $entity)
            <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-indigo-600 font-semibold">Resolved</p>
                        <p class="font-mono text-xl font-semibold text-slate-900">{{ $result->code }}</p>
                        <p class="text-sm text-slate-500 mt-1">{{ $result->entity_type }} · {{ $result->label }}</p>
                    </div>
                    <img src="{{ $result->imageUrl(120) }}" width="120" height="120" class="rounded-lg border border-slate-200" alt="QR">
                </div>

                <div class="p-4 bg-slate-50 rounded-lg text-sm border border-slate-100">
                    @if($result->entity_type === 'product')
                        <p class="font-semibold text-slate-800">{{ $entity->name }}</p>
                        <p class="text-slate-500 font-mono">SKU {{ $entity->sku }} · barcode {{ $entity->barcode ?? '—' }}</p>
                        <p class="mt-1">Total stok: <strong>{{ number_format($entity->total_stock) }}</strong> {{ $entity->unit }}</p>
                    @elseif($result->entity_type === 'location')
                        <p class="font-semibold text-slate-800">{{ $entity->name }}</p>
                        <p class="text-slate-500">{{ \App\Models\Location::typeLabel($entity->type) }} · path {{ $entity->fullPath() }}</p>
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
                    <p class="text-xs uppercase tracking-wider text-slate-500 mb-2">Aksi cepat</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($actions as $action)
                            <a href="{{ $action['url'] }}" class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-indigo-200 hover:bg-indigo-50/50 transition">
                                <span class="text-base opacity-70">{{ $action['icon'] }}</span>
                                <span class="font-medium text-slate-800 text-sm">{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl p-5">
                <p class="font-semibold">Kode tidak ditemukan</p>
                <p class="text-sm mt-1 font-mono">{{ $code }}</p>
                <p class="text-sm mt-2 text-amber-800">Pastikan QR sudah di-generate (lokasi/produk/supplier) atau gunakan format LOC- / ITEM- / SUP- / WH-.</p>
            </div>
        @endif
    @endif
</div>
@endsection
