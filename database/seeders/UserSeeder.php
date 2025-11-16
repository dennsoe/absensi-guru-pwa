<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin default
        $adminId = DB::table('users')->insertGetId([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kepala Sekolah
        $kepsekId = DB::table('users')->insertGetId([
            'username' => 'kepsek',
            'password' => Hash::make('kepsek123'),
            'role' => 'kepala_sekolah',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Guru Piket
        $piketId = DB::table('users')->insertGetId([
            'username' => 'piket',
            'password' => Hash::make('piket123'),
            'role' => 'guru_piket',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kurikulum
        $kurikulumId = DB::table('users')->insertGetId([
            'username' => 'kurikulum',
            'password' => Hash::make('kurikulum123'),
            'role' => 'kurikulum',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Guru Sample
        $guruId = DB::table('users')->insertGetId([
            'username' => 'guru001',
            'password' => Hash::make('guru123'),
            'role' => 'guru',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Data guru lengkap
        DB::table('guru')->insert([
            'user_id' => $guruId,
            'nip' => '198505152010011001',
            'nama' => 'Ahmad Guru Sample',
            'email' => 'guru@smknekas.sch.id',
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
