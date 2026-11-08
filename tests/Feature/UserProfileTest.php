<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $name, string $slug): User
    {
        $role = Role::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'description' => $name]
        );

        $perm = Permission::firstOrCreate(
            ['slug' => 'view-self-profile'],
            ['name' => 'view-self-profile', 'module' => 'General', 'description' => 'View Self Profile']
        );
        $role->permissions()->syncWithoutDetaching([$perm->id]);

        return User::factory()->create([
            'role_id' => $role->id,
            'name' => $name.' User',
            'email' => $slug.'@example.com',
            'phone' => '081234567890',
            'status' => 'Aktif',
            'avatar' => null,
        ]);
    }

    public function test_profile_page_does_not_contain_avatar_upload_for_all_roles(): void
    {
        $roles = [
            'Super Admin' => 'super-admin',
            'Pemilik Kost' => 'pemilik-kost',
            'Pengelola' => 'pengelola',
            'Penghuni' => 'penghuni',
        ];

        foreach ($roles as $roleName => $roleSlug) {
            $user = $this->createUserWithRole($roleName, $roleSlug);

            $response = $this->actingAs($user)->get(route('profile'));

            $response->assertOk();
            $response->assertDontSee('Foto Avatar');
            $response->assertDontSee('name="avatar"', false);
            $response->assertSee('Nama Lengkap');
            $response->assertSee('Alamat Email');
            $response->assertSee('Nomor WhatsApp');
            $response->assertSee('Ubah Kata Sandi');
        }
    }

    public function test_user_can_update_profile_info_successfully(): void
    {
        $user = $this->createUserWithRole('Penghuni', 'penghuni');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nama Baru Penghuni',
            'email' => 'emailbaru@example.com',
            'phone' => '089876543210',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nama Baru Penghuni', $user->name);
        $this->assertSame('emailbaru@example.com', $user->email);
        $this->assertSame('089876543210', $user->phone);
    }

    public function test_avatar_file_is_not_processed_or_stored_when_updating_profile(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithRole('Super Admin', 'super-admin');
        $fakeFile = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Super Admin Updated',
            'email' => 'superadmin-updated@example.com',
            'phone' => '08111222333',
            'avatar' => $fakeFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Super Admin Updated', $user->name);
        $this->assertNull($user->avatar);
        Storage::disk('public')->assertMissing('avatars/'.$fakeFile->hashName());
    }
}

