<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RiwayatAktivitas;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('module');

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function updatePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->slug === 'super-admin') {
            return back()->with('info', 'Role Super Admin selalu memiliki seluruh hak akses sistem secara otomatis.');
        }

        $permissionIds = $request->input('permissions', []);
        $role->permissions()->sync($permissionIds);

        RiwayatAktivitas::catat(
            Auth::id(),
            'Perbarui Hak Akses Role',
            'Super Admin memperbarui hak akses untuk role ' . $role->name,
            'warning'
        );

        return back()->with('success', 'Hak akses untuk role ' . $role->name . ' berhasil diperbarui.');
    }
}
