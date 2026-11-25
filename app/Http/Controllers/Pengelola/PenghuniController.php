<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PenghuniController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'aktif');

        if ($status === 'aktif') {
            $penghunis = User::whereHas('sewas', function ($q) {
                $q->where('status', 'Aktif');
            })->with(['activeSewa.kamar.tipeKamar'])
                ->latest()
                ->paginate(10);
        } else {
            $penghunis = User::whereHas('role', function ($q) {
                $q->where('slug', 'penghuni');
            })->with(['activeSewa.kamar', 'sewas'])
                ->latest()
                ->paginate(10);
        }

        return view('pengelola.penghuni.index', compact('penghunis', 'status'));
    }

    public function show($id)
    {
        $penghuni = User::with([
            'role',
            'sewas.kamar.tipeKamar',
            'bookings.kamar',
            'pembayarans',
            'tagihans.kamar',
            'keluhans.kamar',
            'ratings',
            'riwayatAktivitas',
        ])->findOrFail($id);

        return view('pengelola.penghuni.show', compact('penghuni'));
    }

    public function edit($id)
    {
        $penghuni = User::findOrFail($id);
        return view('pengelola.penghuni.edit', compact('penghuni'));
    }

    public function update(Request $request, $id)
    {
        $penghuni = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'phone' => ['required', function($attribute, $value, $fail) {
                if (!ctype_digit($value)) {
                    $fail('Nomor WhatsApp hanya boleh berisi angka.');
                } elseif (strlen($value) < 12) {
                    $fail('Nomor WhatsApp harus terdiri dari tepat 12 digit.');
                } elseif (strlen($value) > 12) {
                    $fail('Nomor WhatsApp maksimal 12 digit.');
                }
            }],
            'email' => 'required|email|unique:users,email,' . $penghuni->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        $penghuni->name = $data['name'];
        $penghuni->phone = $data['phone'];
        $penghuni->email = $data['email'];
        $penghuni->save();

        return redirect()->route('pengelola.penghuni.show', $penghuni->id)
            ->with('success', 'Data penghuni berhasil diperbarui.');
    }

    public function showResetForm($id)
    {
        $penghuni = User::findOrFail($id);
        return view('pengelola.penghuni.password.request', compact('penghuni'));
    }

    public function sendResetLink(Request $request, $id)
    {
        $penghuni = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|in:' . $penghuni->email,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $status = Password::broker('users')->sendResetLink([
            'email' => $penghuni->email,
        ]);

        // Handle possible statuses
        if ($status === Password::RESET_LINK_SENT) {
            return redirect()->route('pengelola.penghuni.show', $penghuni->id)
                ->with('status', __($status));
        }

        if ($status === Password::RESET_THROTTLED) {
            // Throttling: inform user to wait before retrying
            return redirect()->back()->withErrors([
                'email' => 'Silakan tunggu beberapa menit sebelum mengirim ulang tautan reset.',
            ]);
        }

        // Other errors (e.g., invalid user)
        return redirect()->back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show password reset form from token link (used in email).
     */
    public function showResetFormToken($token)
    {
        $email = request('email');
        $penghuni = User::where('email', $email)->firstOrFail();
        return view('pengelola.penghuni.password.reset', compact('penghuni', 'token'));
    }

    public function resetPassword(Request $request, $id)
    {
        $penghuni = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'token'    => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $credentials = [
            'email' => $penghuni->email,
            'password' => $request->input('password'),
            'token' => $request->input('token'),
        ];

        $status = Password::broker('users')->reset($credentials, function ($user, $password) {
            $user->password = $password;
            $user->save();
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('pengelola.penghuni.show', $penghuni->id)
                ->with('success', 'Password berhasil direset.');
        }

        return redirect()->back()->withErrors(['email' => __($status)]);
    }
}
