<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiwayatAktivitas;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $roleId = $request->query('role_id');
        $status = $request->query('status');
        $search = $request->query('q');

        $query = User::with('role');

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles', 'roleId', 'status', 'search'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25'],
            'status' => ['required', 'in:Aktif,Nonaktif'],
            'password' => ['required', Password::min(8)],
        ]);

        $user = User::create([
            'role_id' => $request->role_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
            'password' => Hash::make($request->password),
        ]);

        RiwayatAktivitas::catat(
            Auth::id(),
            'Buat User Baru',
            'Super Admin membuat user baru: ' . $user->name . ' (' . $user->email . ') dengan role ' . $user->role->name,
            'info'
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User ' . $user->name . ' berhasil ditambahkan ke sistem.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'max:25'],
            'status' => ['required', 'in:Aktif,Nonaktif'],
            'password' => ['nullable', Password::min(8)],
        ]);

        $user->role_id = $request->role_id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        RiwayatAktivitas::catat(
            Auth::id(),
            'Update Data User',
            'Super Admin memperbarui data user: ' . $user->name,
            'info'
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Data user ' . $user->name . ' berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->status = ($user->status === 'Aktif') ? 'Nonaktif' : 'Aktif';
        $user->save();

        RiwayatAktivitas::catat(
            Auth::id(),
            'Ubah Status Akun',
            'Status akun ' . $user->name . ' diubah menjadi ' . $user->status,
            'warning'
        );

        return back()->with('success', 'Status user ' . $user->name . ' berhasil diubah menjadi ' . $user->status . '.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->sewas()->where('status', 'Aktif')->exists()) {
            return back()->with('error', 'User ini memiliki sewa kamar aktif dan tidak dapat dihapus.');
        }

        $userName = $user->name;
        $user->delete();

        RiwayatAktivitas::catat(
            Auth::id(),
            'Hapus User',
            'Super Admin menghapus akun: ' . $userName,
            'danger'
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User ' . $userName . ' berhasil dihapus dari sistem.');
    }
}
