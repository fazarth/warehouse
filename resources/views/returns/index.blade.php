@extends('layouts.app')

@section('title', 'Stock Returns')
@section('page-title', 'Stock Returns')
@section('page-subtitle', 'Customer / supplier return → bin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <form method="GET" class="flex gap-2">
            <select name="status" class="rounded-xl border-gray-200 text-sm" onchange="this.form.submit()">
                <option value="">All status</option>
                @foreach(['draft','received','cancelled'] as $st)
                <option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
                @endforeach
            </select>
        </form>
        @can('return.create')
        <a href="{{ route('returns.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm">+ Buat Return</a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Source</th>
                    <th class="px-4 py-3 text-left">Gudang</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($returns as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-indigo-700">{{ $r->return_number }}</td>
                    <td class="px-4 py-3">{{ $r->source }}</td>
                    <td class="px-4 py-3">{{ $r->warehouse?->code }}</td>
                    <td class="px-4 py-3">{{ $r->return_date?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $r->status==='received'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $r->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('returns.show', $r) }}" class="text-indigo-600 hover:underline">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada return</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $returns->withQueryString()->links() }}
</div>
@endsection
