@extends('layouts.app')

@section('title', 'Lokasi Gudang')
@section('page-title', 'Lokasi Gudang')
@section('page-subtitle', 'Kelola lokasi dan rak gudang')

@section('content')
<div class="space-y-6">
    @if(session('import_errors'))
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">
        <p class="font-semibold mb-2">Beberapa baris import dilewati:</p>
        <ul class="list-disc pl-5 space-y-1">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-gray-600">Total {{ $warehouses->total() }} lokasi gudang</p>
        </div>
        @can('warehouse.create')
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('warehouses.import') }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg">
                        ⬆ Import CSV/XLSX
                    </a>
                    <a href="{{ route('warehouses.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold text-white rounded-lg" style="background:#4f46e5;">
                        + Tambah Lokasi
                    </a>
                </div>
                @endcan
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Nama Lokasi</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($warehouses as $warehouse)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm font-semibold text-indigo-600">{{ $warehouse->code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900 text-sm">{{ $warehouse->name }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $warehouse->description ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($warehouse->is_active)
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                @else
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-slate-50 text-slate-600 border border-slate-200">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('warehouse.edit')
                                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-xs font-medium text-slate-600 hover:text-indigo-600 px-2 py-1 rounded-md hover:bg-indigo-50">Edit</a>
                                    @endcan
                                    @can('warehouse.delete')
                                    <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('Hapus lokasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:bg-red-50 px-2 py-1 rounded-md">Hapus</button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                                <p class="text-sm">Belum ada lokasi gudang</p>
                                <a href="{{ route('warehouses.create') }}" class="text-indigo-600 hover:underline text-sm">Tambah lokasi pertama</a>
                            </td>
                        </tr>
                        @endforelse
                </tbody>
            </table>
        </div>
        
        @if($warehouses->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $warehouses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
