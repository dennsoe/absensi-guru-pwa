<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_mengajar')->onDelete('set null');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpha', 'Terlambat', 'Cuti'])->default('Alpha');
            $table->enum('metode_absen', ['QR Code', 'Selfie', 'Manual'])->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->decimal('latitude_pulang', 10, 8)->nullable();
            $table->decimal('longitude_pulang', 11, 8)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('surat_izin')->nullable();
            $table->enum('validasi_gps', ['Valid', 'Invalid', 'Tidak Divalidasi'])->default('Tidak Divalidasi');
            $table->foreignId('diinput_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['guru_id', 'tanggal']);
            $table->index('status');
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
