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
        Schema::create('eks_pengguna', function (Blueprint $table) {
            $table->id();
            $table->sring('username')->unique();
            $table->string('name');
            $table->string('password');
            $table->enum('peran', ['siswa','pembina','admin'])->default('siswa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eks_pengguna');
    }
};
