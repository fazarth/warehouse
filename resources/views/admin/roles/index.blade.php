@extends('layouts.app')

@section('title', 'Kelola Role')
@section('page-title', 'Kelola Role')
@section('page-subtitle', 'Lihat role sistem dan jumlah permission (mapping hard-coded di seeder; UI edit permission tersedia)')

@section('content')
<div class="space-y-6">
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-900">
        <strong>Hardening note:</strong> matrix role saat ini di-seed dari
        <code class="bg-amber-100 px-1 rounded">RolePermissionSeeder</code>.
        Super Admin bisa menyesuaikan permission lewat tombol <em>Edit Permission</em>.
        Menu sidebar mengikuti permission + <code class="bg-amber-100 px-1 rounded">config/menu.php</code>.
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-teal-100">
            <h3 class="font-bold text-teal-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center text-sm">🔐</span>
                Daftar Role
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Permissions</th>
                        <th class="px-6 py-4">Users</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($roles as $role)
                    <tr class="hover:bg-gray-50/80">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800">{{ $role->name }}</div>
                            @if($role->name === 'Super Admin')
                                <div class="text-xs text-teal-600 mt-0.5">Full system + role/module mapping</div>
                            @elseif($role->name === 'Admin Gudang')
                                <div class="text-xs text-gray-500 mt-0.5">Full ops + users (tanpa role/module UI)</div>
                            @elseif($role->name === 'Supervisor')
                                <div class="text-xs text-gray-500 mt-0.5">Oversight / verify / approve / ship</div>
                            @elseif($role->name === 'Operator')
                                <div class="text-xs text-gray-500 mt-0.5">Floor only: scan / GR-GI create / putaway / count (no master/PO/report)</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700">
                                {{ $role->permissions_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $role->users_count }}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.roles.permissions', $role) }}"
                               class="inline-flex px-3 py-1.5 text-sm rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-medium">
                                Edit Permission
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
