@extends('layouts.app')

@section('title', $goodsReceipt->receipt_number)
@section('page-title', 'Detail Penerimaan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-4">
            <a href="{{ route('goods-receipts.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">←</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $goodsReceipt->receipt_number }}</h2>
                <p class="text-gray-500">{{ $goodsReceipt->receipt_date->format('d F Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-2 text-sm font-medium rounded-xl {{ $goodsReceipt->status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($goodsReceipt->status) }}
            </span>
            <span class="px-3 py-2 text-sm font-medium rounded-xl bg-blue-50 text-blue-700">QC: {{ $goodsReceipt->qc_status }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Informasi</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Supplier</dt>
                    <dd class="font-medium">{{ $goodsReceipt->supplier->name ?? '-' }}</dd>
                </div>
                @if($goodsReceipt->purchaseOrder)
                <div class="flex justify-between">
                    <dt class="text-gray-500">PO</dt>
                    <dd>
                        <a class="text-teal-600 hover:underline" href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}">
                            {{ $goodsReceipt->purchaseOrder->po_number }}
                        </a>
                    </dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Tanggal</dt>
                    <dd>{{ $goodsReceipt->receipt_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">No. SJ</dt>
                    <dd>{{ $goodsReceipt->document_number ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Dibuat</dt>
                    <dd>{{ $goodsReceipt->user->name ?? '-' }}</dd>
                </div>
                @if($goodsReceipt->status === 'verified')
                <div class="flex justify-between">
                    <dt class="text-gray-500">QC/Verify</dt>
                    <dd>{{ $goodsReceipt->qcUser->name ?? $goodsReceipt->verifier->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Waktu</dt>
                    <dd>{{ $goodsReceipt->qc_at?->format('d/m/Y H:i') ?? $goodsReceipt->verified_at?->format('d/m/Y H:i') }}</dd>
                </div>
                @endif
            </dl>
            @if($goodsReceipt->notes)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500">Catatan:</p>
                <p class="text-sm text-gray-700">{{ $goodsReceipt->notes }}</p>
            </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Daftar Item ({{ $goodsReceipt->items->count() }})</h3>

            @if($goodsReceipt->status === 'pending' && auth()->user()->can('goods_receipt.verify'))
                        <form method="POST" action="{{ route('goods-receipts.verify', $goodsReceipt) }}" class="space-y-4">
                            @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Produk</th>
                                <th class="px-3 py-2 text-left">Gudang</th>
                                <th class="px-3 py-2 text-right">Qty</th>
                                <th class="px-3 py-2 text-right">Pass</th>
                                <th class="px-3 py-2 text-right">Fail</th>
                                <th class="px-3 py-2 text-left">QC note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($goodsReceipt->items as $i => $item)
                            <tr>
                                <td class="px-3 py-2">
                                    <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                                    <div class="font-medium">{{ $item->product->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->product->sku ?? '' }} · {{ $item->batch_number ?? '-' }}</div>
                                </td>
                                <td class="px-3 py-2">{{ $item->warehouse->code ?? '-' }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ number_format($item->quantity) }}</td>
                                <td class="px-3 py-2 text-right">
                                    <input type="number" min="0" max="{{ $item->quantity }}" name="items[{{ $i }}][qty_pass]" value="{{ old('items.'.$i.'.qty_pass', $item->quantity) }}" required class="w-20 rounded-lg border-gray-200 text-sm text-right">
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <input type="number" min="0" max="{{ $item->quantity }}" name="items[{{ $i }}][qty_fail]" value="{{ old('items.'.$i.'.qty_fail', 0) }}" class="w-20 rounded-lg border-gray-200 text-sm text-right">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" name="items[{{ $i }}][qc_notes]" value="{{ old('items.'.$i.'.qc_notes') }}" class="w-full rounded-lg border-gray-200 text-sm" placeholder="opsional">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="skip_qc" value="1" class="rounded border-gray-300"> Skip QC (semua pass)
                </label>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl" onclick="return confirm('QC + verifikasi GR? Stok pass masuk staging & putaway dibuat.')">
                    ✓ QC & Verifikasi
                </button>
            </form>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Produk</th>
                            <th class="px-3 py-2 text-left">Gudang</th>
                            <th class="px-3 py-2 text-left">Batch</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Pass</th>
                            <th class="px-3 py-2 text-right">Fail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($goodsReceipt->items as $item)
                        <tr>
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $item->product->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $item->product->sku ?? '' }}</div>
                            </td>
                            <td class="px-3 py-2">{{ $item->warehouse->name ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $item->batch_number ?? '-' }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($item->quantity) }}</td>
                            <td class="px-3 py-2 text-right text-green-600 font-semibold">{{ number_format($item->qty_pass ?? 0) }}</td>
                            <td class="px-3 py-2 text-right text-red-500">{{ number_format($item->qty_fail ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    @if($goodsReceipt->putawayTasks->isNotEmpty())
    <div class="bg-white rounded-2xl border p-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold">Put Away Tasks</h3>
            <a href="{{ route('putaways.index') }}" class="text-sm text-teal-600">Semua putaway →</a>
        </div>
        <ul class="space-y-2 text-sm">
            @foreach($goodsReceipt->putawayTasks as $task)
            <li class="flex justify-between border-b py-2">
                <div>
                    <a href="{{ route('putaways.show', $task) }}" class="font-mono text-teal-700 hover:underline">{{ $task->task_number }}</a>
                    <span class="text-gray-400">· {{ $task->product?->sku }} · qty {{ $task->quantity }}</span>
                    <span class="text-gray-400">· suggest {{ $task->suggestedLocation?->code ?? '—' }}</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $task->status==='done'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $task->status }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
