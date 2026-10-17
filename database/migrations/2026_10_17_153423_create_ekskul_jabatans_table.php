<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daftar jabatan MILIK TIAP EKSKUL (struktur tiap ekskul boleh berbeda)
        Schema::create('ekskul_jabatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->string('nama', 100);                       // mis. "Sekretaris"
            $table->string('varian', 20)->default('');         // mis. "1", "2", "Humas" ('' = tanpa variasi)
            $table->text('deskripsi')->nullable();
            $table->text('tugas')->nullable();
            $table->text('tanggung_jawab')->nullable();
            $table->unsignedTinyInteger('urutan')->default(0); // urutan tampil (kecil = atas)
            $table->unsignedTinyInteger('kuota')->nullable();  // maksimal pemegang aktif (null = bebas)
            $table->timestamps();

            $table->unique(['ekstrakurikuler_id', 'nama', 'varian']);
        });

        // Penetapan jabatan ke anggota sekaligus RIWAYATNYA (periode_selesai NULL = masih menjabat)
        Schema::create('ekskul_anggota_jabatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_ekskuls')->onDelete('cascade');
            $table->foreignId('jabatan_id')->constrained('ekskul_jabatans')->onDelete('cascade');
            $table->unsignedSmallInteger('periode_mulai');
            $table->unsignedSmallInteger('periode_selesai')->nullable();
            $table->foreignId('ditetapkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jabatan_id', 'periode_selesai']);
            $table->index(['pendaftaran_id', 'periode_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_anggota_jabatans');
        Schema::dropIfExists('ekskul_jabatans');
    }
};