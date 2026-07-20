@extends('layouts.app')

@section('title', 'Low Stock')
@section('page-title', 'Low Stock Report')
@section('page-subtitle', 'Produk on-hand ≤ min stock')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">{{ $rows->count() }} produk perlu perhatian</p>
        <a href="{{ route('reports.low-stock', ['export' => 1]) }}"
           class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl">
            ⬇ Export CSV
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">SKU</th>
                        <th class="px-6 py-3 text-left">Produk</th>
                        <th class="px-6 py-3 text-right">Min</th>
                        <th class="px-6 py-3 text-right">On Hand</th>
                        <th class="px-6 py-3 text-right">Shortage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $r)
                    <tr class="hover:bg-red-50/40">
                        <td class="px-6 py-3 font-mono text-sm">{{ $r['sku'] }}</td>
                        <td class="px-6 py-3">
                            <div class="font-medium text-gray-800">{{ $r['name'] }}</div>
                            <div class="text-xs text-gray-400">{{ $r['unit'] }}</div>
                        </td>
                        <td class="px-6 py-3 text-right">{{ number_format($r['min_stock']) }}</td>
                        <td class="px-6 py-3 text-right font-semibold text-red-700">{{ number_format($r['on_hand']) }}</td>
                        <td class="px-6 py-3 text-right font-bold text-rose-600">{{ number_format($r['shortage']) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">✅</div>
                            <p>Tidak ada low stock (set min stock di master produk)</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
