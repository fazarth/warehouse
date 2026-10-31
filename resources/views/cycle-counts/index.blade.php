@extends('layouts.app')

@section('title', 'Cycle Count')
@section('page-title', 'Cycle Count')
@section('page-subtitle', 'Hitung stok per bin (mode cycle)')

@section('content')
<div class="space-y-6">
    <div class="flex justify-end">
        @can('cycle_count.create')
        <a href="{{ route('cycle-counts.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm">+ Cycle Count</a>
        @endcan
    </div>
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Gudang</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($counts as $c)
                <tr>
                    <td class="px-4 py-3 font-mono text-indigo-700">{{ $c->stockopname_number }}</td>
                    <td class="px-4 py-3">{{ $c->warehouse?->code }}</td>
                    <td class="px-4 py-3">{{ $c->stockopname_date?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">{{ $c->status }}</span></td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('cycle-counts.show', $c) }}" class="text-indigo-600">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada cycle count</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $counts->links() }}
</div>
@endsection
