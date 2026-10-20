<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createTestUser(string $roleName = 'Peserta', string $email = 'andi@example.com', string $name = 'Andi Peserta'): void
{
    DB::table('roles')->insert([
        'role_id' => 'ROL-001',
        'role_name' => $roleName,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'user_id' => 'USR-001',
        'role_id' => 'ROL-001',
        'name' => $name,
        'email' => $email,
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('the login page displays role selection buttons', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk Sebagai')
        ->assertSee('Peserta')
        ->assertSee('Mentor')
        ->assertSee('Pengurus');
});

test('the public landing page stays public when a user is authenticated', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('ARSIP AKADEMIK & SHOWCASE MULTIMEDIA', false)
        ->assertDontSee('Selamat datang, Andi Peserta')
        ->assertSee('Masuk')
        ->assertDontSee('Andi Peserta (Peserta)')
        ->assertDontSee('Keluar');
});

test('an authenticated user can open the login form to switch accounts', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk ke Akun');
});

test('a participant logs in and sees the participant dashboard', function () {
    createTestUser();

    $response = $this->post(route('login.submit'), [
        'email' => 'andi@example.com',
        'password' => 'password123',
        'role' => 'Peserta',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang,')
        ->assertSee('Andi Peserta')
        ->assertSee('Andi Peserta')
        ->assertSee('(Peserta)')
        ->assertSee('4 Bidang Mentoring')
        ->assertDontSee('Showcase Capstone')
        ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');});

test('a mentor can log in with the mentor role selected', function () {
    createTestUser('Mentor', 'budi@example.com', 'Budi Mentor');

    $this->post(route('login.submit'), [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'role' => 'Mentor',
    ])->assertRedirect(route('dashboard'));

    $this->assertSame('USR-001', auth()->id());

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang,')
        ->assertSee('Budi Mentor')
        ->assertSee('Budi Mentor')
        ->assertSee('(Mentor)')
        ->assertSee('4 Bidang Mentoring')
        ->assertDontSee('Selamat datang, Andi Peserta');
});

test('logging in as a mentor replaces the previous participant session', function () {
    createTestUser();
    $participant = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();
    $this->actingAs($participant);

    DB::table('roles')->insert([
        'role_id' => 'ROL-002',
        'role_name' => 'Mentor',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-002',
        'role_id' => 'ROL-002',
        'name' => 'Budi Mentor',
        'email' => 'budi@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->post(route('login.submit'), [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'role' => 'Mentor',
    ])->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang,')
        ->assertSee('Budi Mentor')
        ->assertSee('Budi Mentor')
        ->assertSee('(Mentor)')
        ->assertDontSee('Andi Peserta')
        ->assertDontSee('(Peserta)');
});

test('the selected role must match the user account role', function () {
    createTestUser('Mentor', 'budi@example.com', 'Budi Mentor');

    $this->from(route('login'))
        ->post(route('login.submit'), [
            'email' => 'budi@example.com',
            'password' => 'password123',
            'role' => 'Peserta',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('role');

    $this->assertGuest();
});

test('invalid credentials do not authenticate a user', function () {
    createTestUser();

    $this->from(route('login'))
        ->post(route('login.submit'), [
            'email' => 'andi@example.com',
            'password' => 'wrong-password',
            'role' => 'Peserta',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('logging out ends the authenticated session', function () {
    createTestUser();

    $this->post(route('login.submit'), [
        'email' => 'andi@example.com',
        'password' => 'password123',
        'role' => 'Peserta',
    ]);

    $this->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Showcase Capstone')
        ->assertSee('Masuk')
        ->assertDontSee('Keluar');
});

test('the dashboard requires authentication', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
