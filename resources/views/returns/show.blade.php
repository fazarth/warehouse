@extends('layouts.app')

@section('title', $return->return_number)
@section('page-title', $return->return_number)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center flex-wrap gap-3">
        <div>
            <a href="{{ route('returns.index') }}" class="text-sm text-gray-500">← Returns</a>
            <p class="text-gray-500 text-sm">{{ $return->source }} · {{ $return->warehouse?->code }} · {{ $return->return_date?->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2 items-center">
            @if($return->status === 'draft')
            <form method="POST" action="{{ route('returns.receive', $return) }}" onsubmit="return confirm('Receive return ke stok?')">
                @csrf
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm">Receive</button>
            </form>
            @endif
            <span class="px-3 py-2 rounded-xl text-sm {{ $return->status==='received'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $return->status }}</span>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border p-6 text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-500">GI</span><span>{{ $return->goodsIssue?->issue_number ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Reason</span><span>{{ $return->reason ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Default bin</span><span class="font-mono">{{ $return->location?->code ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Dibuat</span><span>{{ $return->user?->name }}</span></div>
            @if($return->received_at)
            <div class="flex justify-between"><span class="text-gray-500">Received</span><span>{{ $return->received_at->format('d/m H:i') }} · {{ $return->receiver?->name }}</span></div>
            @endif
            @if($return->notes)<p class="pt-2 border-t text-gray-600">{{ $return->notes }}</p>@endif
        </div>
        <div class="lg:col-span-2 bg-white rounded-xl border p-6">
            <h3 class="font-bold mb-3">Items</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">SKU</th>
                        <th class="px-3 py-2 text-left">Bin</th>
                        <th class="px-3 py-2 text-left">Batch/Exp</th>
                        <th class="px-3 py-2 text-right">Qty</th>
                        <th class="px-3 py-2 text-left">Cond</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($return->items as $item)
                    <tr>
                        <td class="px-3 py-2">{{ $item->product?->sku }}</td>
                        <td class="px-3 py-2 font-mono">{{ $item->location?->code ?? $return->location?->code ?? 'STAGING' }}</td>
                        <td class="px-3 py-2">{{ $item->batch_number ?? '—' }} · {{ $item->expired_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-3 py-2 text-right font-semibold text-green-700">+{{ $item->quantity }}</td>
                        <td class="px-3 py-2">{{ $item->condition }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
