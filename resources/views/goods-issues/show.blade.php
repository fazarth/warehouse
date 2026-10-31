@extends('layouts.app')

@section('title', $goodsIssue->issue_number)
@section('page-title', 'Detail pengiriman')
@section('page-subtitle', 'Alokasi by expiry → pick → pack → ship')

@php
    $steps = ['pending', 'picking', 'packing', 'packed', 'shipped', 'delivered'];
    $status = $goodsIssue->status;
    $stepIndex = array_search($status, $steps, true);
    if ($stepIndex === false) { $stepIndex = 0; }
    $stepLabels = [
        'pending' => 'Pending',
        'picking' => 'Picking',
        'packing' => 'Packing',
        'packed' => 'Packed',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
    ];
@endphp

@section('content')
<div class="space-y-5">
    {{-- Title + primary action --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div class="flex items-start gap-3 min-w-0">
            <a href="{{ route('goods-issues.index') }}"
               class="mt-0.5 p-2 rounded-md border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 shrink-0"
               title="Kembali">←</a>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold text-gray-900 tracking-tight">{{ $goodsIssue->issue_number }}</h2>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium border
                        {{ in_array($status, ['delivered']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                        {{ in_array($status, ['shipped','packed']) ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                        {{ in_array($status, ['picking','packing','pending']) ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                        {{ !in_array($status, ['delivered','shipped','packed','picking','packing','pending']) ? 'bg-gray-50 text-gray-600 border-gray-200' : '' }}">
                        {{ ucfirst($status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $goodsIssue->issue_date->format('d F Y') }}
                    · strategi <span class="font-medium text-gray-700">{{ strtoupper($goodsIssue->strategy ?? 'fefo') }}</span>
                    <span class="text-gray-400">(batch terdekat expired dulu)</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('goods_issue.ship')
                @if($status === 'pending')
                <form action="{{ route('goods-issues.allocate', $goodsIssue) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-3.5 py-2 rounded-lg text-sm font-semibold text-white hover:opacity-90"
                                                        style="background:#4f46e5;"
                                                        title="Buat pick line dari stok bin berurutan expired terdekat">
                                                    Alokasi by expiry
                                                </button>
                </form>
                @endif

                @if($status === 'picking')
                <form action="{{ route('goods-issues.pick', $goodsIssue) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-3.5 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                            onclick="return confirm('Konfirmasi semua pick line selesai?')">
                        Selesai pick
                    </button>
                </form>
                @endif

                @if($status === 'packing')
                <form action="{{ route('goods-issues.pack', $goodsIssue) }}" method="POST" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="text" name="notes" placeholder="Catatan pack"
                           class="px-3 py-2 rounded-md border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                    <button type="submit" class="px-3.5 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Pack
                    </button>
                </form>
                @endif

                @if($status === 'packed')
                <form action="{{ route('goods-issues.ship', $goodsIssue) }}" method="POST"
                      class="flex flex-wrap items-center gap-2"
                      onsubmit="return confirm('Ship & potong stok bin?')">
                    @csrf
                    <input type="text" name="carrier" placeholder="Carrier"
                           class="px-3 py-2 rounded-md border border-gray-200 text-sm w-28 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <input type="text" name="tracking_number" placeholder="Tracking"
                           class="px-3 py-2 rounded-md border border-gray-200 text-sm w-32 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <button type="submit" class="px-3.5 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Ship
                    </button>
                </form>
                @endif

                @if($status === 'shipped')
                <form action="{{ route('goods-issues.deliver', $goodsIssue) }}" method="POST" onsubmit="return confirm('Tandai delivered?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Delivered
                    </button>
                </form>
                @endif
            @endcan
        </div>
    </div>

    {{-- Workflow stepper --}}
    <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 overflow-x-auto">
        <ol class="flex items-center gap-1 min-w-max">
            @foreach($steps as $i => $step)
                @php
                    $done = $i < $stepIndex;
                    $current = $i === $stepIndex;
                @endphp
                <li class="flex items-center gap-1">
                    <div class="flex items-center gap-2 px-2 py-1 rounded-md
                        {{ $current ? 'bg-indigo-50 border border-indigo-200' : '' }}">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-semibold
                            {{ $done ? 'bg-emerald-500 text-white' : ($current ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500') }}">
                            {{ $done ? '✓' : ($i + 1) }}
                        </span>
                        <span class="text-xs font-medium {{ $current ? 'text-indigo-800' : ($done ? 'text-gray-700' : 'text-gray-400') }}">
                            {{ $stepLabels[$step] }}
                        </span>
                    </div>
                    @if(!$loop->last)
                    <span class="w-6 h-px {{ $done ? 'bg-emerald-300' : 'bg-gray-200' }}"></span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Informasi</h3>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Tujuan</dt><dd class="font-medium text-gray-900 text-right">{{ $goodsIssue->destination }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Tanggal</dt><dd class="text-gray-800">{{ $goodsIssue->issue_date->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">No. SJ</dt><dd class="text-gray-800">{{ $goodsIssue->delivery_note ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Dibuat</dt><dd class="text-gray-800">{{ $goodsIssue->user->name ?? '—' }}</dd></div>
                @if($goodsIssue->allocated_at)
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Allocated</dt><dd class="text-gray-800">{{ $goodsIssue->allocated_at->format('d/m H:i') }}</dd></div>
                @endif
                @if($goodsIssue->picked_at)
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Picked</dt><dd class="text-gray-800">{{ $goodsIssue->picked_at->format('d/m H:i') }}</dd></div>
                @endif
                @if($goodsIssue->packed_at)
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Packed</dt><dd class="text-gray-800">{{ $goodsIssue->packed_at->format('d/m H:i') }}</dd></div>
                @endif
                @if($goodsIssue->shipped_at)
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Shipped</dt><dd class="text-gray-800 text-right">{{ $goodsIssue->shipped_at->format('d/m H:i') }} · {{ $goodsIssue->carrier ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Tracking</dt><dd class="text-gray-800">{{ $goodsIssue->tracking_number ?? '—' }}</dd></div>
                @endif
            </dl>
            @if($goodsIssue->notes)
            <div class="mt-3 pt-3 border-t border-gray-100 text-sm text-gray-600">{{ $goodsIssue->notes }}</div>
            @endif
        </section>

        <section class="lg:col-span-2 rounded-lg border border-gray-200 bg-white overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Items ({{ $goodsIssue->items->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wide bg-gray-50/80 border-b border-gray-100">
                            <th class="px-3 py-2.5">Produk</th>
                            <th class="px-3 py-2.5">Gudang</th>
                            <th class="px-3 py-2.5 text-right">Qty</th>
                            <th class="px-3 py-2.5 text-right">Alloc</th>
                            <th class="px-3 py-2.5 text-right">Picked</th>
                            <th class="px-3 py-2.5 text-right">Packed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($goodsIssue->items as $item)
                        <tr class="hover:bg-gray-50/80">
                            <td class="px-3 py-2.5">
                                <div class="font-medium text-gray-900">{{ $item->product->name ?? '—' }}</div>
                                <div class="text-xs text-gray-400">{{ $item->product->sku ?? '' }} · {{ $item->batch_number ?? 'any batch' }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-gray-700">{{ $item->warehouse->code ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold text-gray-900">{{ number_format($item->quantity) }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-700">{{ number_format($item->quantity_allocated) }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-700">{{ number_format($item->quantity_picked) }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-700">{{ number_format($item->quantity_packed) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @if($goodsIssue->pickLines->isNotEmpty())
    <section class="rounded-lg border border-gray-200 bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Pick lines (by expiry)</h3>
            <p class="text-xs text-gray-500 mt-0.5">Urutan alokasi: expired_date naik · null expiry di belakang</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wide bg-gray-50/80 border-b border-gray-100">
                        <th class="px-3 py-2.5">#</th>
                        <th class="px-3 py-2.5">Produk</th>
                        <th class="px-3 py-2.5">Bin</th>
                        <th class="px-3 py-2.5">Batch / Exp</th>
                        <th class="px-3 py-2.5 text-right">Qty</th>
                        <th class="px-3 py-2.5 text-right">Picked</th>
                        <th class="px-3 py-2.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($goodsIssue->pickLines as $line)
                    <tr class="hover:bg-gray-50/80">
                        <td class="px-3 py-2.5 font-mono text-xs text-gray-500">{{ $line->id }}</td>
                        <td class="px-3 py-2.5 text-gray-900">{{ $line->product?->sku }}</td>
                        <td class="px-3 py-2.5 font-mono text-indigo-700">{{ $line->location?->code }}</td>
                        <td class="px-3 py-2.5">
                            {{ $line->batch_number ?? '—' }}
                            <span class="text-xs text-gray-400">· {{ $line->expired_date?->format('Y-m-d') ?? 'no exp' }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-right">{{ $line->quantity }}</td>
                        <td class="px-3 py-2.5 text-right">{{ $line->quantity_picked }}</td>
                        <td class="px-3 py-2.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium border
                                {{ $line->status==='picked' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($line->status==='allocated' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-gray-50 text-gray-600 border-gray-200') }}">
                                {{ $line->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

    @if($goodsIssue->packages->isNotEmpty())
    <section class="rounded-lg border border-gray-200 bg-white p-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Packages</h3>
        <ul class="divide-y divide-gray-100">
            @foreach($goodsIssue->packages as $pkg)
            <li class="py-2.5 text-sm flex flex-wrap gap-x-2 gap-y-1">
                <span class="font-mono text-indigo-700">{{ $pkg->package_number }}</span>
                <span class="text-gray-400">· {{ $pkg->items->count() }} lines · {{ $pkg->items->sum('quantity') }} qty</span>
                @if($pkg->notes)<span class="text-gray-500">· {{ $pkg->notes }}</span>@endif
            </li>
            @endforeach
        </ul>
    </section>
    @endif
</div>
@endsection
