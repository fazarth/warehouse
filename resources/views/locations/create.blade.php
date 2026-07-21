@extends('layouts.app')

@section('title', 'Tambah Lokasi')
@section('page-title', 'Tambah Lokasi')
@section('page-subtitle', 'Zone / Rak / Bin')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('locations.store') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gudang *</label>
            <select name="warehouse_id" class="w-full rounded-xl border-gray-200" required>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" @selected(old('warehouse_id', $selectedWarehouse) == $wh->id)>{{ $wh->code }} — {{ $wh->name }}</option>
                @endforeach
            </select>
            @error('warehouse_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode *</label>
                <input type="text" name="code" value="{{ old('code') }}" class="w-full rounded-xl border-gray-200 font-mono uppercase" placeholder="A-01-03" required>
                @error('code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barcode scan</label>
                <input type="text" name="barcode" value="{{ old('barcode') }}" class="w-full rounded-xl border-gray-200 font-mono uppercase" placeholder="LOC-A-01-03 (opsional)">
                <p class="text-xs text-gray-400 mt-1">Kosong = auto LOC-{kode}. Dipakai scanner HP/Zebra.</p>
                @error('barcode')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
                <select name="type" class="w-full rounded-xl border-gray-200" required>
                    <option value="zone" @selected(old('type')==='zone')>Zona</option>
                    <option value="rack" @selected(old('type')==='rack')>Rak</option>
                    <option value="bin" @selected(old('type', 'bin')==='bin')>Bin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label tampilan</label>
                <input type="text" name="display_name" value="{{ old('display_name') }}" class="w-full rounded-xl border-gray-200" placeholder="Nama di label QR">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border-gray-200" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parent (zona/rak)</label>
            <select name="parent_id" class="w-full rounded-xl border-gray-200">
                <option value="">— root —</option>
                @foreach($parents as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_id')==$p->id)>
                        [{{ $p->warehouse?->code }}] {{ $p->code }} ({{ $p->type }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas (qty, opsional)</label>
            <input type="number" min="0" name="capacity" value="{{ old('capacity') }}" class="w-full rounded-xl border-gray-200">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full rounded-xl border-gray-200">{{ old('description') }}</textarea>
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 text-teal-600">
            Aktif
        </label>
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('locations.index') }}" class="px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-xl">Batal</a>
            <button class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl">Simpan + Generate QR</button>
        </div>
    </form>
</div>
@endsection
