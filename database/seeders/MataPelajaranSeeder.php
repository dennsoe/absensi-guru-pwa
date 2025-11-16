<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mataPelajaran = [
            ['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'deskripsi' => 'Mata pelajaran Matematika'],
            ['kode_mapel' => 'BIN', 'nama_mapel' => 'Bahasa Indonesia', 'deskripsi' => 'Mata pelajaran Bahasa Indonesia'],
            ['kode_mapel' => 'BING', 'nama_mapel' => 'Bahasa Inggris', 'deskripsi' => 'Mata pelajaran Bahasa Inggris'],
            ['kode_mapel' => 'FIS', 'nama_mapel' => 'Fisika', 'deskripsi' => 'Mata pelajaran Fisika'],
            ['kode_mapel' => 'KIM', 'nama_mapel' => 'Kimia', 'deskripsi' => 'Mata pelajaran Kimia'],
            ['kode_mapel' => 'PWPB', 'nama_mapel' => 'Pemrograman Web dan Perangkat Bergerak', 'deskripsi' => 'Mata pelajaran produktif RPL'],
            ['kode_mapel' => 'PPB', 'nama_mapel' => 'Pemrograman Berorientasi Objek', 'deskripsi' => 'Mata pelajaran produktif RPL'],
            ['kode_mapel' => 'BD', 'nama_mapel' => 'Basis Data', 'deskripsi' => 'Mata pelajaran produktif RPL'],
            ['kode_mapel' => 'PKK', 'nama_mapel' => 'Produk Kreatif dan Kewirausahaan', 'deskripsi' => 'Mata pelajaran kewirausahaan'],
            ['kode_mapel' => 'PAI', 'nama_mapel' => 'Pendidikan Agama Islam', 'deskripsi' => 'Mata pelajaran Agama Islam'],
        ];

        foreach ($mataPelajaran as $mapel) {
            DB::table('mata_pelajaran')->insert([
                'kode_mapel' => $mapel['kode_mapel'],
                'nama_mapel' => $mapel['nama_mapel'],
                'deskripsi' => $mapel['deskripsi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
