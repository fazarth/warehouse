@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('page-subtitle', 'Jejak aksi sistem — Super Admin only')

@section('content')
<div class="space-y-6">
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-sm text-indigo-900">
        <strong>Scope:</strong> login/logout, kelola user, perubahan permission role, toggle modul.
        Password &amp; token selalu di-redact. Hanya role <em>Super Admin</em> yang bisa membuka halaman ini.
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-sm font-medium text-indigo-600">Total event</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-sm font-medium text-emerald-600">Hari ini</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['today']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-sm font-medium text-amber-700">Login tercatat</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['logins']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Aksi admin</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['admin']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-sm">🧾</span>
                Daftar Audit
            </h3>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-500">{{ $logs->total() }} hasil filter</span>
                <a href="{{ route('admin.audit-logs.index', array_merge(request()->query(), ['format' => 'csv'])) }}"
                   class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">CSV</a>
                <a href="{{ route('admin.audit-logs.index', array_merge(request()->query(), ['format' => 'xlsx'])) }}"
                   class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold">Excel</a>
                <a href="{{ route('admin.audit-logs.index', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                   class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold">PDF</a>
            </div>
        </div>

        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari deskripsi, user, IP, action..."
                           class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-indigo-400 text-sm">
                </div>
                <div>
                    <select name="action" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-indigo-400 text-sm">
                        <option value="">Semua action</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="user_id" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-indigo-400 text-sm">
                        <option value="">Semua user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-indigo-400 text-sm">
                </div>
                <div class="flex gap-2">
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-indigo-400 text-sm">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium whitespace-nowrap">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4">IP</th>
                        <th class="px-6 py-4 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/80">
                            <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">
                                {{ $log->created_at?->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($log->user)
                                    <div class="font-medium text-slate-800 text-sm">{{ $log->user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $log->user->email }}</div>
                                @else
                                    <span class="text-sm text-slate-400">— system / unknown —</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $log->action_badge_class }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 max-w-md">
                                <div class="truncate" title="{{ $log->description }}">{{ $log->description ?: '—' }}</div>
                                @if($log->model_type)
                                    <div class="text-xs text-slate-400 mt-0.5">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 font-mono">{{ $log->ip_address ?: '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.audit-logs.show', $log) }}"
                                   class="inline-flex px-3 py-1.5 text-sm rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 font-medium">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Belum ada audit log. Login Super Admin atau ubah user/role akan mulai mengisi jejak.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
