<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->enum('jabatan', ['anggota', 'ketua', 'wakil_ketua', 'sekretaris', 'bendahara', 'koordinator', 'pelatih', 'pembina_pendamping'])
                  ->default('anggota')->after('peran');
            $table->string('angkatan_ekskul', 20)->nullable()->after('jabatan');
            $table->unsignedSmallInteger('tahun_mulai')->nullable()->after('angkatan_ekskul');
            $table->unsignedSmallInteger('tahun_selesai')->nullable()->after('tahun_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->dropColumn(['jabatan', 'angkatan_ekskul', 'tahun_mulai', 'tahun_selesai']);
        });
    }
};