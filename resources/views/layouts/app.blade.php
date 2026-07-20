<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $appName = \App\Models\AppSetting::get('app_name', 'Warehouse Management');
        $appTagline = \App\Models\AppSetting::get('app_tagline', 'Sistem Manajemen Gudang');
        $appLogo = \App\Models\AppSetting::get('app_logo');
        $appFavicon = \App\Models\AppSetting::get('app_favicon');
        $primaryColor = \App\Models\AppSetting::get('primary_color', '#14B8A6');
    @endphp
    <meta name="description" content="{{ $appName }} - {{ $appTagline }}">
    <title>@yield('title', $appName)</title>
    
    <!-- Favicon -->
    @if($appFavicon)
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $appFavicon) }}">
    @else
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📦</text></svg>">
    @endif
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Warehouse Teal Theme -->
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --primary-50: #f0fdfa;
            --primary-100: #ccfbf1;
            --primary-200: #99f6e4;
            --primary-500: #14B8A6;
            --primary-600: #0d9488;
            --primary-700: #0f766e;
            --primary-800: #115e59;
        }
        .warehouse-gradient { background: linear-gradient(135deg, #0f766e 0%, #14b8a6 50%, #2dd4bf 100%); }
        .warehouse-sidebar { background: linear-gradient(180deg, #0f766e 0%, #115e59 100%); }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: #2dd4bf;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">
        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden">
        </div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-72 warehouse-sidebar z-50 flex flex-col transform transition-transform duration-300 lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <!-- Logo Section -->
            <div class="flex items-center justify-between h-20 px-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center text-xl shadow-lg overflow-hidden">
                        @if($appLogo)
                            <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo" class="w-full h-full object-cover">
                        @else
                            📦
                        @endif
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">{{ \Illuminate\Support\Str::words($appName, 2, '') }}</h1>
                        <p class="text-xs text-teal-200">{{ \Illuminate\Support\Str::limit($appTagline, 20) }}</p>
                    </div>
                </div>
                <!-- Close button for mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden text-white/60 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- User Mini Profile -->
            <div class="px-4 py-4 border-b border-white/10">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-300 to-teal-500 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-teal-200 capitalize">{{ auth()->user()->getRoleNames()->first() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation (RBAC-filtered via config/menu.php + ModuleService) -->
            <nav class="flex-1 py-6 overflow-y-auto">
                @forelse(($sidebarMenu ?? []) as $groupKey => $group)
                    <p class="px-6 {{ $loop->first ? 'mb-3' : 'mt-6 mb-3' }} text-xs font-semibold text-teal-300 uppercase tracking-wider">
                        {{ $group['label'] ?? ucfirst($groupKey) }}
                    </p>
                    @foreach(($group['items'] ?? []) as $item)
                        @php
                            $routeIs = $item['route_is'] ?? ($item['route'] ?? '');
                            $isActive = $routeIs !== '' && request()->routeIs(...(is_array($routeIs) ? $routeIs : explode('|', $routeIs)));
                        @endphp
                        <a href="{{ $item['url'] }}"
                           class="sidebar-link {{ $isActive ? 'active' : '' }}">
                            <span class="text-xl">{{ $item['icon'] ?? '•' }}</span>
                            <span class="font-medium">{{ $item['title'] }}</span>
                        </a>
                    @endforeach
                @empty
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="text-xl">📊</span>
                        <span class="font-medium">Dashboard</span>
                    </a>
                @endforelse
            </nav>
            
            <!-- Logout Section (fixed at bottom) -->
            <div class="shrink-0 p-4 border-t border-white/10">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-teal-100 hover:text-white transition-all duration-200">
                        <span>🚪</span>
                        <span class="font-medium">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="w-full lg:pl-72">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-gray-100 shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="p-2 text-gray-600 hover:text-teal-600 hover:bg-teal-50 rounded-xl lg:hidden transition-all duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">@yield('page-title')</h2>
                            <p class="text-xs text-gray-500 hidden sm:block">@yield('page-subtitle', 'Kelola data gudang dengan mudah')</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- Date & Time -->
                        <div class="hidden md:flex items-center gap-3 px-4 py-2 bg-gradient-to-r from-teal-50 to-emerald-50 rounded-xl">
                            <span class="text-lg">📅</span>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-teal-800">{{ now()->format('d M Y') }}</p>
                                <p class="text-xs text-teal-600">{{ now()->format('H:i') }} WIB</p>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="p-2.5 bg-gray-100 hover:bg-teal-50 rounded-xl text-gray-600 hover:text-teal-600 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <span>📊</span>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <span>⚙️</span>
                                    <span>Pengaturan</span>
                                </a>
                                <div class="h-px bg-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 w-full">
                                        <span>🚪</span>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            @if(session('success'))
            <div class="mx-6 mt-4" 
                 x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition>
                <div class="flex items-center gap-3 p-4 rounded-xl bg-teal-50 border border-teal-200 text-teal-800">
                    <span class="text-xl flex-shrink-0">✅</span>
                    <span class="font-medium">{{ session('success') }}</span>
                    <button @click="show = false" class="ml-auto text-teal-600 hover:text-teal-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mx-6 mt-4" 
                 x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition>
                <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800">
                    <span class="text-xl flex-shrink-0">❌</span>
                    <span class="font-medium">{{ session('error') }}</span>
                    <button @click="show = false" class="ml-auto text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            @endif

            <!-- Page content -->
            <main class="p-6">
                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="px-6 py-4 border-t border-gray-100 bg-white/50">
                <p class="text-center text-sm text-gray-500">
                    © {{ date('Y') }} Warehouse Management System. Powered by NexWavetech.
                </p>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
