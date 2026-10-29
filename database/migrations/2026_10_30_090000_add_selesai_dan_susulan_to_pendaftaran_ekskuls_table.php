<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "selesai" = purna tugas: sudah tidak menjabat, tapi riwayat kepengurusan (mis. OSIS angkatan lalu) tetap tercatat.
        // Beda dengan "keluar" (mengundurkan diri) dan "dikeluarkan".
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->enum('status_anggota', ['aktif', 'keluar', 'dikeluarkan', 'selesai'])->default('aktif')->change();
        });

        // Siapa yang mencatat kepengurusan secara susulan/manual (jejak audit)
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->foreignId('dibuat_susulan_oleh')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dibuat_susulan_oleh');
        });

        DB::table('pendaftaran_ekskuls')->where('status_anggota', 'selesai')->update(['status_anggota' => 'keluar']);

        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->enum('status_anggota', ['aktif', 'keluar', 'dikeluarkan'])->default('aktif')->change();
        });
    }
};
