<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('roles');
        
        // Hide Super Admin from non-super admins
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->whereDoesntHave('roles', fn($r) => $r->where('name', 'Super Admin'));
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by role
        if ($request->role) {
            $query->whereHas('roles', fn($r) => $r->where('name', $request->role));
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $roles = Role::when(!auth()->user()->hasRole('Super Admin'), function($q) {
            $q->where('name', '!=', 'Super Admin');
        })->get();
        
        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show create user form
     */
    public function create()
    {
        $roles = Role::when(!auth()->user()->hasRole('Super Admin'), function($q) {
            $q->where('name', '!=', 'Super Admin');
        })->get();
        
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,name'],
        ]);
        
        // Prevent creating Super Admin by non-super admins
        if ($validated['role'] === 'Super Admin' && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        $user->assignRole($validated['role']);
        
        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Show edit user form
     */
    public function edit(User $user)
    {
        // Prevent editing Super Admin by non-super admins
        if ($user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }
        
        $roles = Role::when(!auth()->user()->hasRole('Super Admin'), function($q) {
            $q->where('name', '!=', 'Super Admin');
        })->get();
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        // Prevent editing Super Admin by non-super admins
        if ($user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,name'],
        ]);
        
        // Prevent assigning Super Admin by non-super admins
        if ($validated['role'] === 'Super Admin' && !auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }
        
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        
        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }
        
        $user->syncRoles([$validated['role']]);
        
        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Prevent deleting Super Admin
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Tidak dapat menghapus Super Admin!');
        }
        
        // Prevent self-delete
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri!');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
    }
}
