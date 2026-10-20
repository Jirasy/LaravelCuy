<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ekstrakurikuler_id NULL = tautan milik sekolah (tampil di menu navbar untuk semua)
        Schema::create('ekskul_tautans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->nullable()->constrained('ekstrakurikulers')->onDelete('cascade');
            $table->enum('platform', ['youtube', 'instagram', 'tiktok', 'whatsapp', 'website']);
            $table->string('label', 60);
            $table->string('url', 500);
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_tautans');
    }
};