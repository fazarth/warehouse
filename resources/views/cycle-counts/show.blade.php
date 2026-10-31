@extends('layouts.app')

@section('title', $count->stockopname_number)
@section('page-title', 'Cycle Count '.$count->stockopname_number)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center flex-wrap gap-3">
        <div>
            <a href="{{ route('cycle-counts.index') }}" class="text-sm text-gray-500">← Cycle Counts</a>
            <p class="text-sm text-gray-500">{{ $count->warehouse?->code }} · mode {{ $count->mode }} · {{ $count->status }}</p>
        </div>
        @if($count->status === 'draft')
        <form method="POST" action="{{ route('cycle-counts.submit', $count) }}" onsubmit="return confirm('Submit cycle count?')">
            @csrf
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm">Submit</button>
        </form>
        @endif
    </div>

    <form method="POST" action="{{ route('cycle-counts.update', $count) }}" class="bg-white rounded-xl border p-6">
        @csrf
        @method('PUT')
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-3 py-2 text-left">SKU</th>
                    <th class="px-3 py-2 text-left">Batch</th>
                    <th class="px-3 py-2 text-right">System</th>
                    <th class="px-3 py-2 text-right">Physical</th>
                    <th class="px-3 py-2 text-right">Diff</th>
                    <th class="px-3 py-2 text-left">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($count->items as $i => $item)
                <tr>
                    <td class="px-3 py-2">
                        {{ $item->product?->sku }}
                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                    </td>
                    <td class="px-3 py-2">{{ $item->batch_number ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $item->system_qty }}</td>
                    <td class="px-3 py-2 text-right">
                        @if($count->status === 'draft')
                        <input type="number" min="0" name="items[{{ $i }}][physical_qty]" value="{{ $item->physical_qty }}" class="w-24 rounded-lg border-gray-200 text-right">
                        @else
                        {{ $item->physical_qty ?? '—' }}
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">{{ $item->difference ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs text-gray-400">{{ $item->notes }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($count->status === 'draft')
        <div class="mt-4">
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm">Simpan hitungan</button>
        </div>
        @endif
    </form>
</div>
@endsection
