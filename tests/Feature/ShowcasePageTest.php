<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('the showcase page lists only published projects and filters by search and field', function () {
    $timestamp = now();

    DB::table('roles')->insert([
        [
            'role_id' => 'ROL-TEST',
            'role_name' => 'Peserta',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'role_id' => 'ROL-MENTOR',
            'role_name' => 'Mentor',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'role_id' => 'ROL-ADMIN',
            'role_name' => 'Pengurus KSM',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
    ]);

    DB::table('users')->insert([
        [
            'user_id' => 'USR-TEST',
            'role_id' => 'ROL-TEST',
            'name' => 'Alya Multimedia',
            'email' => 'alya@example.test',
            'password_hash' => 'hashed-password',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'user_id' => 'USR-TEAM',
            'role_id' => 'ROL-TEST',
            'name' => 'Rizky Team',
            'email' => 'rizky@example.test',
            'password_hash' => 'hashed-password',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'user_id' => 'USR-MENTOR',
            'role_id' => 'ROL-MENTOR',
            'name' => 'Budi Mentor',
            'email' => 'budi@example.test',
            'password_hash' => 'hashed-password',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'user_id' => 'USR-ADMIN',
            'role_id' => 'ROL-ADMIN',
            'name' => 'Sari Pengurus',
            'email' => 'sari@example.test',
            'password_hash' => 'hashed-password',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
    ]);

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-WEB',
        'field_name' => 'Web Dev',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
    ]);

    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-TEST',
        'period_name' => 'Semester Ganjil 2026',
        'is_active' => true,
        'start_date' => '2026-08-01',
        'end_date' => '2026-12-31',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
    ]);

    DB::table('projects')->insert([
        [
            'project_id' => 'PRJ-PUBLISHED',
            'field_id' => 'FLD-WEB',
            'period_id' => 'PRD-TEST',
            'created_by' => 'USR-TEST',
            'title' => 'Nusantara Digital Archive',
            'description' => 'Arsip digital budaya Nusantara.',
            'project_link' => 'https://example.test/archive',
            'status' => 'Published',
            'published_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'project_id' => 'PRJ-DRAFT',
            'field_id' => 'FLD-WEB',
            'period_id' => 'PRD-TEST',
            'created_by' => 'USR-TEST',
            'title' => 'Nusantara Draft Project',
            'description' => 'Belum dipublikasikan.',
            'project_link' => null,
            'status' => 'Draft',
            'published_at' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
    ]);

    DB::table('project_members')->insert([
        'project_id' => 'PRJ-PUBLISHED',
        'user_id' => 'USR-TEAM',
        'member_role' => 'Designer',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
    ]);

    $this->assertGuest();

    $this->get(route('showcase', ['q' => 'Nusantara', 'field' => 'FLD-WEB']))
        ->assertOk()
        ->assertSee('Masuk')
        ->assertDontSee('Keluar')
        ->assertSee('Nusantara Digital Archive')
        ->assertSee('Semester Ganjil 2026')
        ->assertDontSee('Nusantara Draft Project');

    $this->get(route('showcase', ['q' => 'Rizky Team', 'field' => 'FLD-WEB']))
        ->assertOk()
        ->assertSee('Nusantara Digital Archive')
        ->assertDontSee('Nusantara Draft Project');

    $this->actingAs(\App\Models\User::where('user_id', 'USR-TEST')->firstOrFail())
        ->get(route('showcase'))
        ->assertSee('Dashboard Peserta')
        ->assertSee('Temukan Inspirasi Karya')
        ->assertSee('Ajukan Karya')
        ->assertSee('Alya Multimedia')
        ->assertSee('(Peserta)')
        ->assertSee('Keluar')
        ->assertDontSee('Masuk</a>', false);

    $this->actingAs(\App\Models\User::where('user_id', 'USR-MENTOR')->firstOrFail())
        ->get(route('showcase'))
        ->assertSee('Dashboard Mentor')
        ->assertSee('Apresiasi Karya Mahasiswa')
        ->assertSee('Buka Dashboard Mentor')
        ->assertSee('Budi Mentor')
        ->assertSee('(Mentor)')
        ->assertSee('Keluar')
        ->assertDontSee('Masuk</a>', false);

    $this->actingAs(\App\Models\User::where('user_id', 'USR-ADMIN')->firstOrFail())
        ->get(route('showcase'))
        ->assertSee('Dashboard Pengurus')
        ->assertSee('Publikasi KSM Multimedia')
        ->assertSee('Buka Dashboard Pengurus')
        ->assertSee('Sari Pengurus')
        ->assertSee('(Pengurus KSM)')
        ->assertSee('Keluar')
        ->assertDontSee('Masuk</a>', false);
});
