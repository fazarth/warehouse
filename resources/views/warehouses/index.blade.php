@extends('layouts.app')

@section('title', 'Lokasi Gudang')
@section('page-title', 'Lokasi Gudang')
@section('page-subtitle', 'Kelola lokasi dan rak gudang')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-gray-600">Total {{ $warehouses->total() }} lokasi gudang</p>
        </div>
        <a href="{{ route('warehouses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl transition">
            <span>➕</span> Tambah Lokasi
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Nama Lokasi</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($warehouses as $warehouse)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-semibold text-teal-600">{{ $warehouse->code }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-800">{{ $warehouse->name }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $warehouse->description ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($warehouse->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('warehouses.edit', $warehouse) }}" class="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition">
                                    ✏️
                                </a>
                                <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('Hapus lokasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">🏭</div>
                            <p>Belum ada lokasi gudang</p>
                            <a href="{{ route('warehouses.create') }}" class="text-teal-600 hover:underline text-sm">Tambah lokasi pertama</a>
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
