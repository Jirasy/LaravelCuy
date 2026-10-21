<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul_materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->enum('jenis', ['video', 'foto', 'dokumen', 'link']);
            $table->string('berkas')->nullable();        // path di disk privat (jenis selain link)
            $table->string('nama_berkas')->nullable();   // nama asli, untuk tampilan & unduhan
            $table->string('mime', 100)->nullable();
            $table->string('url', 500)->nullable();      // untuk jenis link (mis. YouTube)
            $table->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ekstrakurikuler_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_materis');
    }
};