<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $appName = \App\Models\AppSetting::get('app_name', 'Enterprise WMS');
        $appTagline = \App\Models\AppSetting::get('app_tagline', 'Sistem Manajemen Gudang');
        $appLogo = \App\Models\AppSetting::get('app_logo');
        $appFavicon = \App\Models\AppSetting::get('app_favicon');
        $primaryColor = \App\Models\AppSetting::get('primary_color', '#4f46e5');
    @endphp
    <meta name="description" content="{{ $appName }} - {{ $appTagline }}">
    <title>@yield('title', $appName)</title>

    @if($appFavicon)
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $appFavicon) }}">
    @else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary: {{ $primaryColor }};
            --primary-hover: #4338ca;
            --primary-soft: #eef2ff;
            --page: #f8fafc;
            --surface: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --sidebar-w: 16rem;
            --radius: 0.5rem;
            --font: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        }
        body {
            font-family: var(--font);
            background: var(--page);
            color: var(--ink);
            font-feature-settings: "cv01", "ss03";
        }
        .ops-sidebar {
            background: var(--surface);
            border-right: 1px solid var(--line);
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin: 0 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            color: #334155;
            font-size: 0.8125rem;
            font-weight: 500;
            transition: background .12s ease, color .12s ease;
            border: 1px solid transparent;
        }
        .sidebar-link:hover {
            background: #f1f5f9;
            color: var(--ink);
        }
        .sidebar-link.active {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: #c7d2fe;
        }
        .sidebar-group {
            padding: 0.85rem 1rem 0.35rem;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .topbar {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--line);
        }
        .btn-wms {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            padding: .5rem .875rem;
            border-radius: .5rem;
            font-size: .8125rem;
            font-weight: 600;
            background: var(--primary);
            color: #fff;
            border: 1px solid transparent;
        }
        .btn-wms:hover { background: var(--primary-hover); }
        .btn-wms-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            padding: .5rem .875rem;
            border-radius: .5rem;
            font-size: .8125rem;
            font-weight: 600;
            background: #fff;
            color: #334155;
            border: 1px solid var(--line);
        }
        .btn-wms-secondary:hover { background: #f8fafc; }
        .card-wms {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: .75rem;
        }
    </style>
</head>
<body class="min-h-screen">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">
        <div x-show="sidebarOpen"
             x-transition.opacity
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-900/40 z-40 lg:hidden"
             style="display: none;"></div>

        <aside class="ops-sidebar fixed inset-y-0 left-0 w-64 z-50 flex flex-col transform transition-transform duration-200 lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex items-center justify-between h-14 px-4 border-b border-slate-200">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[11px] font-bold overflow-hidden shrink-0 text-white"
                         style="background: var(--primary);">
                        @if($appLogo)
                            <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('icons/wms-logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ \Illuminate\Support\Str::limit($appName, 22) }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ \Illuminate\Support\Str::limit($appTagline, 28) }}</p>
                    </div>
                </div>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-md text-slate-400 hover:bg-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-3 py-3 border-b border-slate-200">
                <div class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-semibold shrink-0 text-white"
                         style="background: var(--primary);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] capitalize text-slate-500 truncate">{{ auth()->user()->getRoleNames()->first() }}</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 py-3 overflow-y-auto">
                @forelse(($sidebarMenu ?? []) as $groupKey => $group)
                    <p class="sidebar-group {{ $loop->first ? '' : 'mt-1' }}">{{ $group['label'] ?? ucfirst($groupKey) }}</p>
                    @foreach(($group['items'] ?? []) as $item)
                        @php
                            $routeIs = $item['route_is'] ?? ($item['route'] ?? '');
                            $isActive = $routeIs !== '' && request()->routeIs(...(is_array($routeIs) ? $routeIs : explode('|', $routeIs)));
                        @endphp
                        <a href="{{ $item['url'] }}" class="sidebar-link {{ $isActive ? 'active' : '' }}">
                            <span class="w-4 text-center text-xs opacity-70">{{ $item['icon'] ?? '·' }}</span>
                            <span class="truncate">{{ $item['title'] }}</span>
                        </a>
                    @endforeach
                @empty
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="w-4 text-center text-xs">·</span>
                        <span>Dashboard</span>
                    </a>
                @endforelse
            </nav>

            <div class="p-3 border-t border-slate-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="w-full lg:pl-64 min-h-screen flex flex-col">
            <header class="topbar sticky top-0 z-30">
                <div class="flex items-center justify-between gap-3 px-4 sm:px-6 h-14">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" @click="sidebarOpen = !sidebarOpen"
                                class="p-2 rounded-lg lg:hidden border border-slate-200 text-slate-600 hover:bg-slate-50 bg-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="min-w-0">
                            <h2 class="text-sm sm:text-base font-semibold text-slate-900 truncate">@yield('page-title')</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500 truncate">@yield('page-subtitle', 'Operasional gudang')</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="hidden md:block text-right px-3 py-1.5 rounded-lg border border-slate-200 bg-white">
                            <p class="text-xs font-medium text-slate-800">{{ now()->format('d M Y') }}</p>
                            <p class="text-[11px] text-slate-500">{{ now()->format('H:i') }} WIB</p>
                        </div>

                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                    class="p-2 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50"
                                 style="display: none;">
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Dashboard</a>
                                <a href="{{ route('settings.index') }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Pengaturan</a>
                                <div class="h-px bg-slate-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">Keluar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @if(session('success'))
            <div class="mx-4 sm:mx-6 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition>
                <div class="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                    <span class="font-medium flex-1">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-emerald-700">✕</button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mx-4 sm:mx-6 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition>
                <div class="flex items-center gap-3 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    <span class="font-medium flex-1">{{ session('error') }}</span>
                    <button type="button" @click="show = false" class="text-red-700">✕</button>
                </div>
            </div>
            @endif

            <main class="flex-1 p-4 sm:p-6">
                @yield('content')
            </main>

            <footer class="px-4 sm:px-6 py-3 border-t border-slate-200 bg-white">
                <p class="text-center text-[11px] text-slate-500">© {{ date('Y') }} {{ $appName }} · NexWave Tech</p>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
