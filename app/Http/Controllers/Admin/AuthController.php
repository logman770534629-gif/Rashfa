<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $admin = Admin::where('email', strtolower(trim($request->validated('email'))))->first();

        if (! $admin || ! $admin->is_active || $admin->isLocked() || ! Hash::check($request->validated('password'), $admin->password_hash)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'بيانات الدخول غير صحيحة.']);
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        // This app does not issue remember-me cookies. Avoid rotating database
        // tokens during a read-only preview; invalidating the session logs out.
        Auth::guard('admin')->logoutCurrentDevice();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
