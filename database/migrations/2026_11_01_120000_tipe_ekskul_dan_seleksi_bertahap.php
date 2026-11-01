<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tipe ekskul + seleksi bertahap.
 *
 * Dibuat idempoten: setiap langkah mengecek dulu apakah tabel/kolomnya sudah ada.
 * - Database kosong (mis. tes SQLite :memory:) -> semua dibuat dari nol.
 * - Database dev yang sebagian sudah terubah -> hanya bagian yang kurang yang dilengkapi.
 */
return new class extends Migration
{
    public function up(): void
    {
        $sekarang = now();

        // ---- 1. Tipe ekskul: Ekskul Biasa, Ekskul Seleksi, Organisasi, Komunitas ----
        if (! Schema::hasColumn('jenis_ekskuls', 'kategori')) {
            Schema::table('jenis_ekskuls', function (Blueprint $table) {
                $table->string('kategori', 20)->default('ekskul')->after('alur_pendaftaran'); // ekskul | organisasi | komunitas
            });
        }

        // Alur "seleksi_casis" (khusus OSIS) menjadi "seleksi" yang berlaku untuk semua ekskul
        DB::table('jenis_ekskuls')->where('alur_pendaftaran', 'seleksi_casis')->update(['alur_pendaftaran' => 'seleksi']);

        // Baris OSIS lama menjadi tipe Organisasi (ekskul OSIS yang sudah ada otomatis ikut)
        if (! DB::table('jenis_ekskuls')->where('kode', 'organisasi')->exists()) {
            DB::table('jenis_ekskuls')->where('kode', 'osis')->update(['kode' => 'organisasi']);
        }

        $tipeBawaan = [
            ['kode' => 'ekskul',     'nama' => 'Ekskul Biasa',   'alur_pendaftaran' => 'langsung', 'kategori' => 'ekskul',     'urutan' => 0],
            ['kode' => 'seleksi',    'nama' => 'Ekskul Seleksi', 'alur_pendaftaran' => 'seleksi',  'kategori' => 'ekskul',     'urutan' => 1],
            ['kode' => 'organisasi', 'nama' => 'Organisasi',     'alur_pendaftaran' => 'seleksi',  'kategori' => 'organisasi', 'urutan' => 2],
            ['kode' => 'komunitas',  'nama' => 'Komunitas',      'alur_pendaftaran' => 'langsung', 'kategori' => 'komunitas',  'urutan' => 3],
        ];

        foreach ($tipeBawaan as $tipe) {
            // hanya_satu = false: organisasi boleh lebih dari satu (mis. MPK dan OSIS di bawahnya)
            $nilai = $tipe + ['hanya_satu' => false, 'aktif' => true, 'bawaan' => true, 'updated_at' => $sekarang];

            if (DB::table('jenis_ekskuls')->where('kode', $tipe['kode'])->exists()) {
                DB::table('jenis_ekskuls')->where('kode', $tipe['kode'])->update($nilai);
            } else {
                DB::table('jenis_ekskuls')->insert($nilai + ['created_at' => $sekarang]);
            }
        }

        // Kolom kompatibilitas pada ekstrakurikulers (biasanya sudah ada dari migrasi sebelumnya)
        if (! Schema::hasColumn('ekstrakurikulers', 'jenis')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->enum('jenis', ['ekskul', 'osis'])->default('ekskul')->after('status');
            });
        }

        if (! Schema::hasColumn('ekstrakurikulers', 'jenis_ekskul_id')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->foreignId('jenis_ekskul_id')->nullable()->after('jenis')
                    ->constrained('jenis_ekskuls')->nullOnDelete();
            });
        }

        // Jenis lama yang salah kaprah (MPK, Pramuka, PMR, Paskibra) dipetakan ulang lalu dihapus:
        // MPK -> Organisasi, sisanya -> Ekskul Biasa (admin bisa mengubahnya ke Ekskul Seleksi dari Edit Ekskul)
        $idTipe = fn (string $kode) => DB::table('jenis_ekskuls')->where('kode', $kode)->value('id');

        foreach (['mpk' => 'organisasi', 'pramuka' => 'ekskul', 'pmr' => 'ekskul', 'paskibra' => 'ekskul'] as $lama => $baru) {
            $idLama = $idTipe($lama);

            if ($idLama) {
                DB::table('ekstrakurikulers')->where('jenis_ekskul_id', $idLama)->update(['jenis_ekskul_id' => $idTipe($baru)]);
                DB::table('jenis_ekskuls')->where('id', $idLama)->delete();
            }
        }

        $idEkskul = $idTipe('ekskul');
        $idOrganisasi = $idTipe('organisasi');

        if (! $idEkskul || ! $idOrganisasi) {
            throw new RuntimeException('Tipe ekskul bawaan gagal disiapkan.');
        }

        // Ekskul tanpa tipe menjadi Ekskul Biasa; ekskul OSIS (dikenali dari kolom lama atau namanya) menjadi Organisasi
        DB::table('ekstrakurikulers')->whereNull('jenis_ekskul_id')->update(['jenis_ekskul_id' => $idEkskul]);

        DB::table('ekstrakurikulers')
            ->where(function ($q) {
                $q->where('jenis', 'osis')
                    ->orWhere('nama_ekstrakurikuler', 'like', '%Organisasi Siswa Intra Sekolah%');
            })
            ->update(['jenis' => 'osis', 'jenis_ekskul_id' => $idOrganisasi]);

        // Kolom lama "jenis" hanya penanda kompatibilitas: osis = bertipe organisasi
        DB::table('ekstrakurikulers')->where('jenis_ekskul_id', $idOrganisasi)->update(['jenis' => 'osis']);
        DB::table('ekstrakurikulers')->where('jenis_ekskul_id', '!=', $idOrganisasi)->update(['jenis' => 'ekskul']);

        // ---- 2. Komunitas: siapa pembuatnya dan kapan diresmikan admin ----
        if (! Schema::hasColumn('ekstrakurikulers', 'dibuat_oleh')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('ekstrakurikulers', 'diresmikan_pada')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->timestamp('diresmikan_pada')->nullable();
            });
        }

        if (! Schema::hasColumn('ekstrakurikulers', 'diresmikan_oleh')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->foreignId('diresmikan_oleh')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        // ---- 3. Seleksi bertahap: jumlah tahap bebas, jadwal & tempat diatur pengelola/senior ----
        if (! Schema::hasTable('seleksi_tahaps')) {
            Schema::create('seleksi_tahaps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('osis_periode_id')->constrained('osis_periodes')->onDelete('cascade');
                $table->unsignedSmallInteger('urutan');
                $table->string('nama', 60);
                $table->dateTime('mulai_pada')->nullable();
                $table->dateTime('selesai_pada')->nullable();
                $table->string('tempat', 120)->nullable();
                $table->text('keterangan')->nullable();
                $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['osis_periode_id', 'urutan']);
            });
        }

        // Hasil tiap pendaftar di tiap tahap (lolos / tidak lolos)
        if (! Schema::hasTable('seleksi_hasils')) {
            Schema::create('seleksi_hasils', function (Blueprint $table) {
                $table->id();
                $table->foreignId('casis_pendaftaran_id')->constrained('casis_pendaftarans')->onDelete('cascade');
                $table->foreignId('seleksi_tahap_id')->constrained('seleksi_tahaps')->onDelete('cascade');
                $table->string('hasil', 12); // lolos | tidak_lolos
                $table->text('catatan')->nullable();
                $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['casis_pendaftaran_id', 'seleksi_tahap_id'], 'seleksi_hasils_pendaftaran_tahap_unique');
            });
        }

        // Penilaian kini per tahap. Indeks unik baru dipasang dulu, baru yang lama dilepas
        // (supaya kunci asing tetap punya indeks pendukung di MySQL). Dilewati kalau kolomnya sudah ada.
        if (! Schema::hasColumn('casis_reviews', 'seleksi_tahap_id')) {
            Schema::table('casis_reviews', function (Blueprint $table) {
                $table->foreignId('seleksi_tahap_id')->nullable()->constrained('seleksi_tahaps')->onDelete('cascade');
                $table->unique(['casis_pendaftaran_id', 'reviewer_id', 'seleksi_tahap_id'], 'casis_reviews_pendaftar_penilai_tahap_unique');
            });

            Schema::table('casis_reviews', function (Blueprint $table) {
                $table->dropUnique(['casis_pendaftaran_id', 'reviewer_id']);
            });
        }

        // ---- 4. Data lama: setiap periode mendapat tahap 1; penilaian & keputusan lama ikut ke tahap itu ----
        foreach (DB::table('osis_periodes')->get() as $periode) {
            $idTahap = DB::table('seleksi_tahaps')
                ->where('osis_periode_id', $periode->id)
                ->where('urutan', 1)
                ->value('id');

            if ($idTahap) {
                continue; // periode ini sudah punya tahap 1, jangan digandakan
            }

            $idTahap = DB::table('seleksi_tahaps')->insertGetId([
                'osis_periode_id' => $periode->id,
                'urutan'          => 1,
                'nama'            => 'Seleksi',
                'created_at'      => $sekarang,
                'updated_at'      => $sekarang,
            ]);

            $pendaftar = DB::table('casis_pendaftarans')->where('osis_periode_id', $periode->id)->get();

            DB::table('casis_reviews')
                ->whereIn('casis_pendaftaran_id', $pendaftar->pluck('id'))
                ->whereNull('seleksi_tahap_id')
                ->update(['seleksi_tahap_id' => $idTahap]);

            foreach ($pendaftar->whereIn('status', ['diterima', 'ditolak']) as $c) {
                DB::table('seleksi_hasils')->insert([
                    'casis_pendaftaran_id' => $c->id,
                    'seleksi_tahap_id'     => $idTahap,
                    'hasil'                => $c->status === 'diterima' ? 'lolos' : 'tidak_lolos',
                    'catatan'              => $c->catatan_keputusan,
                    'diputuskan_oleh'      => $c->diputuskan_oleh,
                    'created_at'           => $c->diputuskan_pada ?? $sekarang,
                    'updated_at'           => $c->diputuskan_pada ?? $sekarang,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Sengaja tidak menghapus tabel atau kolom agar data tidak hilang.
    }
};