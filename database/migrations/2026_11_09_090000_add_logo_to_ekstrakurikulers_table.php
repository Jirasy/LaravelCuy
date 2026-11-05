<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Logo ekskul: path relatif di disk "public" (mis. logo-ekskul/12-ab34cd.png). Kosong = tampilkan monogram dari nama.
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('nama_singkatan');
        });
    }

    public function down(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
