@extends('layouts.app')

@section('title', 'Laporan Mutasi')
@section('page-title', 'Laporan Mutasi Stok')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2 rounded-xl border border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2 rounded-xl border border-gray-200">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                <select name="type" class="px-4 py-2 rounded-xl border border-gray-200">
                    <option value="">Semua</option>
                    <option value="goods_receipt" {{ request('type') == 'goods_receipt' ? 'selected' : '' }}>Penerimaan</option>
                    <option value="goods_issue" {{ request('type') == 'goods_issue' ? 'selected' : '' }}>Pengiriman</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="stockopname" {{ request('type') == 'stockopname' ? 'selected' : '' }}>Stock Opname</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition">🔍 Filter</button>
                        <a href="{{ route('reports.mutations', array_merge(request()->query(), ['export' => 1])) }}"
                           class="px-6 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 transition">⬇ CSV</a>
                    </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Dari</th>
                        <th class="px-6 py-4">Ke</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4 text-right">Qty</th>
                        <th class="px-6 py-4">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mutations as $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800">{{ $m->product->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $m->product->sku ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $m->fromWarehouse->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $m->toWarehouse->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded bg-gray-100">{{ ucfirst(str_replace('_', ' ', $m->reference_type)) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold {{ $m->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $m->quantity > 0 ? '+' : '' }}{{ number_format($m->quantity) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $m->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mutations->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $mutations->links() }}</div>
        @endif
    </div>
</div>
@endsection
