@extends('layouts.app')

@section('title', 'Pengiriman Barang')
@section('page-title', 'Pengiriman Barang')
@section('page-subtitle', 'Kelola pengiriman barang ke tujuan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex gap-2">
            <select name="status" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition">🔍</button>
        </form>
        <a href="{{ route('goods-issues.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition">
            <span>📤</span> Buat Pengiriman
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">No. Pengiriman</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Tujuan</th>
                        <th class="px-6 py-4">Items</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($issues as $issue)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('goods-issues.show', $issue) }}" class="font-mono text-sm font-semibold text-orange-600 hover:underline">
                                {{ $issue->issue_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $issue->issue_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $issue->destination }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-gray-100 rounded-full">{{ $issue->items->count() }} item</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($issue->status === 'delivered')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">✓ Delivered</span>
                            @elseif($issue->status === 'shipped')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">🚚 Shipped</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">⏳ Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('goods-issues.show', $issue) }}" class="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition">👁️</a>
                                @if($issue->status === 'pending')
                                <form action="{{ route('goods-issues.ship', $issue) }}" method="POST" onsubmit="return confirm('Kirim barang ini?')">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Kirim">🚚</button>
                                </form>
                                @elseif($issue->status === 'shipped')
                                <form action="{{ route('goods-issues.deliver', $issue) }}" method="POST" onsubmit="return confirm('Konfirmasi terkirim?')">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Delivered">✓</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📤</div>
                            <p>Belum ada pengiriman</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($issues->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $issues->links() }}</div>
        @endif
    </div>
</div>
@endsection
