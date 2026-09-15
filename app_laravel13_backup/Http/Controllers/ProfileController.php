<?php

namespace App\Http\Controllers;

use App\Models\RiwayatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $riwayats = RiwayatAktivitas::where('user_id', $user->id)->latest()->take(10)->get();

        return view('profile', compact('user', 'riwayats'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:25'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->save();

        RiwayatAktivitas::catat($user->id, 'Profil Diperbarui', 'Data profil pengguna telah diperbarui.', 'info');

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::min(8), 'confirmed'],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        RiwayatAktivitas::catat($user->id, 'Password Diubah', 'Kata sandi akun telah berhasil diganti.', 'warning');

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }
}
