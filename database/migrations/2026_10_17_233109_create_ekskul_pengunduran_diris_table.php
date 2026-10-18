<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengajuan keluar (anggota / pembina) dan catatan pengeluaran oleh admin/pembina
        Schema::create('ekskul_pengunduran_diris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();          // yang keluar
            $table->foreignId('pendaftaran_id')->nullable()->constrained('pendaftaran_ekskuls')->nullOnDelete();
            $table->enum('peran', ['anggota', 'pembina']);
            $table->enum('jenis', ['mengundurkan_diri', 'dikeluarkan']);
            $table->text('alasan');
            $table->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_keputusan')->nullable();
            $table->timestamp('diputuskan_pada')->nullable();
            $table->timestamps();

            $table->index(['status', 'ekstrakurikuler_id']);
            $table->index(['user_id', 'ekstrakurikuler_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_pengunduran_diris');
    }
};