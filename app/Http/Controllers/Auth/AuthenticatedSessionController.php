<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ElectionSetting;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return $this->loginView(false);
    }

    /** Halaman login admin (alamat terpisah, tidak ditautkan dari mana pun). */
    public function createAdmin(): View
    {
        return $this->loginView(true);
    }

    private function loginView(bool $adminDoor): View
    {
        $setting = ElectionSetting::current();

        return view('auth.login', [
            'setting' => $setting,
            'status' => $setting->status(),
            'adminDoor' => $adminDoor,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(adminDoor: false);

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(adminDoor: true);

        $request->session()->regenerate();

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
