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
        Schema::create('eks_eskul', function (Blueprint $table) {
            $table->id();
            $table->string('eks_name')->unique();
            $table->string('eks_description')->nullable();
            $table->foreignId('eks_pembina_id')->constrained('eks_pengguna')->onDelete('cascade');
            $table->string('eks_jadwal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eks_eskul');
    }
};
