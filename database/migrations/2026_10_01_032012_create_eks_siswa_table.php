<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eks_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->constrained('eks_pengguna')->onDelete('cascade');
            $table->string('nis');
            $table->string('kelas');
            $table->string('jurusan');
            $table->string('no_hp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eks_siswa');
    }
};
