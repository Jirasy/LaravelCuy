<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu pertemuan/latihan
        Schema::create('ekskul_pertemuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('topik', 200)->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ekstrakurikuler_id', 'tanggal']);
        });

        // Kehadiran tiap anggota (pendaftaran) di satu pertemuan
        Schema::create('ekskul_kehadirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_id')->constrained('ekskul_pertemuans')->onDelete('cascade');
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_ekskuls')->onDelete('cascade');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->string('catatan', 200)->nullable();
            $table->timestamps();

            $table->unique(['pertemuan_id', 'pendaftaran_id']);
            $table->index(['pendaftaran_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_kehadirans');
        Schema::dropIfExists('ekskul_pertemuans');
    }
};