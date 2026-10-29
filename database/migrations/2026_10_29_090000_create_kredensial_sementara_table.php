<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Password sementara akun yang dibuatkan admin (mis. pembina baru). BUKAN hash: dienkripsi
        // dengan APP_KEY supaya admin bisa membukanya lagi. Baris dihapus otomatis saat pemilik akun
        // mengganti password, atau setelah kedaluwarsa.
        Schema::create('kredensial_sementara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ekstrakurikuler_id')->nullable()->constrained('ekstrakurikulers')->nullOnDelete();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('password_terenkripsi');
            $table->timestamp('kedaluwarsa_pada');
            $table->timestamp('terakhir_dilihat_pada')->nullable();
            $table->foreignId('terakhir_dilihat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('user_id');          // satu kredensial aktif per akun; reset menimpa
            $table->index('kedaluwarsa_pada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kredensial_sementara');
    }
};
