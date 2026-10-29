<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jenis/kategori ekskul yang bisa diatur admin (OSIS, MPK, Pramuka, PMR, Paskibra, dst), bukan lagi enum tetap.
        Schema::create('jenis_ekskuls', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 60);
            // langsung = daftar lalu disetujui pembina; seleksi_casis = alur CaSis OSIS (string, bukan enum: mudah ditambah alur baru)
            $table->string('alur_pendaftaran', 20)->default('langsung');
            $table->boolean('hanya_satu')->default(false);   // hanya boleh ada satu ekskul berjenis ini (mis. OSIS, MPK)
            $table->boolean('aktif')->default(true);         // nonaktif = tidak bisa dipilih untuk ekskul baru
            $table->boolean('bawaan')->default(false);       // jenis sistem: tidak bisa dihapus/dinonaktifkan
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        $sekarang = now();
        $awal = [
            ['kode' => 'ekskul',   'nama' => 'Ekskul Biasa', 'alur_pendaftaran' => 'langsung',      'hanya_satu' => false, 'bawaan' => true,  'urutan' => 0],
            ['kode' => 'osis',     'nama' => 'OSIS',         'alur_pendaftaran' => 'seleksi_casis', 'hanya_satu' => true,  'bawaan' => true,  'urutan' => 1],
            ['kode' => 'mpk',      'nama' => 'MPK',          'alur_pendaftaran' => 'langsung',      'hanya_satu' => true,  'bawaan' => false, 'urutan' => 2],
            ['kode' => 'pramuka',  'nama' => 'Pramuka',      'alur_pendaftaran' => 'langsung',      'hanya_satu' => false, 'bawaan' => false, 'urutan' => 3],
            ['kode' => 'pmr',      'nama' => 'PMR',          'alur_pendaftaran' => 'langsung',      'hanya_satu' => false, 'bawaan' => false, 'urutan' => 4],
            ['kode' => 'paskibra', 'nama' => 'Paskibra',     'alur_pendaftaran' => 'langsung',      'hanya_satu' => false, 'bawaan' => false, 'urutan' => 5],
        ];

        DB::table('jenis_ekskuls')->insert(array_map(
            fn ($baris) => $baris + ['aktif' => true, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            $awal
        ));

        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->foreignId('jenis_ekskul_id')->nullable()->constrained('jenis_ekskuls')->nullOnDelete();
        });

        // Data lama: kolom "jenis" (ekskul/osis) dipetakan ke jenis baru
        $idEkskul = DB::table('jenis_ekskuls')->where('kode', 'ekskul')->value('id');
        $idOsis   = DB::table('jenis_ekskuls')->where('kode', 'osis')->value('id');

        DB::table('ekstrakurikulers')->where('jenis', 'osis')->update(['jenis_ekskul_id' => $idOsis]);
        DB::table('ekstrakurikulers')->where(fn ($q) => $q->where('jenis', '!=', 'osis')->orWhereNull('jenis'))
            ->update(['jenis_ekskul_id' => $idEkskul]);
    }

    public function down(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_ekskul_id');
        });

        Schema::dropIfExists('jenis_ekskuls');
    }
};
