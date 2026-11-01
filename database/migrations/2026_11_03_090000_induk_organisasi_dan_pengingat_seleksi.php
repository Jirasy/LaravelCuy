<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hierarki organisasi: OSIS berada di bawah MPK -> OSIS.induk_id = id MPK. Hanya berarti untuk tipe Organisasi.
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->foreignId('induk_id')->nullable()->constrained('ekstrakurikulers')->nullOnDelete();
        });

        // Catatan pengingat jadwal seleksi yang sudah terkirim, supaya tidak dobel. Dihapus saat jadwal tahap diubah
        // (jadi pengingat dikirim ulang untuk jadwal yang baru).
        Schema::create('seleksi_pengingats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seleksi_tahap_id')->constrained('seleksi_tahaps')->onDelete('cascade');
            $table->string('jenis', 8); // 24j = dalam 24 jam, 1j = kurang dari 1 jam
            $table->timestamp('dikirim_pada')->nullable();
            $table->timestamps();

            $table->unique(['seleksi_tahap_id', 'jenis']);
        });

        // MPK dan OSIS sama-sama bertipe Organisasi, jadi tipe ini tidak boleh lagi dibatasi "hanya satu ekskul"
        DB::table('jenis_ekskuls')
            ->where('kategori', 'organisasi')
            ->update(['hanya_satu' => false]);
    }

    public function down(): void
    {
        Schema::dropIfExists('seleksi_pengingats');

        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('induk_id');
        });
    }
};