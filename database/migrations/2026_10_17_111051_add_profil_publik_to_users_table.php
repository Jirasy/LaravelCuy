<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Profil publik
            $table->string('no_hp', 20)->nullable()->after('kelas');              // format 62812xxxx
            $table->enum('no_hp_tampil', ['staff', 'anggota_ekskul', 'semua'])->default('staff')->after('no_hp');
            $table->string('bio', 160)->nullable()->after('no_hp_tampil');

            // Kustomisasi tampilan (preset terbatas supaya selalu rapi dan kontras)
            $table->enum('navbar_gaya', ['melayang', 'kaca', 'solid', 'ringkas'])->default('melayang')->after('palet');
            $table->enum('kartu_gaya', ['kaca', 'gradasi', 'polos'])->default('kaca')->after('navbar_gaya');
            $table->enum('kartu_aksen', ['ikut_palet', 'indigo', 'rose', 'emerald', 'amber', 'sky'])->default('ikut_palet')->after('kartu_gaya');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['no_hp', 'no_hp_tampil', 'bio', 'navbar_gaya', 'kartu_gaya', 'kartu_aksen']);
        });
    }
};