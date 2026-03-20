@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')
@section('page-subtitle', 'Buat akun pengguna baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-teal-100">
            <h3 class="font-bold text-teal-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center text-sm">➕</span>
                Tambah User Baru
            </h3>
        </div>
        
        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-5">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 @error('name') border-red-300 @enderror" 
                       placeholder="Masukkan nama lengkap" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 @error('email') border-red-300 @enderror" 
                       placeholder="contoh@email.com" required>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 @error('password') border-red-300 @enderror" 
                           placeholder="Minimal 8 karakter" required>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200" 
                           placeholder="Ulangi password" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role / Hak Akses <span class="text-red-500">*</span></label>
                <select name="role" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 @error('role') border-red-300 @enderror" required>
                    <option value="">Pilih Role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
                @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                
                <div class="mt-3 p-3 bg-gray-50 rounded-xl text-sm text-gray-600 space-y-1">
                    <p><strong>Super Admin:</strong> Full system access</p>
                    <p><strong>Admin Gudang:</strong> Warehouse operations management</p>
                    <p><strong>Supervisor:</strong> Approval & oversight</p>
                    <p><strong>Operator:</strong> Daily warehouse transactions</p>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-xl transition">
                    💾 Simpan User
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
