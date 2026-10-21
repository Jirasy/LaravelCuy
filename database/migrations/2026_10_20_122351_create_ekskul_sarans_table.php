<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ekstrakurikuler_id NULL = kritik/saran umum untuk sekolah (ditangani admin)
        Schema::create('ekskul_sarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();   // pengirim (disembunyikan kalau anonim)
            $table->foreignId('ekstrakurikuler_id')->nullable()->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->enum('jenis', ['kritik', 'saran'])->default('saran');
            $table->text('isi');
            $table->boolean('anonim')->default(false);
            $table->enum('status', ['baru', 'dibaca', 'ditindaklanjuti'])->default('baru');
            $table->text('balasan')->nullable();
            $table->foreignId('dibalas_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dibalas_pada')->nullable();
            $table->timestamps();

            $table->index(['ekstrakurikuler_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_sarans');
    }
};