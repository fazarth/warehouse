@extends('layouts.app')

@section('title', 'Lokasi (Zone / Rak / Bin)')
@section('page-title', 'Lokasi Gudang')
@section('page-subtitle', 'Hierarki zona → rak → bin + QR')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Gudang</label>
                <select name="warehouse_id" class="rounded-xl border-gray-200 text-sm" onchange="this.form.submit()">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" @selected($warehouseId == $wh->id)>{{ $wh->code }} — {{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipe</label>
                <select name="type" class="rounded-xl border-gray-200 text-sm" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="zone" @selected(request('type')==='zone')>Zona</option>
                    <option value="rack" @selected(request('type')==='rack')>Rak</option>
                    <option value="bin" @selected(request('type')==='bin')>Bin</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode / nama" class="rounded-xl border-gray-200 text-sm">
            </div>
            <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm">Filter</button>
        </form>
        @can('location.create')
        <a href="{{ route('locations.create', ['warehouse_id' => $warehouseId]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl">
            ➕ Tambah Lokasi
        </a>
        @endcan
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Nama</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">Parent</th>
                        <th class="px-6 py-4">QR</th>
                        <th class="px-6 py-4">Kapasitas</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($locations as $loc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-sm font-semibold text-teal-600">{{ $loc->code }}</td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-800">{{ $loc->name }}</p>
                            <p class="text-xs text-gray-400">{{ $loc->warehouse?->code }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-teal-50 text-teal-700">{{ \App\Models\Location::typeLabel($loc->type) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $loc->parent?->code ?? '—' }}</td>
                        <td class="px-6 py-4 font-mono text-xs">{{ $loc->qrCode?->code ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $loc->capacity ?? '∞' }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('locations.show', $loc) }}" class="p-2 hover:bg-teal-50 rounded-lg" title="Detail">👁️</a>
                                <a href="{{ route('locations.label', $loc) }}" class="p-2 hover:bg-teal-50 rounded-lg" title="Label">🖨️</a>
                                <a href="{{ route('locations.edit', $loc) }}" class="p-2 hover:bg-teal-50 rounded-lg" title="Edit">✏️</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📍</div>
                            <p>Belum ada lokasi. Mulai dari zona → rak → bin.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($locations->hasPages())
        <div class="px-6 py-4 border-t">{{ $locations->links() }}</div>
        @endif
    </div>
</div>
@endsection
