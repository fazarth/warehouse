@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi')
@section('page-title', 'Pengaturan Aplikasi')
@section('page-subtitle', 'Kustomisasi tampilan dan informasi aplikasi')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100">
            <h3 class="font-bold text-purple-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-sm">⚙️</span>
                Pengaturan Aplikasi
            </h3>
        </div>
        
        <form action="{{ route('settings.app.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <!-- Current Logo Preview -->
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-16 h-16 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg overflow-hidden">
                    @if($settings['app_logo'] ?? null)
                        <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <span class="text-3xl">🏭</span>
                    @endif
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-lg">{{ $settings['app_name'] ?? 'Warehouse Management' }}</p>
                    <p class="text-gray-500 text-sm">{{ $settings['app_tagline'] ?? 'Sistem Manajemen Gudang' }}</p>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Aplikasi</label>
                <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? '') }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('app_name') border-red-300 @enderror" 
                       placeholder="Contoh: Warehouse Management" required>
                @error('app_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tagline / Deskripsi Singkat</label>
                <input type="text" name="app_tagline" value="{{ old('app_tagline', $settings['app_tagline'] ?? '') }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 @error('app_tagline') border-red-300 @enderror" 
                       placeholder="Contoh: Sistem Manajemen Gudang Terpadu">
                @error('app_tagline')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo Aplikasi</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center border-2 border-dashed border-gray-300 overflow-hidden" id="logo-preview">
                        @if($settings['app_logo'] ?? null)
                            <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="Preview" class="w-full h-full object-cover">
                        @else
                            <span class="text-gray-400 text-2xl">📷</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input type="file" name="app_logo" id="app_logo" accept="image/*" class="hidden">
                        <label for="app_logo" class="inline-block px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition cursor-pointer">
                            📤 Pilih Gambar
                        </label>
                        <p class="text-xs text-gray-400 mt-2">Format: JPG, PNG. Maks 2MB</p>
                    </div>
                </div>
                @error('app_logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Warna Utama</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="primary_color" value="{{ old('primary_color', $settings['primary_color'] ?? '#14b8a6') }}" 
                           class="w-12 h-12 rounded-xl border-2 border-gray-200 cursor-pointer">
                    <input type="text" value="{{ $settings['primary_color'] ?? '#14b8a6' }}" 
                           class="flex-1 px-4 py-3 rounded-xl border border-gray-200 bg-gray-50" readonly id="color-display">
                </div>
                <p class="text-xs text-gray-400 mt-2">Warna ini akan digunakan sebagai warna utama aplikasi</p>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition">
                    Simpan Pengaturan
                </button>
                <a href="{{ route('settings.index') }}" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Logo preview
    document.getElementById('app_logo')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-preview').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Color picker sync
    document.querySelector('input[name="primary_color"]')?.addEventListener('input', function(e) {
        document.getElementById('color-display').value = e.target.value;
    });
</script>
@endpush
@endsection
