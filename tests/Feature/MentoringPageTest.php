<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('guests can browse mentoring programs and continue to login', function () {
    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-WEB',
        'field_name' => 'Web Dev',
        'description' => 'Pengembangan aplikasi web.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->get(route('mentoring'))
        ->assertOk()
        ->assertSee('Temukan jalur mentoring Anda')
        ->assertSee('Web Development')
        ->assertSee('Kurikulum terarah')
        ->assertSee('Masuk untuk Bergabung')
        ->assertSee(route('login'))
        ->assertSee(route('mentoring'));
});

test('participants see mentoring calls to action for their role', function () {
    DB::table('roles')->insert([
        'role_id' => 'ROL-PESERTA',
        'role_name' => 'Peserta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'user_id' => 'USR-PESERTA',
        'role_id' => 'ROL-PESERTA',
        'name' => 'Alya Peserta',
        'email' => 'alya@example.test',
        'password_hash' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs(User::where('user_id', 'USR-PESERTA')->firstOrFail())
        ->get(route('mentoring'))
        ->assertOk()
        ->assertSee('Kembangkan ide menjadi karya nyata')
        ->assertSee('Mulai Proyek')
        ->assertSee(route('dashboard.peserta.project.create'))
        ->assertSee('Alya Peserta')
        ->assertSee('Keluar');
});
