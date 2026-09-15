<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\RiwayatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->status !== 'Aktif') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', 'Akun Anda saat ini dinonaktifkan. Silakan hubungi pihak pengelola kost.');
            }

            RiwayatAktivitas::catat(
                $user->id,
                'Login Berhasil',
                'Pengguna masuk ke dalam sistem dari IP: ' . $request->ip(),
                'success'
            );

            return $this->redirectBasedOnRole($user);
        }

        return back()->withInput($request->only('email'))->with('error', 'Email atau password yang Anda masukkan salah.');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah terdaftar di sistem.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        // Default role is always Penghuni for public registration
        $penghuniRole = Role::where('slug', 'penghuni')->first();

        $user = User::create([
            'role_id' => $penghuniRole?->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => 'Aktif',
            'password' => Hash::make($validated['password']),
        ]);

        RiwayatAktivitas::catat(
            $user->id,
            'Pendaftaran Berhasil',
            'Akun baru berhasil didaftarkan sebagai calon penghuni.',
            'success'
        );

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Silakan masuk dengan email dan password Anda.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            RiwayatAktivitas::catat(
                Auth::id(),
                'Logout',
                'Pengguna keluar dari sistem.',
                'info'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    public function redirectBasedOnRole(User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isPemilik()) {
            return redirect()->route('pemilik.dashboard');
        } else {
            return redirect()->route('penghuni.dashboard');
        }
    }
}
