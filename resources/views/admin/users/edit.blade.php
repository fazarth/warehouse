@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', 'Perbarui data dan hak akses pengguna')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-yellow-50 border-b border-amber-100">
            <h3 class="font-bold text-amber-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center text-sm">Edit</span>
                Edit User
            </h3>
        </div>
        
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <!-- User Preview -->
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-16 h-16 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-xl font-bold shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-lg">{{ $user->name }}</p>
                    <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                    <span class="inline-block px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 rounded-full mt-1">{{ $user->getRoleNames()->first() }}</span>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('name') border-red-300 @enderror" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('email') border-red-300 @enderror" required>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <p class="text-sm text-amber-800 font-medium mb-3">🔐 Ganti Password (opsional)</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                        <input type="password" name="password" 
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('password') border-red-300 @enderror" 
                               placeholder="Kosongkan jika tidak ganti">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" 
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" 
                               placeholder="Ulangi password baru">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role / Hak Akses <span class="text-red-500">*</span></label>
                <select name="role" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('role') border-red-300 @enderror" required>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
                @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
