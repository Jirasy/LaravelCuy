<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            // 'osis' = ekskul khusus OSIS (hanya boleh satu). Anggota, jabatan, pengumuman, galeri, dst ikut memakai fitur ekskul.
            $table->enum('jenis', ['ekskul', 'osis'])->default('ekskul')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};