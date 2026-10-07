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
            $table->string('nama_pendaftar')->nullable()->after('ekstrakurikuler_id');
            $table->enum('peran', ['siswa', 'admin', 'pembina'])->default('siswa')->after('nama_pendaftar');
            $table->string('kelas')->nullable()->after('peran');
            $table->string('nisn')->nullable()->after('kelas');
            $table->text('alasan_mendaftar')->nullable()->after('nisn');
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Isi nama untuk pendaftaran lama dari tabel users
        DB::statement('UPDATE pendaftaran_ekskuls p JOIN users u ON u.id = p.user_id SET p.nama_pendaftar = u.name, p.kelas = u.kelas, p.nisn = u.nisn');
    }

    public function down(): void
    {
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->dropColumn(['nama_pendaftar', 'peran', 'kelas', 'nisn', 'alasan_mendaftar']);
        });
    }
};