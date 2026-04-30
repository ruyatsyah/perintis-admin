<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check()) {
            return auth()->user()->role === 'admin' 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('petugas.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials)) {
            $request->session()->regenerate();

            return auth()->user()->role === 'admin' 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('petugas.index');
        }

        return back()->withErrors([
            'email' => 'Login gagal, periksa kembali email dan password.',
        ]);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
