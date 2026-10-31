@extends('layouts.app')

@section('title', 'Aging Report')
@section('page-title', 'Aging / Expiry Report')
@section('page-subtitle', 'FEFO buckets by expired_date')

@section('content')
<div class="space-y-6">
    <form method="GET" class="flex flex-wrap gap-3 bg-white rounded-xl border p-4">
        <select name="warehouse_id" class="rounded-xl border-gray-200 text-sm">
            <option value="">All warehouses</option>
            @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}" @selected($warehouseId==$wh->id)>{{ $wh->code }}</option>
            @endforeach
        </select>
        <select name="bucket" class="rounded-xl border-gray-200 text-sm">
            <option value="">All buckets</option>
            @foreach(['expired','0-30','31-60','61-90','90+','no_expiry'] as $b)
            <option value="{{ $b }}" @selected($bucket===$b)>{{ $b }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm">Filter</button>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.aging', array_merge(request()->query(), ['format' => 'csv'])) }}"
               class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition text-sm font-medium">CSV</a>
            <a href="{{ route('reports.aging', array_merge(request()->query(), ['format' => 'xlsx'])) }}"
               class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl hover:bg-emerald-100 transition text-sm font-medium">Excel</a>
            <a href="{{ route('reports.aging', array_merge(request()->query(), ['format' => 'pdf'])) }}"
               class="px-4 py-2 bg-rose-50 text-rose-700 rounded-xl hover:bg-rose-100 transition text-sm font-medium">PDF</a>
        </div>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach($summary as $key => $stat)
        <a href="{{ route('reports.aging', array_filter(['warehouse_id'=>$warehouseId,'bucket'=>$key])) }}"
           class="bg-white rounded-xl border p-4 hover:border-indigo-300 {{ $bucket===$key ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="text-xs uppercase text-gray-400">{{ $key }}</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($stat['qty']) }}</div>
            <div class="text-xs text-gray-400">{{ $stat['lines'] }} lines</div>
        </a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-3 py-2 text-left">SKU</th>
                    <th class="px-3 py-2 text-left">WH / Bin</th>
                    <th class="px-3 py-2 text-left">Batch</th>
                    <th class="px-3 py-2 text-left">Exp</th>
                    <th class="px-3 py-2 text-right">Days</th>
                    <th class="px-3 py-2 text-left">Bucket</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($rows as $r)
                <tr class="{{ $r['bucket']==='expired' ? 'bg-red-50' : ($r['bucket']==='0-30' ? 'bg-amber-50' : '') }}">
                    <td class="px-3 py-2 font-medium">{{ $r['sku'] }}</td>
                    <td class="px-3 py-2 font-mono text-xs">{{ $r['warehouse'] }} / {{ $r['location'] }}</td>
                    <td class="px-3 py-2">{{ $r['batch'] ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $r['expired_date'] ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $r['days_to_expiry'] ?? '—' }}</td>
                    <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100">{{ $r['bucket'] }}</span></td>
                    <td class="px-3 py-2 text-right font-semibold">{{ number_format($r['qty']) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No stock rows</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
