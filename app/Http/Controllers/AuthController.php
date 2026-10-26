<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:Peserta,Mentor,Pengurus KSM'],
        ]);

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $roleName = $credentials['role'];
        unset($credentials['role']);

        if (! Auth::attempt($credentials, false)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang dimasukkan tidak sesuai.',
            ]);
        }

        if (Auth::user()->role?->role_name !== $roleName) {
            Auth::logout();

            throw ValidationException::withMessages([
                'role' => 'Role yang dipilih tidak sesuai dengan akun ini.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('landing');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
