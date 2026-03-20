@extends('layouts.app')

@section('title', 'Penerimaan Barang')
@section('page-title', 'Penerimaan Barang')
@section('page-subtitle', 'Kelola penerimaan barang dari supplier')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex gap-2">
            <select name="status" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition">🔍</button>
        </form>
        <a href="{{ route('goods-receipts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl transition">
            <span>📥</span> Buat Penerimaan
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">No. Penerimaan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Supplier</th>
                        <th class="px-6 py-4">Items</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($receipts as $receipt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('goods-receipts.show', $receipt) }}" class="font-mono text-sm font-semibold text-green-600 hover:underline">
                                {{ $receipt->receipt_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $receipt->receipt_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800">{{ $receipt->supplier->name ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-gray-100 rounded-full">{{ $receipt->items->count() }} item</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($receipt->status === 'verified')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">✓ Verified</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">⏳ Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('goods-receipts.show', $receipt) }}" class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition">👁️</a>
                                @if($receipt->status === 'pending')
                                <form action="{{ route('goods-receipts.verify', $receipt) }}" method="POST" onsubmit="return confirm('Verifikasi penerimaan ini? Stok akan diperbarui.')">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Verifikasi">✓</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📥</div>
                            <p>Belum ada penerimaan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $receipts->links() }}</div>
        @endif
    </div>
</div>
@endsection
