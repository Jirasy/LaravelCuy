<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pembina yang BENAR-BENAR terhubung ke ekskul (bukan hanya teks nama).
        // selesai = NULL berarti masih aktif. Riwayat pembina lama tetap tersimpan.
        Schema::create('ekskul_pembinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('mulai');
            $table->date('selesai')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'selesai']);
            $table->index(['ekstrakurikuler_id', 'selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_pembinas');
    }
};