@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')
@section('page-subtitle', 'Kelola pengaturan akun dan aplikasi')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Menu -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 space-y-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 mb-2">Akun Saya</p>
                
                <a href="{{ route('settings.account') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-indigo-50 text-gray-700 hover:text-indigo-700 transition-all {{ request()->routeIs('settings.account') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                    <span class="text-lg">👤</span>
                    <span class="font-medium">Profil</span>
                </a>
                
                <a href="{{ route('settings.password') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-indigo-50 text-gray-700 hover:text-indigo-700 transition-all {{ request()->routeIs('settings.password') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                    <span class="text-lg">🔒</span>
                    <span class="font-medium">Password</span>
                </a>

                @if($isAdmin ?? false)
                <hr class="my-3 border-gray-200">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 mb-2">Admin</p>
                
                <a href="{{ route('settings.app') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-indigo-50 text-gray-700 hover:text-indigo-700 transition-all {{ request()->routeIs('settings.app') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                    <span class="text-lg">⚙️</span>
                    <span class="font-medium">Aplikasi</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-3">
            <!-- Quick Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Account Card -->
                <a href="{{ route('settings.account') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform">
                            👤
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Profil Saya</h3>
                            <p class="text-sm text-gray-500">Edit nama, email, telepon</p>
                        </div>
                    </div>
                </a>

                <!-- Password Card -->
                <a href="{{ route('settings.password') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform">
                            🔒
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Ubah Password</h3>
                            <p class="text-sm text-gray-500">Ganti password akun</p>
                        </div>
                    </div>
                </a>

                @if($isAdmin ?? false)
                <!-- App Settings Card -->
                <a href="{{ route('settings.app') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform">
                            ⚙️
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Pengaturan Aplikasi</h3>
                            <p class="text-sm text-gray-500">Nama, logo, tampilan</p>
                        </div>
                    </div>
                </a>
                @endif
            </div>

            <!-- User Info -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm mt-6 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-sm">ℹ️</span>
                        Informasi Akun
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-800">{{ $user->name }}</p>
                            <p class="text-gray-500">{{ $user->email }}</p>
                            <span class="inline-block px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 rounded-full mt-1">{{ $user->getRoleNames()->first() }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="p-3 bg-gray-50 rounded-xl">
                            <p class="text-gray-500">Terdaftar</p>
                            <p class="font-semibold text-gray-800">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl">
                            <p class="text-gray-500">Terakhir Login</p>
                            <p class="font-semibold text-gray-800">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
