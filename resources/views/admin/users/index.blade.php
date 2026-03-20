@extends('layouts.app')

@section('title', 'Kelola User')
@section('page-title', 'Kelola User')
@section('page-subtitle', 'Kelola pengguna dan hak akses sistem')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-xl p-4 border border-teal-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-teal-600">Total User</p>
                    <p class="text-2xl font-bold text-teal-800 mt-1">{{ $users->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-teal-400 to-teal-600 rounded-xl flex items-center justify-center text-xl shadow-lg">👥</div>
            </div>
        </div>
        @foreach($roles->take(3) as $role)
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ $role->name }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $role->users->count() }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-lg">👤</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- User Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-teal-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="font-bold text-teal-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center text-sm">👤</span>
                Daftar User
            </h3>
            @can('user.create')
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl transition text-sm font-medium">
                ➕ Tambah User
            </a>
            @endcan
        </div>

        <!-- Search & Filter -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Cari nama atau email..." 
                           value="{{ request('search') }}"
                           class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500 text-sm">
                </div>
                <select name="role" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-teal-500 text-sm w-full sm:w-48">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition text-sm">
                    🔍 Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Terdaftar</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-semibold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @foreach($user->roles as $role)
                            <span class="px-2 py-0.5 text-xs rounded-full 
                                {{ $role->name === 'Super Admin' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $role->name === 'Admin Gudang' ? 'bg-teal-100 text-teal-700' : '' }}
                                {{ $role->name === 'Supervisor' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $role->name === 'Operator' ? 'bg-blue-100 text-blue-700' : '' }}">
                                {{ $role->name }}
                            </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-gray-600 text-sm">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2 justify-center">
                                @can('user.edit')
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition">
                                    ✏️
                                </a>
                                @endcan
                                @can('user.delete')
                                @if($user->id !== auth()->id() && !$user->hasRole('Super Admin'))
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        🗑️
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">👤</div>
                            <p>Belum ada user</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
