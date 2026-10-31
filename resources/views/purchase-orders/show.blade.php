@extends('layouts.app')

@section('title', $purchaseOrder->po_number)
@section('page-title', $purchaseOrder->po_number)
@section('page-subtitle', $purchaseOrder->supplier?->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('purchase-orders.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← Daftar PO</a>
        <div class="flex gap-2">
            @if(in_array($purchaseOrder->status, ['ordered','partial']))
                <a href="{{ route('goods-receipts.create', ['purchase_order_id' => $purchaseOrder->id]) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm">Buat GR dari PO</a>
            @endif
            @if(!in_array($purchaseOrder->status, ['received','cancelled']))
                <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Batalkan PO?')">
                    @csrf
                    <button class="px-4 py-2 bg-red-50 text-red-600 rounded-xl text-sm">Batalkan</button>
                </form>
            @endif
            <span class="px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 text-sm">{{ $purchaseOrder->status }}</span>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border p-6 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Supplier</span><span>{{ $purchaseOrder->supplier?->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Order date</span><span>{{ $purchaseOrder->order_date?->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">ETA</span><span>{{ $purchaseOrder->expected_date?->format('d/m/Y') ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Dibuat</span><span>{{ $purchaseOrder->user?->name }}</span></div>
            @if($purchaseOrder->notes)
                <p class="pt-3 border-t text-gray-600">{{ $purchaseOrder->notes }}</p>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl border p-6">
            <h3 class="font-bold mb-4">Items</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Produk</th>
                        <th class="px-3 py-2 text-right">Ordered</th>
                        <th class="px-3 py-2 text-right">Received</th>
                        <th class="px-3 py-2 text-right">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td class="px-3 py-2">
                            <div class="font-medium">{{ $item->product?->name }}</div>
                            <div class="text-xs text-gray-400">{{ $item->product?->sku }}</div>
                        </td>
                        <td class="px-3 py-2 text-right">{{ number_format($item->quantity_ordered) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($item->quantity_received) }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ number_format($item->remaining()) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($purchaseOrder->goodsReceipts->isNotEmpty())
    <div class="bg-white rounded-xl border p-6">
        <h3 class="font-bold mb-3">Goods Receipts</h3>
        <ul class="space-y-2 text-sm">
            @foreach($purchaseOrder->goodsReceipts as $gr)
                <li>
                    <a class="text-indigo-600 hover:underline" href="{{ route('goods-receipts.show', $gr) }}">{{ $gr->receipt_number }}</a>
                    <span class="text-gray-400">· {{ $gr->status }} · QC {{ $gr->qc_status }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
