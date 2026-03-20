@extends('layouts.app')

@section('title', 'Ubah Password')
@section('page-title', 'Ubah Password')
@section('page-subtitle', 'Ganti password akun Anda')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-yellow-50 border-b border-amber-100">
            <h3 class="font-bold text-amber-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center text-sm">🔒</span>
                Ubah Password
            </h3>
        </div>
        
        <form action="{{ route('settings.password.update') }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                <p class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>Pastikan password baru minimal 8 karakter dan gunakan kombinasi huruf dan angka.</span>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
                <input type="password" name="current_password" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 @error('current_password') border-red-300 @enderror" 
                       placeholder="Masukkan password saat ini" required>
                @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                <input type="password" name="password" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 @error('password') border-red-300 @enderror" 
                       placeholder="Minimal 8 karakter" required>
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200" 
                       placeholder="Ulangi password baru" required>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-xl transition">
                    🔐 Ubah Password
                </button>
                <a href="{{ route('settings.index') }}" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
