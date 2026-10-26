<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu periode kepengurusan, mis. "2026/2027"
        Schema::create('osis_periodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade'); // ekskul bertipe OSIS
            $table->string('nama', 20);
            $table->enum('status', ['rekrutmen', 'seleksi', 'berjalan', 'selesai'])->default('rekrutmen');
            $table->date('buka_pada')->nullable();
            $table->date('tutup_pada')->nullable();
            $table->timestamps();

            $table->unique(['ekstrakurikuler_id', 'nama']);
        });

        // Pengurus OSIS angkatan sebelumnya yang ditunjuk sebagai penilai CaSis pada periode tertentu
        Schema::create('osis_periode_seniors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osis_periode_id')->constrained('osis_periodes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['osis_periode_id', 'user_id']);
        });

        // Pendaftaran Calon Siswa OSIS (CaSis). Penggunanya tetap role siswa.
        Schema::create('casis_pendaftarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osis_periode_id')->constrained('osis_periodes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('motivasi')->nullable();
            $table->text('pengalaman')->nullable();
            $table->text('visi_misi')->nullable();
            $table->enum('status', ['draft', 'diajukan', 'diterima', 'ditolak', 'mengundurkan_diri'])->default('draft');
            $table->foreignId('didaftarkan_oleh')->nullable()->constrained('users')->nullOnDelete(); // kalau dimasukkan admin/pembina
            $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_keputusan')->nullable();
            $table->timestamp('diputuskan_pada')->nullable();
            $table->timestamps();

            $table->unique(['osis_periode_id', 'user_id']);
            $table->index(['osis_periode_id', 'status']);
        });

        // Penilaian dari tiga unsur: pembina OSIS, admin, dan senior
        Schema::create('casis_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('casis_pendaftaran_id')->constrained('casis_pendaftarans')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipe', ['pembina', 'admin', 'senior']);
            $table->unsignedTinyInteger('skor');                        // 0-100
            $table->enum('rekomendasi', ['lolos', 'pertimbangkan', 'tidak_lolos']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['casis_pendaftaran_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casis_reviews');
        Schema::dropIfExists('casis_pendaftarans');
        Schema::dropIfExists('osis_periode_seniors');
        Schema::dropIfExists('osis_periodes');
    }
};