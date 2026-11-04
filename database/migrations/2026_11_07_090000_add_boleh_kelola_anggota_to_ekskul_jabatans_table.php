<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jabatan "penanggung jawab anggota": pemegang aktifnya (senior/pengurus) boleh ikut mengelola jumlah anggota
        // ekskul itu (kuota, buka/tutup pendaftaran, menyetujui/menolak pendaftar). Mati secara bawaan; hanya admin dan
        // pembina ekskul yang bisa menyalakannya.
        Schema::table('ekskul_jabatans', function (Blueprint $table) {
            $table->boolean('boleh_kelola_anggota')->default(false)->after('kuota');
        });
    }

    public function down(): void
    {
        Schema::table('ekskul_jabatans', function (Blueprint $table) {
            $table->dropColumn('boleh_kelola_anggota');
        });
    }
};
