@extends('layouts.app')

@section('title', $goodsIssue->issue_number)
@section('page-title', 'Detail Pengiriman')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('goods-issues.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">←</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $goodsIssue->issue_number }}</h2>
                <p class="text-gray-500">{{ $goodsIssue->issue_date->format('d F Y') }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            @if($goodsIssue->status === 'pending')
            <form action="{{ route('goods-issues.ship', $goodsIssue) }}" method="POST" onsubmit="return confirm('Kirim barang?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">🚚 Kirim</button>
            </form>
            @elseif($goodsIssue->status === 'shipped')
            <form action="{{ route('goods-issues.deliver', $goodsIssue) }}" method="POST" onsubmit="return confirm('Konfirmasi terkirim?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl transition">✓ Delivered</button>
            </form>
            @endif
            
            <span class="px-3 py-2 text-sm font-medium rounded-xl 
                {{ $goodsIssue->status === 'delivered' ? 'bg-green-100 text-green-700' : '' }}
                {{ $goodsIssue->status === 'shipped' ? 'bg-blue-100 text-blue-700' : '' }}
                {{ $goodsIssue->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                {{ ucfirst($goodsIssue->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Informasi</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Tujuan</dt>
                    <dd class="font-medium">{{ $goodsIssue->destination }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Tanggal</dt>
                    <dd>{{ $goodsIssue->issue_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">No. SJ</dt>
                    <dd>{{ $goodsIssue->delivery_note ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Dibuat</dt>
                    <dd>{{ $goodsIssue->user->name ?? '-' }}</dd>
                </div>
                @if($goodsIssue->status !== 'pending')
                <div class="flex justify-between">
                    <dt class="text-gray-500">Dikirim oleh</dt>
                    <dd>{{ $goodsIssue->shipper->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Waktu Kirim</dt>
                    <dd>{{ $goodsIssue->shipped_at?->format('d/m/Y H:i') }}</dd>
                </div>
                @endif
            </dl>
            
            @if($goodsIssue->notes)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500">Catatan:</p>
                <p class="text-sm text-gray-700">{{ $goodsIssue->notes }}</p>
            </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Daftar Item ({{ $goodsIssue->items->count() }})</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Dari</th>
                            <th class="px-4 py-3">Batch</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($goodsIssue->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $item->product->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->product->sku ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->warehouse->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->batch_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">
                                -{{ number_format($item->quantity) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 font-medium text-right">Total Qty:</td>
                            <td class="px-4 py-3 text-right font-bold text-red-600">
                                -{{ number_format($goodsIssue->items->sum('quantity')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
