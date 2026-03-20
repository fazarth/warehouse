<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login ke Warehouse Management System">
    <title>Login - Warehouse Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --teal-50: #f0fdfa;
            --teal-100: #ccfbf1;
            --teal-200: #99f6e4;
            --teal-300: #5eead4;
            --teal-400: #2dd4bf;
            --teal-500: #14b8a6;
            --teal-600: #0d9488;
            --teal-700: #0f766e;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-emerald-50 flex items-center justify-center py-12 px-4">
    <!-- Background Pattern -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-200/30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-radial from-teal-100/20 to-transparent rounded-full"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Card Container -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl shadow-teal-200/50 overflow-hidden border border-white/50">
            <!-- Header with Teal gradient -->
            <div class="relative bg-gradient-to-br from-teal-500 via-teal-600 to-emerald-600 px-8 py-10 overflow-hidden">
                <!-- Decorative circles -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-xl"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                
                <div class="text-center relative z-10">
                    <!-- Logo -->
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-xl rounded-2xl mb-5 shadow-2xl">
                        <span class="text-4xl">🏭</span>
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Warehouse Management</h1>
                    <p class="text-teal-100 text-sm mt-2 font-medium">Sistem Manajemen Gudang Terpadu</p>
                </div>
            </div>

            <!-- Form Content -->
            <div class="px-8 py-8 bg-white">
                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-3">
                        <span class="text-lg">⚠️</span>
                        <span class="text-sm font-medium">{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                    @csrf
                    
                    <!-- Email Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="text-teal-500">📧</span>
                                Email Address
                            </span>
                        </label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required
                               autocomplete="email"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('email') border-red-300 @enderror" 
                               placeholder="nama@warehouse.com" />
                        @error('email')
                            <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div x-data="{ show: false }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="text-teal-500">🔒</span>
                                Password
                            </span>
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" 
                                   name="password" 
                                   required
                                   autocomplete="current-password"
                                   class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('password') border-red-300 @enderror" 
                                   placeholder="••••••••" />
                            <button type="button" 
                                    @click="show = !show"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-teal-600 transition-colors">
                                <span x-show="!show">👁️</span>
                                <span x-show="show">🙈</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input id="remember" 
                                   type="checkbox" 
                                   name="remember" 
                                   class="w-5 h-5 rounded-lg border-2 border-gray-300 text-teal-600 focus:ring-teal-400 focus:ring-offset-0 cursor-pointer transition-colors">
                            <span class="text-sm text-gray-600 group-hover:text-teal-600 transition-colors">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-3.5 text-base mt-2 bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-semibold rounded-xl shadow-lg shadow-teal-500/30 transition group">
                        <span class="mr-2 group-hover:scale-110 transition-transform inline-block">🚀</span>
                        Masuk ke Dashboard
                    </button>
                </form>
            </div>

            <!-- Footer with Test Credentials -->
            <div class="px-8 py-5 bg-gradient-to-r from-gray-50 to-teal-50/30 border-t border-gray-100">
                <p class="text-xs text-gray-600 mb-3 font-semibold flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-teal-100 text-teal-700 rounded-full text-[10px] uppercase tracking-wide">Demo</span>
                    Test Credentials
                </p>
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-3 py-2.5 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-teal-200 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-teal-100 rounded-lg flex items-center justify-center text-xs">👤</span>
                            <span class="text-xs font-medium text-gray-700">superadmin@fazarth.my.id</span>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">superadmin123!</span>
                    </div>
                    <div class="flex items-center justify-between px-3 py-2.5 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-teal-200 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center text-xs">👔</span>
                            <span class="text-xs font-medium text-gray-700">admin.gudang@fazarth.my.id</span>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">password</span>
                    </div>
                    <div class="flex items-center justify-between px-3 py-2.5 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-teal-200 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-purple-100 rounded-lg flex items-center justify-center text-xs">🧑</span>
                            <span class="text-xs font-medium text-gray-700">operator@fazarth.my.id</span>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">password</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Text -->
        <div class="text-center mt-8">
            <p class="text-sm text-gray-500">
                © 2026 <span class="font-semibold text-teal-600">fazarth.my.id</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Warehouse Management System 🏭
            </p>
        </div>
    </div>
    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
