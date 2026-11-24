<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('Admin')) {
            return redirect('/back-admin-logged/dashboard');
        }

        if ($user->hasRole('Participant')) {
            return redirect('/vote');
        }

        // Akun tanpa role tidak punya halaman tujuan: keluarkan dengan pesan yang jelas.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->withErrors(['username' => 'Akun ini belum memiliki akses. Hubungi panitia.']);
    }
}
