@extends('layouts.app')

@section('title', 'Tambah Lokasi Gudang')
@section('page-title', 'Tambah Lokasi Gudang')
@section('page-subtitle', 'Buat lokasi atau rak baru')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-teal-100">
            <h3 class="text-lg font-bold text-teal-800">Form Lokasi Gudang</h3>
        </div>
        
        <form action="{{ route('warehouses.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode Lokasi *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition"
                           placeholder="Contoh: GDG-A1">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lokasi *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition"
                           placeholder="Contoh: Gudang Utama Rak A1">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition"
                          placeholder="Deskripsi lokasi (opsional)">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                       class="w-5 h-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                <label for="is_active" class="text-sm text-gray-700">Aktif (dapat digunakan)</label>
            </div>
            
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-xl transition">
                    💾 Simpan
                </button>
                <a href="{{ route('warehouses.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
