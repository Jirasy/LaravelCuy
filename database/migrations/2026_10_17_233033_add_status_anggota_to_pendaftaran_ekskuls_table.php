<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            // Status keanggotaan SETELAH diterima. Kolom "status" (pending/diterima/ditolak) tetap untuk persetujuan MASUK.
            $table->enum('status_anggota', ['aktif', 'keluar', 'dikeluarkan'])->default('aktif')->after('status');
            $table->date('tanggal_keluar')->nullable()->after('tahun_selesai');
        });

        // Data lama: anggota yang "tahun selesai"-nya sudah diisi dianggap sudah keluar
        DB::table('pendaftaran_ekskuls')
            ->where('status', 'diterima')
            ->whereNotNull('tahun_selesai')
            ->where('tahun_selesai', '<=', now()->year)
            ->update(['status_anggota' => 'keluar']);
    }

    public function down(): void
    {
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->dropColumn(['status_anggota', 'tanggal_keluar']);
        });
    }
};