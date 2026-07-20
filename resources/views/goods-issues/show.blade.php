@extends('layouts.app')

@section('title', $goodsIssue->issue_number)
@section('page-title', 'Detail Pengiriman')
@section('page-subtitle', 'FEFO pick → pack → ship')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-4">
            <a href="{{ route('goods-issues.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">←</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $goodsIssue->issue_number }}</h2>
                <p class="text-gray-500">{{ $goodsIssue->issue_date->format('d F Y') }} · strategy {{ strtoupper($goodsIssue->strategy ?? 'fefo') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @can('goods_issue.ship')
                        @if($goodsIssue->status === 'pending')
                        <form action="{{ route('goods-issues.allocate', $goodsIssue) }}" method="POST">
                            @csrf
                            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm">🎯 Allocate FEFO</button>
                        </form>
                        @endif

                        @if($goodsIssue->status === 'picking')
                        <form action="{{ route('goods-issues.pick', $goodsIssue) }}" method="POST" class="flex flex-wrap items-end gap-2">
                            @csrf
                            <button class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm" onclick="return confirm('Confirm all pick lines?')">✓ Complete Pick</button>
                        </form>
                        @endif

                        @if($goodsIssue->status === 'packing')
                        <form action="{{ route('goods-issues.pack', $goodsIssue) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="notes" placeholder="Pack notes" class="rounded-xl border-gray-200 text-sm">
                            <button class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm">📦 Pack</button>
                        </form>
                        @endif

                        @if($goodsIssue->status === 'packed')
                        <form action="{{ route('goods-issues.ship', $goodsIssue) }}" method="POST" class="flex flex-wrap gap-2" onsubmit="return confirm('Ship & deduct bin stock?')">
                            @csrf
                            <input type="text" name="carrier" placeholder="Carrier" class="rounded-xl border-gray-200 text-sm w-28">
                            <input type="text" name="tracking_number" placeholder="Tracking" class="rounded-xl border-gray-200 text-sm w-32">
                            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm">🚚 Ship</button>
                        </form>
                        @endif

                        @if($goodsIssue->status === 'shipped')
                        <form action="{{ route('goods-issues.deliver', $goodsIssue) }}" method="POST" onsubmit="return confirm('Mark delivered?')">
                            @csrf
                            <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm">✓ Delivered</button>
                        </form>
                        @endif
                        @endcan

            <span class="px-3 py-2 text-sm font-medium rounded-xl
                {{ in_array($goodsIssue->status, ['delivered']) ? 'bg-green-100 text-green-700' : '' }}
                {{ in_array($goodsIssue->status, ['shipped','packed']) ? 'bg-blue-100 text-blue-700' : '' }}
                {{ in_array($goodsIssue->status, ['picking','packing','pending']) ? 'bg-yellow-100 text-yellow-700' : '' }}">
                {{ ucfirst($goodsIssue->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Informasi</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Tujuan</dt><dd class="font-medium">{{ $goodsIssue->destination }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tanggal</dt><dd>{{ $goodsIssue->issue_date->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">No. SJ</dt><dd>{{ $goodsIssue->delivery_note ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Dibuat</dt><dd>{{ $goodsIssue->user->name ?? '-' }}</dd></div>
                @if($goodsIssue->allocated_at)
                <div class="flex justify-between"><dt class="text-gray-500">Allocated</dt><dd>{{ $goodsIssue->allocated_at->format('d/m H:i') }}</dd></div>
                @endif
                @if($goodsIssue->picked_at)
                <div class="flex justify-between"><dt class="text-gray-500">Picked</dt><dd>{{ $goodsIssue->picked_at->format('d/m H:i') }}</dd></div>
                @endif
                @if($goodsIssue->packed_at)
                <div class="flex justify-between"><dt class="text-gray-500">Packed</dt><dd>{{ $goodsIssue->packed_at->format('d/m H:i') }}</dd></div>
                @endif
                @if($goodsIssue->shipped_at)
                <div class="flex justify-between"><dt class="text-gray-500">Shipped</dt><dd>{{ $goodsIssue->shipped_at->format('d/m H:i') }} · {{ $goodsIssue->carrier ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tracking</dt><dd>{{ $goodsIssue->tracking_number ?? '-' }}</dd></div>
                @endif
            </dl>
            @if($goodsIssue->notes)
            <div class="mt-4 pt-4 border-t text-sm text-gray-600">{{ $goodsIssue->notes }}</div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Items ({{ $goodsIssue->items->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Produk</th>
                            <th class="px-3 py-2 text-left">Gudang</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Alloc</th>
                            <th class="px-3 py-2 text-right">Picked</th>
                            <th class="px-3 py-2 text-right">Packed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($goodsIssue->items as $item)
                        <tr>
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $item->product->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $item->product->sku ?? '' }} · {{ $item->batch_number ?? 'any batch' }}</div>
                            </td>
                            <td class="px-3 py-2">{{ $item->warehouse->code ?? '-' }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format($item->quantity) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($item->quantity_allocated) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($item->quantity_picked) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($item->quantity_packed) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($goodsIssue->pickLines->isNotEmpty())
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-3">Pick Lines (FEFO)</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-3 py-2 text-left">#</th>
                    <th class="px-3 py-2 text-left">Produk</th>
                    <th class="px-3 py-2 text-left">Bin</th>
                    <th class="px-3 py-2 text-left">Batch / Exp</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-right">Picked</th>
                    <th class="px-3 py-2 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($goodsIssue->pickLines as $line)
                <tr>
                    <td class="px-3 py-2 font-mono text-xs">{{ $line->id }}</td>
                    <td class="px-3 py-2">{{ $line->product?->sku }}</td>
                    <td class="px-3 py-2 font-mono text-teal-700">{{ $line->location?->code }}</td>
                    <td class="px-3 py-2">
                        {{ $line->batch_number ?? '—' }}
                        <span class="text-xs text-gray-400">· {{ $line->expired_date?->format('Y-m-d') ?? 'no exp' }}</span>
                    </td>
                    <td class="px-3 py-2 text-right">{{ $line->quantity }}</td>
                    <td class="px-3 py-2 text-right">{{ $line->quantity_picked }}</td>
                    <td class="px-3 py-2">
                        <span class="px-2 py-0.5 rounded-full text-xs
                            {{ $line->status==='picked'?'bg-green-100 text-green-700':($line->status==='allocated'?'bg-yellow-100 text-yellow-700':'bg-gray-100') }}">
                            {{ $line->status }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($goodsIssue->packages->isNotEmpty())
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-3">Packages</h3>
        <ul class="space-y-2 text-sm">
            @foreach($goodsIssue->packages as $pkg)
            <li class="border-b py-2">
                <span class="font-mono text-teal-700">{{ $pkg->package_number }}</span>
                <span class="text-gray-400">· {{ $pkg->items->count() }} lines · {{ $pkg->items->sum('quantity') }} qty</span>
                @if($pkg->notes)<span class="text-gray-400">· {{ $pkg->notes }}</span>@endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
