<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->boolean('pendaftaran_dibuka')->default(true)->after('status');   // saklar buka/tutup manual
            $table->dateTime('pendaftaran_tutup_pada')->nullable()->after('pendaftaran_dibuka'); // tutup otomatis
            $table->unsignedSmallInteger('kuota')->nullable()->after('pendaftaran_tutup_pada');   // null = tanpa batas
        });
    }

    public function down(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropColumn(['pendaftaran_dibuka', 'pendaftaran_tutup_pada', 'kuota']);
        });
    }
};