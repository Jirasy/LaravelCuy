<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Wallpaper: default (palet) | polos | gradasi | foto (berkas di kolom wallpaper, boleh GIF) | url
            $table->string('wallpaper_tipe', 10)->default('default');
            $table->string('wallpaper_warna1', 7)->nullable();
            $table->string('wallpaper_warna2', 7)->nullable();
            $table->string('wallpaper_url', 500)->nullable();
            $table->string('wallpaper_mode', 6)->default('tutupi'); // tutupi | ulang (pola, mis. batik)

            // Aksen kartu profil: preset (kolom kartu_aksen) | polos | gradasi | foto (aksen_gambar) | url
            $table->string('aksen_tipe', 10)->default('preset');
            $table->string('aksen_warna1', 7)->nullable();
            $table->string('aksen_warna2', 7)->nullable();
            $table->string('aksen_gambar')->nullable();
            $table->string('aksen_url', 500)->nullable();
            $table->string('aksen_mode', 6)->default('tutupi');
        });

        // Pengguna yang sudah punya foto wallpaper tetap memakainya
        DB::table('users')->whereNotNull('wallpaper')->update(['wallpaper_tipe' => 'foto']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'wallpaper_tipe', 'wallpaper_warna1', 'wallpaper_warna2', 'wallpaper_url', 'wallpaper_mode',
                'aksen_tipe', 'aksen_warna1', 'aksen_warna2', 'aksen_gambar', 'aksen_url', 'aksen_mode',
            ]);
        });
    }
};
