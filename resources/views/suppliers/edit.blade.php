@extends('layouts.app')

@section('title', 'Edit Supplier')
@section('page-title', 'Edit Supplier')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h3 class="text-lg font-bold text-slate-800">Edit: {{ $supplier->name }}</h3>
        </div>
        
        <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="p-6 space-y-6">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode *</label>
                    <input type="text" name="code" value="{{ old('code', $supplier->code) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Supplier *</label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                <textarea name="address" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">{{ old('address', $supplier->address) }}</textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
            </div>
            
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $supplier->is_active ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>
            
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition">Simpan</button>
                <a href="{{ route('suppliers.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
