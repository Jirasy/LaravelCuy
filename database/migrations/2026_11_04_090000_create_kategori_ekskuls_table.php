<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori/label ekskul (mis. "Bela Diri", "Seni & Olahraga"). BEDA dengan tipe ekskul (jenis_ekskuls):
        // tipe menentukan cara masuk dan hanya satu per ekskul; kategori hanya label, satu ekskul boleh punya banyak.
        Schema::create('kategori_ekskuls', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 60)->unique();
            $table->string('slug', 70)->unique();            // pengenal tetap untuk URL filter katalog; tidak berubah walau nama diganti
            $table->unsignedSmallInteger('urutan')->default(50);
            $table->boolean('aktif')->default(true);         // nonaktif = tidak bisa dipilih lagi, label lama tetap melekat
            $table->timestamps();
        });

        Schema::create('ekstrakurikuler_kategori', function (Blueprint $table) {
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->foreignId('kategori_ekskul_id')->constrained('kategori_ekskuls')->cascadeOnDelete();
            $table->primary(['ekstrakurikuler_id', 'kategori_ekskul_id']);
        });

        // Kategori awal; admin bebas mengubah/menambah dari halaman Kategori Ekskul
        $sekarang = now();
        $awal = [
            ['nama' => 'Bela Diri',            'slug' => 'bela-diri',        'urutan' => 1],
            ['nama' => 'Seni & Olahraga',      'slug' => 'seni-olahraga',    'urutan' => 2],
            ['nama' => 'Akademik & Sains',     'slug' => 'akademik-sains',   'urutan' => 3],
            ['nama' => 'Kepemimpinan',         'slug' => 'kepemimpinan',     'urutan' => 4],
            ['nama' => 'Sosial & Kemanusiaan', 'slug' => 'sosial-kemanusiaan', 'urutan' => 5],
            ['nama' => 'Teknologi',            'slug' => 'teknologi',        'urutan' => 6],
        ];

        DB::table('kategori_ekskuls')->insert(array_map(
            fn ($baris) => $baris + ['aktif' => true, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            $awal
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler_kategori');
        Schema::dropIfExists('kategori_ekskuls');
    }
};
