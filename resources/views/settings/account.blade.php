@extends('layouts.app')

@section('title', 'Pengaturan Profil')
@section('page-title', 'Pengaturan Profil')
@section('page-subtitle', 'Edit informasi akun Anda')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-sm">👤</span>
                Edit Profil
            </h3>
        </div>
        
        <form action="{{ route('settings.account.update') }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <!-- Avatar Preview -->
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-20 h-20 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-lg">{{ $user->name }}</p>
                    <span class="inline-block px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 rounded-full">{{ $user->getRoleNames()->first() }}</span>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('name') border-red-300 @enderror" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('email') border-red-300 @enderror" required>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('phone') border-red-300 @enderror" placeholder="08xxxxxxxxxx">
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('settings.index') }}" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
