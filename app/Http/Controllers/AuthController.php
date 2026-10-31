<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            AuditLog::log(
                'auth.login',
                \App\Models\User::class,
                Auth::id(),
                'Login berhasil: ' . Auth::user()->email,
                null,
                ['email' => Auth::user()->email, 'remember' => $request->boolean('remember')]
            );

            return redirect()->intended(route('dashboard'));
        }

        AuditLog::log(
            'auth.login_failed',
            null,
            null,
            'Login gagal untuk email: ' . $credentials['email'],
            null,
            ['email' => $credentials['email']]
        );

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::log(
                'auth.logout',
                \App\Models\User::class,
                $user->id,
                'Logout: ' . $user->email,
                null,
                ['email' => $user->email],
                $user->id
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
