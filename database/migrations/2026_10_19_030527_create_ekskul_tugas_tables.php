<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->nullable()->constrained('ekstrakurikulers')->nullOnDelete();
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->date('tenggat')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Satu task bisa diberikan ke banyak orang; tiap orang punya status sendiri
        Schema::create('ekskul_tugas_penerimas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('ekskul_tugas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['belum', 'dikerjakan', 'selesai'])->default('belum');
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();

            $table->unique(['tugas_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_tugas_penerimas');
        Schema::dropIfExists('ekskul_tugas');
    }
};