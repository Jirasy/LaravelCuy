<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penilaian oleh pembina/admin: satu baris = satu anggota di satu periode
        Schema::create('ekskul_nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_ekskuls')->onDelete('cascade');
            $table->string('periode', 60);                       // mis. "Semester Ganjil 2026/2027"
            $table->unsignedTinyInteger('keterampilan');         // 0-100
            $table->unsignedTinyInteger('disiplin');
            $table->unsignedTinyInteger('kerja_sama');
            $table->unsignedTinyInteger('sikap');
            $table->text('saran')->nullable();
            $table->text('kritik')->nullable();
            $table->foreignId('penilai_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['pendaftaran_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_nilais');
    }
};