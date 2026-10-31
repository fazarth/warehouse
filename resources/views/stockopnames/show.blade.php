@extends('layouts.app')

@section('title', $stockopname->stockopname_number)
@section('page-title', 'Detail Stock Opname')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('stockopnames.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">←</a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $stockopname->stockopname_number }}</h2>
                <p class="text-gray-500">{{ $stockopname->stockopname_date->format('d F Y') }} • {{ $stockopname->warehouse->name ?? '-' }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            @if($stockopname->status === 'draft')
                        @can('stockopname.create')
                        <form action="{{ route('stockopnames.submit', $stockopname) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition">Submit</button>
                        </form>
                        @endcan
                        @elseif($stockopname->status === 'pending_approval')
                        @can('stockopname.approve')
                        <form action="{{ route('stockopnames.approve', $stockopname) }}" method="POST" class="inline" onsubmit="return confirm('Approve?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition">Approve</button>
                        </form>
                        <form action="{{ route('stockopnames.reject', $stockopname) }}" method="POST" class="inline" onsubmit="return confirm('Reject?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition">✗ Reject</button>
                        </form>
                        @endcan
                        @endif
            
            <span class="px-3 py-2 text-sm font-medium rounded-xl 
                {{ $stockopname->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                {{ $stockopname->status === 'pending_approval' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $stockopname->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                {{ $stockopname->status === 'draft' ? 'bg-gray-100 text-gray-600' : '' }}">
                {{ ucfirst(str_replace('_', ' ', $stockopname->status)) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Informasi</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Gudang</dt>
                    <dd class="font-medium">{{ $stockopname->warehouse->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Tanggal</dt>
                    <dd>{{ $stockopname->stockopname_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Dibuat</dt>
                    <dd>{{ $stockopname->user->name ?? '-' }}</dd>
                </div>
                @if($stockopname->status === 'approved' || $stockopname->status === 'rejected')
                <div class="flex justify-between">
                    <dt class="text-gray-500">{{ $stockopname->status === 'approved' ? 'Approved' : 'Rejected' }} oleh</dt>
                    <dd>{{ $stockopname->approver->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Waktu</dt>
                    <dd>{{ $stockopname->approved_at?->format('d/m/Y H:i') }}</dd>
                </div>
                @endif
            </dl>
            
            @if($stockopname->notes)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500">Catatan:</p>
                <p class="text-sm text-gray-700">{{ $stockopname->notes }}</p>
            </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Daftar Item ({{ $stockopname->items->count() }})</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-right">Sistem</th>
                            <th class="px-4 py-3 text-right">Fisik</th>
                            <th class="px-4 py-3 text-right">Selisih</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($stockopname->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $item->product->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->product->sku ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item->system_qty) }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($item->physical_qty) }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $item->difference > 0 ? 'text-green-600' : ($item->difference < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                {{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="px-4 py-3 font-medium text-right">Total:</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($stockopname->items->sum('system_qty')) }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($stockopname->items->sum('physical_qty')) }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $stockopname->items->sum('difference') >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $stockopname->items->sum('difference') >= 0 ? '+' : '' }}{{ number_format($stockopname->items->sum('difference')) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
