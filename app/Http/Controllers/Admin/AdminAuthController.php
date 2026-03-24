<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Admin/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect.']);
        }

        $user = $request->user();

        $adminProfile = $user->profiles()
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->where('is_active', true)
            ->first();

        if (!$adminProfile) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            return back()->withErrors(['email' => 'You do not have admin access.']);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_profile_id', $adminProfile->id);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
