<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // nama_ekstrakurikuler tetap menjadi NAMA LENGKAP (dipakai di detail, laporan, notifikasi).
        // nama_singkatan = alias untuk katalog/kartu/badge, mis. "Palang Merah Remaja" -> "PMR". Boleh kosong.
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->string('nama_singkatan', 30)->nullable()->unique()->after('nama_ekstrakurikuler');
        });
    }

    public function down(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropUnique(['nama_singkatan']);
            $table->dropColumn('nama_singkatan');
        });
    }
};
