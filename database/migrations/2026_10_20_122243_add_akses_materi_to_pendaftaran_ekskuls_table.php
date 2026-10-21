<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            // true = anggota ini diizinkan pembina/admin membuka Materi & PR ekskulnya
            $table->boolean('akses_materi')->default(false)->after('status_anggota');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->dropColumn('akses_materi');
        });
    }
};