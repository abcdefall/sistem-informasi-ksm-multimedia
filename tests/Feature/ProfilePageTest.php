<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createProfileTestUser(string $roleName, string $userId): User
{
    $roleId = "ROLE-{$userId}";
    DB::table('roles')->insert([
        'role_id' => $roleId,
        'role_name' => $roleName,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'user_id' => $userId,
        'role_id' => $roleId,
        'name' => "Nama {$roleName}",
        'email' => strtolower($userId).'@example.test',
        'password_hash' => Hash::make('password-lama'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return User::where('user_id', $userId)->firstOrFail();
}

test('participants and mentors can view their profile page', function (string $roleName, string $userId) {
    $user = createProfileTestUser($roleName, $userId);

    $this->actingAs($user)
        ->get(route('dashboard.profile'))
        ->assertOk()
        ->assertSee('Profil Saya')
        ->assertSee('Nama '.$roleName)
        ->assertSee($user->email)
        ->assertSee('Simpan Profil')
        ->assertSee(route('dashboard.profile.update'))
        ->assertSee('Halo, Nama '.$roleName)
        ->assertSee($roleName === 'Mentor' ? 'PORTAL MENTOR' : 'PORTAL PESERTA')
        ->assertSee('sidebar-link active', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee(route('dashboard.profile'));
})->with([
    'participant' => ['Peserta', 'USR-PROFILE-PESERTA'],
    'mentor' => ['Mentor', 'USR-PROFILE-MENTOR'],
]);

test('participants and mentors can update their name and bio but not account credentials', function (string $roleName, string $userId) {
    $user = createProfileTestUser($roleName, $userId);
    $originalEmail = $user->email;
    $originalPassword = $user->password_hash;

    $this->actingAs($user)
        ->put(route('dashboard.profile.update'), [
            'name' => 'Nama Profil Baru',
            'bio' => 'Saya tertarik mengembangkan produk multimedia.',
            'email' => 'changed@example.test',
            'password' => 'password-baru',
        ])
        ->assertRedirect(route('dashboard.profile'))
        ->assertSessionHas('status', 'Profil berhasil diperbarui.');

    $user->refresh();
    expect($user->name)->toBe('Nama Profil Baru')
        ->and($user->profile_data['bio'])->toBe('Saya tertarik mengembangkan produk multimedia.')
        ->and($user->email)->toBe($originalEmail)
        ->and($user->password_hash)->toBe($originalPassword);
})->with([
    'participant' => ['Peserta', 'USR-UPDATE-PESERTA'],
    'mentor' => ['Mentor', 'USR-UPDATE-MENTOR'],
]);

test('other roles cannot access or update participant and mentor profiles', function () {
    $user = createProfileTestUser('Pengurus KSM', 'USR-PROFILE-ADMIN');

    $this->actingAs($user)
        ->get(route('dashboard.profile'))
        ->assertForbidden();

    $this->put(route('dashboard.profile.update'), [
        'name' => 'Nama Baru',
        'bio' => 'Bio baru',
    ])->assertForbidden();
});

test('profile bio is limited to 500 characters', function () {
    $user = createProfileTestUser('Peserta', 'USR-PROFILE-LIMIT');

    $this->actingAs($user)
        ->from(route('dashboard.profile'))
        ->put(route('dashboard.profile.update'), [
            'name' => 'Nama Peserta',
            'bio' => str_repeat('a', 501),
        ])
        ->assertRedirect(route('dashboard.profile'))
        ->assertSessionHasErrors('bio');
});
