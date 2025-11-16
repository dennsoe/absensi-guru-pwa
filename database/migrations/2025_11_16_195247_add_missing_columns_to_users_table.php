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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama', 100)->after('password');
            $table->string('email', 100)->nullable()->unique()->after('nama');
            $table->string('nip', 50)->nullable()->after('email');
            $table->string('no_hp', 20)->nullable()->after('nip');
            $table->unsignedBigInteger('guru_id')->nullable()->after('no_hp');
            $table->unsignedBigInteger('kelas_id')->nullable()->after('guru_id');
            $table->boolean('is_active')->default(true)->after('kelas_id');

            // Foreign keys
            $table->foreign('guru_id')->references('id')->on('guru')->onDelete('set null');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('set null');

            // Indexes
            $table->index('guru_id');
            $table->index('kelas_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['nama', 'email', 'nip', 'no_hp', 'guru_id', 'kelas_id', 'is_active']);
        });
    }
};
