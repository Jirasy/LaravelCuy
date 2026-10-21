<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PR / tugas ekskul yang dikumpulkan dan dinilai (berbeda dari "task" to-do di Fase E)
        Schema::create('ekskul_prs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->date('tenggat')->nullable();
            $table->unsignedTinyInteger('nilai_maks')->default(100);
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ekstrakurikuler_id', 'created_at']);
        });

        // Satu anggota mengumpulkan satu kali per PR (boleh diperbarui selama belum dinilai)
        Schema::create('ekskul_pr_pengumpulans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->constrained('ekskul_prs')->onDelete('cascade');
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_ekskuls')->onDelete('cascade');
            $table->text('jawaban')->nullable();
            $table->string('berkas')->nullable();
            $table->string('nama_berkas')->nullable();
            $table->string('mime', 100)->nullable();
            $table->timestamp('dikumpulkan_pada');
            $table->unsignedTinyInteger('nilai')->nullable();
            $table->text('komentar')->nullable();
            $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dinilai_pada')->nullable();
            $table->timestamps();

            $table->unique(['pr_id', 'pendaftaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_pr_pengumpulans');
        Schema::dropIfExists('ekskul_prs');
    }
};