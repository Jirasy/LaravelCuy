<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_panggilans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 30);
            $table->boolean('utama')->default(false); // dipakai untuk sapaan "Halo, ...!" di dashboard
            $table->timestamps();

            $table->unique(['user_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_panggilans');
    }
};
