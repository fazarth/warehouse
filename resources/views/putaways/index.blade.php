@extends('layouts.app')

@section('title', 'Put Away')
@section('page-title', 'Put Away Tasks')
@section('page-subtitle', 'Pindah stok staging → bin')

@section('content')
<div class="space-y-6">
    <form method="GET" class="flex gap-3">
        <select name="status" class="rounded-xl border-gray-200 text-sm" onchange="this.form.submit()">
            @foreach(['pending','done','cancelled'] as $st)
                <option value="{{ $st }}" @selected((request('status','pending'))===$st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </form>

    <div class="bg-white rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Task</th>
                    <th class="px-4 py-3 text-left">Produk</th>
                    <th class="px-4 py-3 text-left">GR</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3 text-left">Suggested</th>
                    <th class="px-4 py-3 text-left">Actual</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($tasks as $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-teal-700">{{ $task->task_number }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $task->product?->name }}</div>
                        <div class="text-xs text-gray-400">{{ $task->batch_number }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $task->goodsReceipt?->receipt_number }}</td>
                    <td class="px-4 py-3 text-right">{{ $task->quantity_done }}/{{ $task->quantity }}</td>
                    <td class="px-4 py-3 font-mono">{{ $task->suggestedLocation?->code ?? '—' }}</td>
                    <td class="px-4 py-3 font-mono">{{ $task->location?->code ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('putaways.show', $task) }}" class="text-teal-600 hover:underline">
                            {{ $task->status === 'pending' ? 'Proses' : 'Detail' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Tidak ada task.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($tasks->hasPages())
        <div class="px-4 py-3 border-t">{{ $tasks->links() }}</div>
        @endif
    </div>
</div>
@endsection
