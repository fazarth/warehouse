<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Main settings page - redirects based on role
     */
    public function index()
    {
        $user = auth()->user();

        return view('settings.index', [
            'user' => $user,
            // App settings only for users with setting.edit (Super Admin / Admin Gudang)
            'isAdmin' => $user->can('setting.edit'),
        ]);
    }

    /**
     * Account settings (all users)
     */
    public function account()
    {
        return view('settings.account', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update account settings
     */
    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Password settings
     */
    public function password()
    {
        return view('settings.password');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }

    /**
     * App settings (Admin only)
     */
    public function app()
    {
        abort_unless(auth()->user()->can('setting.edit'), 403);

        return view('settings.app', [
            'settings' => [
                'app_name' => AppSetting::get('app_name', 'Warehouse Management'),
                'app_tagline' => AppSetting::get('app_tagline', 'Sistem Manajemen Gudang'),
                'app_logo' => AppSetting::get('app_logo'),
                'app_favicon' => AppSetting::get('app_favicon'),
                'primary_color' => AppSetting::get('primary_color', '#14B8A6'),
            ],
        ]);
    }

    /**
     * Update app settings
     */
    public function updateApp(Request $request)
    {
        abort_unless(auth()->user()->can('setting.edit'), 403);

        $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'app_tagline' => ['nullable', 'string', 'max:200'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'app_logo' => ['nullable', 'image', 'max:2048'],
            'app_favicon' => ['nullable', 'image', 'max:512'],
        ]);

        AppSetting::set('app_name', $request->app_name);
        AppSetting::set('app_tagline', $request->app_tagline);
        
        if ($request->primary_color) {
            AppSetting::set('primary_color', $request->primary_color, 'string', 'appearance');
        }

        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('logos', 'public');
            AppSetting::set('app_logo', $path, 'string', 'appearance');
        }

        if ($request->hasFile('app_favicon')) {
            $path = $request->file('app_favicon')->store('favicons', 'public');
            AppSetting::set('app_favicon', $path, 'string', 'appearance');
        }

        return back()->with('success', 'Pengaturan aplikasi berhasil disimpan!');
    }

    /**
     * Loan & Saving settings (Admin only)
     */
    public function finance()
    {
        // Legacy koperasi screen — not used by WMS; keep gated.
        abort_unless(auth()->user()->can('setting.edit'), 403);

        return view('settings.finance', [
            'settings' => [
                'interest_rate_default' => AppSetting::get('interest_rate_default', 12),
                'max_loan_amount' => AppSetting::get('max_loan_amount', 50000000),
                'simpanan_pokok' => AppSetting::get('simpanan_pokok', 100000),
                'simpanan_wajib' => AppSetting::get('simpanan_wajib', 50000),
            ],
        ]);
    }

    /**
     * Update finance settings
     */
    public function updateFinance(Request $request)
    {
        abort_unless(auth()->user()->can('setting.edit'), 403);

        $request->validate([
            'interest_rate_default' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_loan_amount' => ['required', 'numeric', 'min:0'],
            'simpanan_pokok' => ['required', 'numeric', 'min:0'],
            'simpanan_wajib' => ['required', 'numeric', 'min:0'],
        ]);

        AppSetting::set('interest_rate_default', $request->interest_rate_default, 'integer', 'loan');
        AppSetting::set('max_loan_amount', $request->max_loan_amount, 'integer', 'loan');
        AppSetting::set('simpanan_pokok', $request->simpanan_pokok, 'integer', 'saving');
        AppSetting::set('simpanan_wajib', $request->simpanan_wajib, 'integer', 'saving');

        return back()->with('success', 'Pengaturan keuangan berhasil disimpan!');
    }
}
