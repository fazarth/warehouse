@extends('layouts.app')

@section('title', 'Stock Opname')
@section('page-title', 'Stock Opname')
@section('page-subtitle', 'Kelola stock opname gudang')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex gap-2">
            <select name="status" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-indigo-400">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Filter</button>
        </form>
        @can('stockopname.create')
                <a href="{{ route('stockopnames.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition">
                    <span>📋</span> Buat Stock Opname
                </a>
                @endcan
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">No. SO</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Gudang</th>
                        <th class="px-6 py-4">Items</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($stockopnames as $so)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('stockopnames.show', $so) }}" class="font-mono text-sm font-semibold text-purple-600 hover:underline">
                                {{ $so->stockopname_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $so->stockopname_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $so->warehouse->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-gray-100 rounded-full">{{ $so->items->count() }} item</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($so->status === 'approved')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Approved</span>
                            @elseif($so->status === 'pending_approval')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                            @elseif($so->status === 'rejected')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">✗ Rejected</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">📝 Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('stockopnames.show', $so) }}" class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition">Detail</a>
                                @if($so->status === 'draft')
                                                                @can('stockopname.create')
                                                                <form action="{{ route('stockopnames.submit', $so) }}" method="POST" onsubmit="return confirm('Submit untuk approval?')">
                                                                    @csrf
                                                                    <button type="submit" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Submit">📤</button>
                                                                </form>
                                                                @endcan
                                                                @elseif($so->status === 'pending_approval')
                                                                @can('stockopname.approve')
                                                                <form action="{{ route('stockopnames.approve', $so) }}" method="POST" onsubmit="return confirm('Approve stock opname?')">
                                                                    @csrf
                                                                    <button type="submit" class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Approve">OK</button>
                                                                </form>
                                                                <form action="{{ route('stockopnames.reject', $so) }}" method="POST" onsubmit="return confirm('Reject stock opname?')">
                                                                    @csrf
                                                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Reject">✗</button>
                                                                </form>
                                                                @endcan
                                                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📋</div>
                            <p>Belum ada stock opname</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stockopnames->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $stockopnames->links() }}</div>
        @endif
    </div>
</div>
@endsection
