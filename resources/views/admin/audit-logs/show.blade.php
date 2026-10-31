@extends('layouts.app')

@section('title', 'Detail Audit Log')
@section('page-title', 'Detail Audit Log')
@section('page-subtitle', 'Event #' . $log->id)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('admin.audit-logs.index') }}"
           class="inline-flex px-3 py-1.5 text-sm rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">
            ← Kembali
        </a>
        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $log->action_badge_class }}">
            {{ $log->action }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-3">
                <h3 class="font-bold text-slate-800">Meta</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Waktu</dt>
                        <dd class="text-slate-800 font-medium">{{ $log->created_at?->format('d M Y H:i:s') }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">User</dt>
                        <dd class="text-slate-800 font-medium text-right">
                            @if($log->user)
                                {{ $log->user->name }}<br>
                                <span class="text-xs text-slate-500">{{ $log->user->email }}</span>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">IP</dt>
                        <dd class="text-slate-800 font-mono">{{ $log->ip_address ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Model</dt>
                        <dd class="text-slate-800 text-right">
                            @if($log->model_type)
                                {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 mb-2">Deskripsi</h3>
                <p class="text-sm text-slate-700">{{ $log->description ?: '—' }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 mb-2">User Agent</h3>
                <p class="text-xs text-slate-600 break-all">{{ $log->user_agent ?: '—' }}</p>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 mb-3">Old values</h3>
                <pre class="text-xs bg-slate-50 border border-slate-100 rounded-xl p-4 overflow-x-auto text-slate-700">{{ $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : 'null' }}</pre>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 mb-3">New values</h3>
                <pre class="text-xs bg-slate-50 border border-slate-100 rounded-xl p-4 overflow-x-auto text-slate-700">{{ $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : 'null' }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
