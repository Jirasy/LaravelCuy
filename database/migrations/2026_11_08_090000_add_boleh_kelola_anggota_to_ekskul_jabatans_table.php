<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Jabatan bertanda "penanggung jawab anggota": pemegangnya (anggota aktif) ikut mengatur kuota, buka/tutup
    // pendaftaran, dan menyetujui/menolak pendaftar ekskulnya. Bawaan mati; hanya admin/pembina yang menandainya.
    // Dijaga hasColumn supaya aman dijalankan di database yang kolomnya sudah ada.
    public function up(): void
    {
        if (Schema::hasColumn('ekskul_jabatans', 'boleh_kelola_anggota')) {
            return;
        }

        Schema::table('ekskul_jabatans', function (Blueprint $table) {
            $table->boolean('boleh_kelola_anggota')->default(false)->after('kuota');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('ekskul_jabatans', 'boleh_kelola_anggota')) {
            return;
        }

        Schema::table('ekskul_jabatans', function (Blueprint $table) {
            $table->dropColumn('boleh_kelola_anggota');
        });
    }
};