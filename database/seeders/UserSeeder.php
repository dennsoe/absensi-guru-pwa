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
        // 1. Admin default (tidak punya guru_id)
        DB::table('users')->insert([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama' => 'Administrator',
            'email' => 'admin@smknekas.sch.id',
            'role' => 'admin',
            'status' => 'aktif',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create users for guru first (guru role doesn't need guru_id initially)
        $userGuru1Id = DB::table('users')->insertGetId([
            'username' => 'kepsek',
            'password' => Hash::make('kepsek123'),
            'nama' => 'Dr. Bambang Sudrajat, S.Pd., M.Pd',
            'email' => 'kepala.sekolah@smknekas.sch.id',
            'nip' => '196805152000011001',
            'no_hp' => '081234567801',
            'role' => 'kepala_sekolah',
            'status' => 'aktif',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userGuru2Id = DB::table('users')->insertGetId([
            'username' => 'piket',
            'password' => Hash::make('piket123'),
            'nama' => 'Ahmad Fauzi, S.Pd',
            'email' => 'guru.piket@smknekas.sch.id',
            'nip' => '197505152003011002',
            'no_hp' => '081234567802',
            'role' => 'guru_piket',
            'status' => 'aktif',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userGuru3Id = DB::table('users')->insertGetId([
            'username' => 'kurikulum',
            'password' => Hash::make('kurikulum123'),
            'nama' => 'Siti Nurhaliza, S.Pd., M.Pd',
            'email' => 'kurikulum@smknekas.sch.id',
            'nip' => '198005152008012003',
            'no_hp' => '081234567803',
            'role' => 'kurikulum',
            'status' => 'aktif',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userGuru4Id = DB::table('users')->insertGetId([
            'username' => 'guru.rpl',
            'password' => Hash::make('guru123'),
            'nama' => 'Dedi Suryadi, S.Kom',
            'email' => 'guru.rpl@smknekas.sch.id',
            'nip' => '198505152010011004',
            'no_hp' => '081234567804',
            'role' => 'guru',
            'status' => 'aktif',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userGuru5Id = DB::table('users')->insertGetId([
            'username' => 'guru.mtk',
            'password' => Hash::make('guru123'),
            'nama' => 'Rina Wati, S.Pd',
            'email' => 'guru.mtk@smknekas.sch.id',
            'nip' => '199005152015012005',
            'no_hp' => '081234567805',
            'role' => 'guru',
            'status' => 'aktif',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Now create guru records linked to users
        $guru1Id = DB::table('guru')->insertGetId([
            'user_id' => $userGuru1Id,
            'nip' => '196805152000011001',
            'nama' => 'Dr. Bambang Sudrajat, S.Pd., M.Pd',
            'email' => 'kepala.sekolah@smknekas.sch.id',
            'no_hp' => '081234567801',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $guru2Id = DB::table('guru')->insertGetId([
            'user_id' => $userGuru2Id,
            'nip' => '197505152003011002',
            'nama' => 'Ahmad Fauzi, S.Pd',
            'email' => 'guru.piket@smknekas.sch.id',
            'no_hp' => '081234567802',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $guru3Id = DB::table('guru')->insertGetId([
            'user_id' => $userGuru3Id,
            'nip' => '198005152008012003',
            'nama' => 'Siti Nurhaliza, S.Pd., M.Pd',
            'email' => 'kurikulum@smknekas.sch.id',
            'no_hp' => '081234567803',
            'jenis_kelamin' => 'P',
            'status_kepegawaian' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $guru4Id = DB::table('guru')->insertGetId([
            'user_id' => $userGuru4Id,
            'nip' => '198505152010011004',
            'nama' => 'Dedi Suryadi, S.Kom',
            'email' => 'guru.rpl@smknekas.sch.id',
            'no_hp' => '081234567804',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $guru5Id = DB::table('guru')->insertGetId([
            'user_id' => $userGuru5Id,
            'nip' => '199005152015012005',
            'nama' => 'Rina Wati, S.Pd',
            'email' => 'guru.mtk@smknekas.sch.id',
            'no_hp' => '081234567805',
            'jenis_kelamin' => 'P',
            'status_kepegawaian' => 'PPPK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update users to link guru_id for staff roles
        DB::table('users')->where('id', $userGuru1Id)->update(['guru_id' => $guru1Id]);
        DB::table('users')->where('id', $userGuru2Id)->update(['guru_id' => $guru2Id]);
        DB::table('users')->where('id', $userGuru3Id)->update(['guru_id' => $guru3Id]);

        // Create sample kelas
        $kelasId = DB::table('kelas')->insertGetId([
            'nama_kelas' => 'XII RPL 1',
            'tingkat' => '12',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'wali_kelas_id' => $guru4Id,
            'tahun_ajaran' => '2025/2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Ketua Kelas (siswa, tidak punya guru_id tapi punya kelas_id)
        $ketuaKelasId = DB::table('users')->insertGetId([
            'username' => 'ketua.rpl1',
            'password' => Hash::make('ketua123'),
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@smknekas.sch.id',
            'no_hp' => '081234567890',
            'role' => 'ketua_kelas',
            'kelas_id' => $kelasId,
            'status' => 'aktif',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update kelas to set ketua_kelas_user_id
        DB::table('kelas')->where('id', $kelasId)->update([
            'ketua_kelas_user_id' => $ketuaKelasId
        ]);
    }
}
