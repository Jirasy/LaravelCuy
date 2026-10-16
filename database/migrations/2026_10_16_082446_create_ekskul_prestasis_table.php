<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul_prestasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->string('judul');
            $table->enum('tingkat', ['sekolah', 'kecamatan', 'kabupaten', 'provinsi', 'nasional', 'internasional'])->default('sekolah');
            $table->string('peringkat', 100);          // mis. "Juara 1", "Harapan 2", "Finalis"
            $table->unsignedSmallInteger('tahun');
            $table->string('penyelenggara')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // Anggota (pendaftaran) yang meraih prestasi tersebut -> muncul di riwayat siswa
        Schema::create('ekskul_prestasi_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestasi_id')->constrained('ekskul_prestasis')->onDelete('cascade');
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_ekskuls')->onDelete('cascade');
            $table->unique(['prestasi_id', 'pendaftaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_prestasi_anggota');
        Schema::dropIfExists('ekskul_prestasis');
    }
};