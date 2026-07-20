@extends('layouts.app')

@section('title', $putaway->task_number)
@section('page-title', 'Put Away '.$putaway->task_number)

@section('content')
<div class="max-w-2xl space-y-6">
    <a href="{{ route('putaways.index') }}" class="text-sm text-gray-500">← Daftar putaway</a>

    <div class="bg-white rounded-2xl border p-6 space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-gray-500">Produk</span><span class="font-medium">{{ $putaway->product?->name }} ({{ $putaway->product?->sku }})</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Gudang</span><span>{{ $putaway->warehouse?->code }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Batch</span><span>{{ $putaway->batch_number ?? '—' }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Qty</span><span>{{ $putaway->quantity_done }}/{{ $putaway->quantity }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Suggested</span><span class="font-mono text-teal-700">{{ $putaway->suggestedLocation?->code ?? '—' }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Status</span><span>{{ $putaway->status }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">GR</span>
            <a class="text-teal-600" href="{{ route('goods-receipts.show', $putaway->goods_receipt_id) }}">{{ $putaway->goodsReceipt?->receipt_number }}</a>
        </div>
    </div>

    @if($putaway->status === 'pending')
    <form method="POST" action="{{ route('putaways.complete', $putaway) }}" class="bg-white rounded-2xl border p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-2">Target Bin *</label>
            <select name="location_id" required class="w-full rounded-xl border-gray-200">
                <option value="">Pilih bin</option>
                @foreach($bins as $bin)
                    <option value="{{ $bin->id }}" @selected(old('location_id', $putaway->suggested_location_id)==$bin->id)>
                        {{ $bin->code }} — {{ $bin->name }}@if($bin->capacity) (cap {{ $bin->capacity }})@endif
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Qty (opsional, default sisa)</label>
            <input type="number" min="1" max="{{ $putaway->quantity - $putaway->quantity_done }}" name="quantity" class="w-full rounded-xl border-gray-200" placeholder="{{ $putaway->quantity - $putaway->quantity_done }}">
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Catatan</label>
            <input type="text" name="notes" class="w-full rounded-xl border-gray-200" value="{{ old('notes', $putaway->notes) }}">
        </div>
        <button class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-medium">✓ Complete Put Away</button>
    </form>
    @else
    <div class="bg-green-50 text-green-700 rounded-2xl p-4">Task selesai di bin <span class="font-mono">{{ $putaway->location?->code }}</span></div>
    @endif
</div>
@endsection
