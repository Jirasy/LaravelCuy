<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bentrok_jadwal_pilihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu']);
            // Pasangan ekskul yang bentrok; ekskul_a_id selalu yang lebih kecil supaya pasangannya unik
            $table->foreignId('ekskul_a_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->foreignId('ekskul_b_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            // Siswa hadir di ekskul A, hadir di ekskul B, atau membagi waktu (dijelaskan di catatan)
            $table->enum('pilihan', ['ekskul_a', 'ekskul_b', 'bagi_waktu']);
            $table->string('catatan', 200)->nullable();
            $table->timestamps();

            // Nama indeks dipendekkan supaya tidak melewati batas 64 karakter di MySQL
            $table->unique(['user_id', 'hari', 'ekskul_a_id', 'ekskul_b_id'], 'bentrok_pilihan_unik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bentrok_jadwal_pilihans');
    }
};
