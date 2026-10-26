<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Isi Data Roles
        DB::table('roles')->insert([
            ['role_id' => 'ROL-001', 'role_name' => 'Peserta', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => 'ROL-002', 'role_name' => 'Mentor', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => 'ROL-003', 'role_name' => 'Pengurus KSM', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Isi Data Mentoring Fields (4 Peminatan)
        DB::table('mentoring_fields')->insert([
            ['field_id' => 'FLD-001', 'field_name' => 'Web Dev', 'description' => 'Pengembangan aplikasi web modern menggunakan Laravel dan Vue/React', 'created_at' => now(), 'updated_at' => now()],
            ['field_id' => 'FLD-002', 'field_name' => 'Game Dev', 'description' => 'Pengembangan game interaktif menggunakan Unity atau Unreal Engine', 'created_at' => now(), 'updated_at' => now()],
            ['field_id' => 'FLD-003', 'field_name' => 'UI/UX', 'description' => 'Perancangan antarmuka dan pengalaman pengguna berbasis riset', 'created_at' => now(), 'updated_at' => now()],
            ['field_id' => 'FLD-004', 'field_name' => 'Graphic Design', 'description' => 'Desain visual, branding, dan aset multimedia kreatif', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Isi Data Mentoring Periods
        DB::table('mentoring_periods')->insert([
            ['period_id' => 'PRD-001', 'period_name' => 'Genap 2025/2026', 'start_date' => '2026-02-01', 'end_date' => '2026-07-31', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Isi Data Users Contoh
        DB::table('users')->insert([
            ['user_id' => 'USR-001', 'role_id' => 'ROL-001', 'name' => 'Andi Peserta', 'email' => 'andi@student.ksm.ac.id', 'password_hash' => Hash::make('password123'), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 'USR-002', 'role_id' => 'ROL-002', 'name' => 'Budi Mentor', 'email' => 'budi.mentor@ksm.ac.id', 'password_hash' => Hash::make('password123'), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 'USR-003', 'role_id' => 'ROL-003', 'name' => 'Siti Pengurus', 'email' => 'siti.admin@ksm.ac.id', 'password_hash' => Hash::make('password123'), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 'USR-004', 'role_id' => 'ROL-001', 'name' => 'Dina Peserta', 'email' => 'dina@student.ksm.ac.id', 'password_hash' => Hash::make('password123'), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
