<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login ke Enterprise WMS">
    <title>Login — Enterprise WMS</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            font-feature-settings: "cv01", "ss03";
            background: #f8fafc;
            color: #0f172a;
        }
        .field {
            width: 100%;
            padding: .7rem .875rem;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            background: #fff;
            font-size: .875rem;
        }
        .field:focus {
            outline: none;
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-10 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 pt-7 pb-5 border-b border-slate-100 text-center">
                <div class="inline-flex items-center justify-center w-11 h-11 rounded-lg text-white text-sm font-bold mb-3 overflow-hidden"
                     style="background:#4f46e5;">
                    <img src="{{ asset('icons/wms-logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <h1 class="text-lg font-semibold text-slate-900 tracking-tight">Enterprise WMS</h1>
                <p class="text-sm text-slate-500 mt-1">Sistem Manajemen Gudang</p>
            </div>

            <div class="px-6 py-6">
                @if($errors->any())
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               class="field @error('email') border-red-300 @enderror"
                               placeholder="nama@warehouse.com" />
                        @error('email')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                   class="field pr-12 @error('password') border-red-300 @enderror"
                                   placeholder="••••••••" />
                            <button type="button" @click="show = !show"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400 hover:text-slate-600">
                                <span x-show="!show">Show</span>
                                <span x-show="show" style="display:none;">Hide</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input id="remember" type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-600">Ingat saya</span>
                    </label>

                    <button type="submit"
                            class="w-full py-2.5 text-sm font-semibold text-white rounded-lg"
                            style="background:#4f46e5;">
                        Masuk
                    </button>
                </form>
            </div>

            @if(config('app.debug'))
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-2">Demo role (local)</p>
                <div class="space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between gap-2"><span>Super Admin</span><span class="font-mono text-slate-500">superadmin@…</span></div>
                    <div class="flex justify-between gap-2"><span>Admin Gudang</span><span class="font-mono text-slate-500">admin.gudang@…</span></div>
                    <div class="flex justify-between gap-2"><span>Supervisor</span><span class="font-mono text-slate-500">supervisor@…</span></div>
                    <div class="flex justify-between gap-2"><span>Operator (floor)</span><span class="font-mono text-slate-500">operator@…</span></div>
                </div>
                <p class="mt-2 text-[11px] text-slate-400">Seed password: lihat seeder / docs/RBAC.md (tidak ada register publik).</p>
            </div>
            @endif
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">© {{ date('Y') }} NexWave Tech</p>
    </div>
</body>
</html>
