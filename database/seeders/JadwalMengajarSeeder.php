<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalMengajarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get IDs from database
        $kelasId = DB::table('kelas')->where('nama_kelas', 'XII RPL 1')->value('id');
        $guru4Id = DB::table('guru')->where('nama', 'Dedi Suryadi, S.Kom')->value('id');
        $guru5Id = DB::table('guru')->where('nama', 'Rina Wati, S.Pd')->value('id');

        $pwpbId = DB::table('mata_pelajaran')->where('kode_mapel', 'PWPB')->value('id');
        $ppbId = DB::table('mata_pelajaran')->where('kode_mapel', 'PPB')->value('id');
        $bdId = DB::table('mata_pelajaran')->where('kode_mapel', 'BD')->value('id');
        $mtkId = DB::table('mata_pelajaran')->where('kode_mapel', 'MTK')->value('id');
        $pkkId = DB::table('mata_pelajaran')->where('kode_mapel', 'PKK')->value('id');

        // Jadwal untuk hari ini (untuk testing)
        $hariIni = strtolower(now()->locale('id')->isoFormat('dddd')); // senin, selasa, etc

        $jadwalList = [
            // Senin
            [
                'guru_id' => $guru4Id,
                'kelas_id' => $kelasId,
                'mapel_id' => $pwpbId,
                'hari' => 'senin',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:00',
                'ruangan' => 'Lab Komputer 1',
                'tahun_ajaran' => '2025/2026',
                'semester' => '1',
                'status' => 'aktif',
            ],
            [
                'guru_id' => $guru5Id,
                'kelas_id' => $kelasId,
                'mapel_id' => $mtkId,
                'hari' => 'senin',
                'jam_mulai' => '09:15',
                'jam_selesai' => '10:45',
                'ruangan' => 'Ruang 12A',
                'tahun_ajaran' => '2025/2026',
                'semester' => '1',
                'status' => 'aktif',
            ],
            // Selasa
            [
                'guru_id' => $guru4Id,
                'kelas_id' => $kelasId,
                'mapel_id' => $ppbId,
                'hari' => 'selasa',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:00',
                'ruangan' => 'Lab Komputer 1',
                'tahun_ajaran' => '2025/2026',
                'semester' => '1',
                'status' => 'aktif',
            ],
            [
                'guru_id' => $guru5Id,
                'kelas_id' => $kelasId,
                'mapel_id' => $mtkId,
                'hari' => 'selasa',
                'jam_mulai' => '10:00',
                'jam_selesai' => '11:30',
                'ruangan' => 'Ruang 12A',
                'tahun_ajaran' => '2025/2026',
                'semester' => '1',
                'status' => 'aktif',
            ],
            // Rabu
            [
                'guru_id' => $guru4Id,
                'kelas_id' => $kelasId,
                'mapel_id' => $bdId,
                'hari' => 'rabu',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:00',
                'ruangan' => 'Lab Komputer 2',
                'tahun_ajaran' => '2025/2026',
                'semester' => '1',
                'status' => 'aktif',
            ],
            // Kamis
            [
                'guru_id' => $guru4Id,
                'kelas_id' => $kelasId,
                'mapel_id' => $pwpbId,
                'hari' => 'kamis',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:00',
                'ruangan' => 'Lab Komputer 1',
                'tahun_ajaran' => '2025/2026',
                'semester' => '1',
                'status' => 'aktif',
            ],
            [
                'guru_id' => $guru4Id,
                'kelas_id' => $kelasId,
                'mapel_id' => $pkkId,
                'hari' => 'kamis',
                'jam_mulai' => '10:00',
                'jam_selesai' => '11:30',
                'ruangan' => 'Ruang 12A',
                'tahun_ajaran' => '2025/2026',
                'semester' => '1',
                'status' => 'aktif',
            ],
            // Jumat
            [
                'guru_id' => $guru5Id,
                'kelas_id' => $kelasId,
                'mapel_id' => $mtkId,
                'hari' => 'jumat',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:00',
                'ruangan' => 'Ruang 12A',
                'tahun_ajaran' => '2025/2026',
                'semester' => '1',
                'status' => 'aktif',
            ],
        ];

        foreach ($jadwalList as $jadwal) {
            DB::table('jadwal_mengajar')->insert(array_merge($jadwal, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
