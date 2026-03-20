@extends('layouts.app')

@section('title', $goodsReceipt->receipt_number)
@section('page-title', 'Detail Penerimaan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('goods-receipts.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">←</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $goodsReceipt->receipt_number }}</h2>
                <p class="text-gray-500">{{ $goodsReceipt->receipt_date->format('d F Y') }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            @if($goodsReceipt->status === 'pending')
            <form action="{{ route('goods-receipts.verify', $goodsReceipt) }}" method="POST" onsubmit="return confirm('Verifikasi penerimaan ini?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">
                    ✓ Verifikasi
                </button>
            </form>
            @endif
            
            <span class="px-3 py-2 text-sm font-medium rounded-xl {{ $goodsReceipt->status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($goodsReceipt->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Informasi</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Supplier</dt>
                    <dd class="font-medium">{{ $goodsReceipt->supplier->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Tanggal</dt>
                    <dd>{{ $goodsReceipt->receipt_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">No. SJ</dt>
                    <dd>{{ $goodsReceipt->delivery_note ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Dibuat</dt>
                    <dd>{{ $goodsReceipt->user->name ?? '-' }}</dd>
                </div>
                @if($goodsReceipt->status === 'verified')
                <div class="flex justify-between">
                    <dt class="text-gray-500">Diverifikasi</dt>
                    <dd>{{ $goodsReceipt->verifier->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Waktu Verifikasi</dt>
                    <dd>{{ $goodsReceipt->verified_at?->format('d/m/Y H:i') }}</dd>
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

        <!-- Items -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Daftar Item ({{ $goodsReceipt->items->count() }})</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Lokasi</th>
                            <th class="px-4 py-3">Batch</th>
                            <th class="px-4 py-3">Expired</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($goodsReceipt->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $item->product->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->product->sku ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->warehouse->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $item->batch_number ?? '-' }}
                                @if($item->lot_number)
                                <br><span class="text-xs">Lot: {{ $item->lot_number }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $item->expired_date ? $item->expired_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-green-600">
                                +{{ number_format($item->quantity) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-4 py-3 font-medium text-right">Total Qty:</td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">
                                +{{ number_format($goodsReceipt->items->sum('quantity')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
