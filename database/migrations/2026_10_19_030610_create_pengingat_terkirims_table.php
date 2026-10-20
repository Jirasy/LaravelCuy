<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catatan pengingat yang sudah dikirim, supaya tidak terkirim dobel untuk jadwal + tanggal yang sama
        Schema::create('pengingat_terkirims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekskul_hari_id')->constrained('ekskul_haris')->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamps();

            $table->unique(['ekskul_hari_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengingat_terkirims');
    }
};