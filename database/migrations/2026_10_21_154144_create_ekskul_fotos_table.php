<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained('ekskul_events')->nullOnDelete(); // opsional: foto dari event tertentu
            $table->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('berkas');                    // path foto asli (disk privat)
            $table->string('thumb')->nullable();         // path thumbnail (null kalau server tidak punya GD)
            $table->string('nama_berkas')->nullable();
            $table->string('mime', 100)->nullable();
            $table->string('caption', 200)->nullable();
            $table->enum('kategori', ['latihan', 'tanding', 'lomba', 'lainnya'])->default('latihan');
            $table->date('diambil_pada')->nullable();
            $table->boolean('disetujui')->default(false); // foto siswa menunggu persetujuan pengelola
            $table->timestamps();

            $table->index(['ekstrakurikuler_id', 'disetujui', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_fotos');
    }
};