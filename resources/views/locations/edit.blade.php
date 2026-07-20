@extends('layouts.app')

@section('title', 'Edit Lokasi')
@section('page-title', 'Edit Lokasi')
@section('page-subtitle', $location->code)

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('locations.update', $location) }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gudang *</label>
            <select name="warehouse_id" class="w-full rounded-xl border-gray-200" required>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" @selected(old('warehouse_id', $location->warehouse_id) == $wh->id)>{{ $wh->code }} — {{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode *</label>
                <input type="text" name="code" value="{{ old('code', $location->code) }}" class="w-full rounded-xl border-gray-200 font-mono uppercase" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
                <select name="type" class="w-full rounded-xl border-gray-200" required>
                    @foreach(['zone','rack','bin'] as $t)
                        <option value="{{ $t }}" @selected(old('type', $location->type)===$t)>{{ \App\Models\Location::typeLabel($t) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
            <input type="text" name="name" value="{{ old('name', $location->name) }}" class="w-full rounded-xl border-gray-200" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parent</label>
            <select name="parent_id" class="w-full rounded-xl border-gray-200">
                <option value="">— root —</option>
                @foreach($parents as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_id', $location->parent_id)==$p->id)>{{ $p->code }} ({{ $p->type }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
            <input type="number" min="0" name="capacity" value="{{ old('capacity', $location->capacity) }}" class="w-full rounded-xl border-gray-200">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full rounded-xl border-gray-200">{{ old('description', $location->description) }}</textarea>
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $location->is_active)) class="rounded border-gray-300 text-teal-600">
            Aktif
        </label>
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('locations.show', $location) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-xl">Batal</a>
            <button class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl">Simpan</button>
        </div>
    </form>
</div>
@endsection
