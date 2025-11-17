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
        // Untuk MySQL, kita perlu alter enum dengan raw SQL
        DB::statement("ALTER TABLE guru MODIFY COLUMN status_kepegawaian ENUM('PNS', 'PPPK', 'GTT', 'GTY', 'Honorer', 'aktif') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE guru MODIFY COLUMN status_kepegawaian ENUM('PNS', 'PPPK', 'GTT', 'GTY') NULL");
    }
};
