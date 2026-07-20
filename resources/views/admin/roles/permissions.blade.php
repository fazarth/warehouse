@extends('layouts.app')

@section('title', 'Permission · ' . $role->name)
@section('page-title', 'Permission: ' . $role->name)
@section('page-subtitle', 'Centang permission yang diizinkan untuk role ini')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.roles.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali ke daftar role</a>
        @if($role->name === 'Super Admin')
            <span class="text-xs px-3 py-1 rounded-full bg-amber-100 text-amber-800">
                Super Admin biasanya punya semua permission
            </span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($permissions as $group => $items)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-3 bg-gradient-to-r from-slate-50 to-teal-50 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 capitalize">{{ str_replace('_', ' ', $group) }}</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($items as $perm)
                <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-teal-200 hover:bg-teal-50/40 cursor-pointer">
                    <input type="checkbox"
                           name="permissions[]"
                           value="{{ $perm->name }}"
                           class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                           @checked(in_array($perm->name, $rolePermissions, true))>
                    <span class="text-sm font-mono text-gray-700">{{ $perm->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold">
                Simpan Permission
            </button>
        </div>
    </form>
</div>
@endsection
