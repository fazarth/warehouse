@extends('layouts.app')

@section('title', 'Kelola Modul')
@section('page-title', 'Kelola Modul')
@section('page-subtitle', 'Aktifkan atau nonaktifkan fitur aplikasi')

@section('content')
<div class="space-y-6">
    <!-- Modules Grid -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-sm">🧩</span>
                Daftar Modul
            </h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modules as $module)
                <div class="border border-gray-200 rounded-xl p-4 {{ !$module->is_licensed ? 'opacity-60 bg-gray-50' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl
                                {{ $module->is_accessible ? 'bg-indigo-50' : 'bg-gray-100' }}">
                                {{ $module->icon }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">{{ $module->name }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 text-xs rounded-full
                                        {{ $module->package === 'basic' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $module->package === 'pro' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $module->package === 'full' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                        {{ ucfirst($module->package) }}
                                    </span>
                                    @if($module->is_core)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Core</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Toggle Switch -->
                        @if(!$module->is_core && $module->is_licensed)
                        <form action="{{ route('admin.modules.toggle', $module->code) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                                        {{ $module->is_enabled ? 'bg-indigo-500' : 'bg-gray-300' }}"
                                    title="{{ $module->is_enabled ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-md transition-transform
                                    {{ $module->is_enabled ? 'translate-x-6' : 'translate-x-1' }}">
                                </span>
                            </button>
                        </form>
                        @elseif($module->is_core)
                        <div class="text-indigo-500 text-sm font-medium">✓ Aktif</div>
                        @else
                        <div class="text-gray-400 text-xs">🔒</div>
                        @endif
                    </div>
                    
                    <p class="text-sm text-gray-500 mt-3">{{ $module->description }}</p>
                    
                    @if(!$module->is_licensed)
                    <div class="mt-3 p-2 bg-amber-50 rounded-lg text-xs text-amber-700">
                        🔒 Tersedia di paket {{ ucfirst($module->package) }} ke atas
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
