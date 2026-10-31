<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * 1. Pulihkan tabel jenis_ekskuls jika belum tersedia.
         */
        if (!Schema::hasTable('jenis_ekskuls')) {
            Schema::create('jenis_ekskuls', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 30)->unique();
                $table->string('nama', 60);
                $table->string('alur_pendaftaran', 20)->default('langsung');
                $table->string('kategori', 20)->default('ekskul');
                $table->boolean('hanya_satu')->default(false);
                $table->boolean('aktif')->default(true);
                $table->boolean('bawaan')->default(false);
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('jenis_ekskuls', 'kategori')) {
            Schema::table('jenis_ekskuls', function (Blueprint $table) {
                $table->string('kategori', 20)
                    ->default('ekskul')
                    ->after('alur_pendaftaran');
            });
        }

        /*
         * 2. Pastikan empat tipe bawaan tersedia.
         */
        $sekarang = now();

        $tipeBawaan = [
            [
                'kode' => 'ekskul',
                'nama' => 'Ekskul Biasa',
                'alur_pendaftaran' => 'langsung',
                'kategori' => 'ekskul',
                'hanya_satu' => false,
                'aktif' => true,
                'bawaan' => true,
                'urutan' => 0,
            ],
            [
                'kode' => 'seleksi',
                'nama' => 'Ekskul Seleksi',
                'alur_pendaftaran' => 'seleksi',
                'kategori' => 'ekskul',
                'hanya_satu' => false,
                'aktif' => true,
                'bawaan' => true,
                'urutan' => 1,
            ],
            [
                'kode' => 'organisasi',
                'nama' => 'Organisasi',
                'alur_pendaftaran' => 'seleksi',
                'kategori' => 'organisasi',
                'hanya_satu' => true,
                'aktif' => true,
                'bawaan' => true,
                'urutan' => 2,
            ],
            [
                'kode' => 'komunitas',
                'nama' => 'Komunitas',
                'alur_pendaftaran' => 'langsung',
                'kategori' => 'komunitas',
                'hanya_satu' => false,
                'aktif' => true,
                'bawaan' => true,
                'urutan' => 3,
            ],
        ];

        foreach ($tipeBawaan as $tipe) {
            $ada = DB::table('jenis_ekskuls')
                ->where('kode', $tipe['kode'])
                ->exists();

            if ($ada) {
                DB::table('jenis_ekskuls')
                    ->where('kode', $tipe['kode'])
                    ->update(array_merge($tipe, [
                        'updated_at' => $sekarang,
                    ]));
            } else {
                DB::table('jenis_ekskuls')->insert(array_merge($tipe, [
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]));
            }
        }

        /*
         * 3. Pulihkan kolom kompatibilitas pada ekstrakurikulers.
         */
        if (!Schema::hasColumn('ekstrakurikulers', 'jenis')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->enum('jenis', ['ekskul', 'osis'])
                    ->default('ekskul')
                    ->after('status');
            });
        }

        if (!Schema::hasColumn('ekstrakurikulers', 'jenis_ekskul_id')) {
            Schema::table('ekstrakurikulers', function (Blueprint $table) {
                $table->foreignId('jenis_ekskul_id')
                    ->nullable()
                    ->after('jenis')
                    ->constrained('jenis_ekskuls')
                    ->nullOnDelete();
            });
        }

        $idEkskul = DB::table('jenis_ekskuls')
            ->where('kode', 'ekskul')
            ->value('id');

        $idOrganisasi = DB::table('jenis_ekskuls')
            ->where('kode', 'organisasi')
            ->value('id');

        if (!$idEkskul || !$idOrganisasi) {
            throw new RuntimeException('Tipe ekskul bawaan gagal disiapkan.');
        }

        /*
         * 4. Hubungkan data lama.
         * OSIS menjadi organisasi. Enam ekskul lainnya tetap ekskul biasa.
         */
        DB::table('ekstrakurikulers')
            ->whereNull('jenis_ekskul_id')
            ->update([
                'jenis_ekskul_id' => $idEkskul,
            ]);

        DB::table('ekstrakurikulers')
            ->where(function ($query) {
                $query->whereRaw('LOWER(kategori) = ?', ['osis'])
                    ->orWhere(
                        'nama_ekstrakurikuler',
                        'like',
                        '%Organisasi Siswa Intra Sekolah%'
                    );
            })
            ->update([
                'jenis' => 'osis',
                'jenis_ekskul_id' => $idOrganisasi,
            ]);

        DB::table('ekstrakurikulers')
            ->whereNull('jenis')
            ->update(['jenis' => 'ekskul']);

        /*
         * 5. Jangan membuat ulang seleksi_tahaps.
         * Tabel tersebut sudah ada di database.
         *
         * Buat seleksi_hasils hanya jika belum tersedia.
         */
        if (!Schema::hasTable('seleksi_hasils')) {
            Schema::create('seleksi_hasils', function (Blueprint $table) {
                $table->id();

                $table->foreignId('casis_pendaftaran_id')
                    ->constrained('casis_pendaftarans')
                    ->cascadeOnDelete();

                $table->foreignId('seleksi_tahap_id')
                    ->constrained('seleksi_tahaps')
                    ->cascadeOnDelete();

                $table->string('hasil', 12);
                $table->text('catatan')->nullable();

                $table->foreignId('diputuskan_oleh')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->unique(
                    ['casis_pendaftaran_id', 'seleksi_tahap_id'],
                    'seleksi_hasils_pendaftaran_tahap_unique'
                );
            });
        }

        /*
         * 6. Buat tahap pertama untuk periode yang belum memilikinya.
         * Tidak menggandakan tahap yang sudah tersedia.
         */
        foreach (DB::table('osis_periodes')->get(['id']) as $periode) {
            $sudahAda = DB::table('seleksi_tahaps')
                ->where('osis_periode_id', $periode->id)
                ->where('urutan', 1)
                ->exists();

            if (!$sudahAda) {
                DB::table('seleksi_tahaps')->insert([
                    'osis_periode_id' => $periode->id,
                    'urutan' => 1,
                    'nama' => 'Seleksi',
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }
        }

        /*
         * 7. Tidak mengubah casis_reviews.
         * Kolom seleksi_tahap_id dan indeks uniknya sudah tersedia.
         *
         * Data casis_pendaftarans dan casis_reviews saat pemeriksaan
         * sebelumnya masih kosong, sehingga belum ada riwayat yang
         * perlu dipindahkan ke seleksi_hasils.
         */
    }

    public function down(): void
    {
        /*
         * Sengaja tidak menghapus tabel atau kolom.
         * Perbaikan ini mempertahankan data database.
         */
    }
};
