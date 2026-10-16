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
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('role'); // ini buat foto profil user
            $table->string('wallpaper')->nullable()->after('foto'); // ini buat wallpaper user
            $table->string('palet')->nullable()->after('wallpaper'); // ini buat palet warna user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['foto', 'wallpaper', 'palet']);
        });
    }
};
