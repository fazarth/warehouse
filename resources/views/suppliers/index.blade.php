@extends('layouts.app')

@section('title', 'Supplier')
@section('page-title', 'Supplier')
@section('page-subtitle', 'Kelola data supplier')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <p class="text-gray-600">Total {{ $suppliers->total() }} supplier</p>
        @can('supplier.create')
                <a href="{{ route('suppliers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl transition">
                    <span>➕</span> Tambah Supplier
                </a>
                @endcan
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Nama Supplier</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Telepon</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-semibold text-teal-600">{{ $supplier->code }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-800">{{ $supplier->name }}</p>
                            <p class="text-xs text-gray-500">{{ $supplier->email ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $supplier->contact_person ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $supplier->phone ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($supplier->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('supplier.edit')
                                                                <a href="{{ route('suppliers.edit', $supplier) }}" class="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition">✏️</a>
                                                                @endcan
                                                                @can('supplier.delete')
                                                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Hapus supplier ini?')">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">🗑️</button>
                                                                </form>
                                                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">🚚</div>
                            <p>Belum ada supplier</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>
@endsection
