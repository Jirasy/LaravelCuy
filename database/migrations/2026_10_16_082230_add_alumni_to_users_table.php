<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // role = hak akses (admin/pembina/siswa). Alumni BUKAN role, hanya status.
            $table->boolean('is_alumni')->default(false)->after('role');
            $table->string('angkatan_sekolah', 20)->nullable()->after('is_alumni');
            $table->unsignedSmallInteger('tahun_lulus')->nullable()->after('angkatan_sekolah');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_alumni', 'angkatan_sekolah', 'tahun_lulus']);
        });
    }
};