<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();                       // pemilik profil
            $table->foreignId('pemberi_id')->nullable()->constrained('users')->nullOnDelete();    // yang memberi tag
            $table->string('teks', 30);
            $table->enum('status', ['privat', 'tampil'])->default('privat');                      // privat = hanya pemilik (+ admin/pembina)
            $table->timestamps();

            $table->unique(['user_id', 'pemberi_id', 'teks']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_tags');
    }
};
