<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_ekskuls', function (Blueprint $table) {
            $table->id();
            // Nullable: pendaftar boleh isi manual tanpa akun login
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');

            // Data pendaftar (isi manual di form)
            $table->string('nama_pendaftar');
            $table->enum('peran', ['siswa', 'admin', 'pembina'])->default('siswa');
            $table->string('kelas')->nullable();
            $table->string('nisn')->nullable();
            $table->text('alasan_mendaftar')->nullable();

            // Keputusan: pending / diterima (Disetujui) / ditolak (Tidak Disetujui)
            $table->enum('status', ['pending', 'diterima', 'ditolak'])->default('pending');
            $table->text('alasan')->nullable(); // catatan / alasan keputusan (tetap dipertahankan)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_ekskuls');
    }
};