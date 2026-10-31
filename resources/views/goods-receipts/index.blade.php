@extends('layouts.app')

@section('title', 'Penerimaan Barang')
@section('page-title', 'Penerimaan Barang')
@section('page-subtitle', 'Kelola penerimaan barang dari supplier')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="status" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                <option value="">Semua status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
            </select>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            <button type="submit" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>
        @can('goods_receipt.create')
        <a href="{{ route('goods-receipts.create') }}"
           class="inline-flex items-center justify-center px-3.5 py-2 rounded-lg text-sm font-semibold text-white"
           style="background:#4f46e5;">
            + Buat penerimaan
        </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-3">No. penerimaan</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receipts as $receipt)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3">
                            <a href="{{ route('goods-receipts.show', $receipt) }}" class="font-mono text-sm font-semibold text-indigo-600 hover:underline">
                                {{ $receipt->receipt_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $receipt->receipt_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $receipt->supplier->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-[11px] font-medium bg-slate-50 border border-slate-200 rounded-full text-slate-600">{{ $receipt->items->count() }} item</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($receipt->status === 'verified')
                            <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Verified</span>
                            @else
                            <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-xs font-medium text-slate-600 hover:text-indigo-600 px-2 py-1 rounded-md hover:bg-indigo-50">Detail</a>
                                @if($receipt->status === 'pending')
                                @can('goods_receipt.verify')
                                <form action="{{ route('goods-receipts.verify', $receipt) }}" method="POST" onsubmit="return confirm('Verifikasi penerimaan ini? Stok akan diperbarui.')">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-indigo-600 hover:bg-indigo-50 px-2 py-1 rounded-md">Verify</button>
                                </form>
                                @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400">Belum ada penerimaan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $receipts->links() }}</div>
        @endif
    </div>
</div>
@endsection
