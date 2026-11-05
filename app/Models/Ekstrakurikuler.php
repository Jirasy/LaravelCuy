<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikulers';

    protected $fillable = [
        'nama_ekstrakurikuler',
        'nama_singkatan',
        'logo',
        'wa_grup_url',
        'wa_pembina',
        'telegram_url',
        'instagram_url',
        'tiktok_url',
        'pembina',
        'deskripsi',
        'status',
        'pendaftaran_dibuka',
        'pendaftaran_tutup_pada',
        'kuota',
        'jenis',
        'jenis_ekskul_id',
        'induk_id',
        'dibuat_oleh',
        'diresmikan_pada',
        'diresmikan_oleh',
    ];

    protected $casts = [
        'pendaftaran_dibuka'     => 'boolean',
        'pendaftaran_tutup_pada' => 'datetime',
        'diresmikan_pada'        => 'datetime',
    ];

    protected static function booted(): void
    {
        // Kolom lama "jenis" (ekskul/osis) hanya penanda kompatibilitas dan dijaga sinkron dengan jenis_ekskul_id:
        // - jenis_ekskul_id diubah  -> "jenis" = osis kalau tipenya Organisasi, selain itu ekskul
        // - hanya "jenis" yang diubah / jenis_ekskul_id kosong -> jenis_ekskul_id dicari dari kodenya (osis = Organisasi)
        static::saving(function (self $ekskul) {
            if ($ekskul->jenis_ekskul_id && $ekskul->isDirty('jenis_ekskul_id')) {
                $kategori = JenisEkskul::whereKey($ekskul->jenis_ekskul_id)->value('kategori');
                $ekskul->jenis = $kategori === 'organisasi' ? 'osis' : 'ekskul';

                return;
            }

            if (! $ekskul->jenis_ekskul_id || $ekskul->isDirty('jenis')) {
                $ekskul->jenis = $ekskul->jenis === 'osis' ? 'osis' : 'ekskul';
                $ekskul->jenis_ekskul_id = JenisEkskul::where('kode', $ekskul->jenis === 'osis' ? 'organisasi' : 'ekskul')->value('id');
            }
        });

        // Induk hanya berlaku untuk organisasi, dan tidak boleh organisasi itu sendiri
        static::saving(function (self $ekskul) {
            if (! $ekskul->induk_id) {
                return;
            }

            $organisasi = JenisEkskul::whereKey($ekskul->jenis_ekskul_id)->value('kategori') === 'organisasi';

            if (! $organisasi || ($ekskul->exists && (int) $ekskul->induk_id === (int) $ekskul->id)) {
                $ekskul->induk_id = null;
            }
        });

        // Menghapus ekskul = menghapus juga berkas fisik materi, task, dan galerinya
        static::deleting(function (self $ekskul) {
            foreach (['materi', 'pr', 'galeri'] as $folder) {
                Storage::disk('local')->deleteDirectory("{$folder}/{$ekskul->id}");
            }

            // Logo disimpan di disk publik (bukan privat seperti materi/galeri), jadi dihapus terpisah
            if ($ekskul->logo) {
                Storage::disk('public')->delete($ekskul->logo);
            }
        });
    }

    public function jenisEkskul()
    {
        return $this->belongsTo(JenisEkskul::class, 'jenis_ekskul_id');
    }

    // Nama untuk katalog, kartu, badge, dan label ringkas: singkatan kalau ada, kalau tidak nama lengkap.
    // Judul halaman detail, tabel admin, laporan/dokumen, dan teks notifikasi tetap memakai nama_ekstrakurikuler (nama lengkap).
    public function getNamaTampilAttribute(): string
    {
        return $this->nama_singkatan ?: (string) $this->nama_ekstrakurikuler;
    }

    // ---- Logo ----

    // Warna latar monogram (gradasi dua warna Tailwind 600/700, semuanya cukup gelap untuk teks putih).
    // Dipilih dari nama, jadi satu ekskul selalu berwarna sama dan antar-ekskul tersebar rata.
    public const WARNA_MONOGRAM = [
        ['#4f46e5', '#7c3aed'],   // indigo -> violet
        ['#0284c7', '#0891b2'],   // sky -> cyan
        ['#059669', '#0d9488'],   // emerald -> teal
        ['#d97706', '#ea580c'],   // amber -> orange
        ['#dc2626', '#e11d48'],   // red -> rose
        ['#c026d3', '#db2777'],   // fuchsia -> pink
        ['#4338ca', '#1d4ed8'],   // indigo -> blue
        ['#65a30d', '#16a34a'],   // lime -> green
    ];

    // Alamat logo yang diunggah, atau null kalau belum ada (tampilan lalu memakai monogram)
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }

    // Teks monogram cadangan: singkatan kalau ada ("PMR"), kalau tidak huruf pertama dua kata pertama ("Palang Merah" -> "PM").
    // Maksimal 3 huruf supaya tetap terbaca di lingkaran kecil.
    public function getMonogramAttribute(): string
    {
        $singkatan = preg_replace('/[^\p{L}\p{N}]/u', '', (string) $this->nama_singkatan);

        if ($singkatan !== '') {
            return mb_strtoupper(mb_substr($singkatan, 0, 3));
        }

        $kata = preg_split('/[^\p{L}\p{N}]+/u', trim((string) $this->nama_ekstrakurikuler), -1, PREG_SPLIT_NO_EMPTY);
        $huruf = collect($kata)->take(2)->map(fn ($k) => mb_substr($k, 0, 1))->implode('');

        return mb_strtoupper($huruf !== '' ? $huruf : '?');
    }

    // Pasangan warna gradasi untuk monogram, tetap untuk nama yang sama
    public function getWarnaMonogramAttribute(): array
    {
        $kunci = mb_strtolower((string) $this->nama_ekstrakurikuler);

        return self::WARNA_MONOGRAM[crc32($kunci) % count(self::WARNA_MONOGRAM)];
    }

    // Singkatan kosong/spasi saja disimpan sebagai null (kolomnya unik; null boleh banyak, string kosong tidak)
    public function setNamaSingkatanAttribute($nilai): void
    {
        $nilai = is_string($nilai) ? trim($nilai) : $nilai;
        $this->attributes['nama_singkatan'] = $nilai === '' ? null : $nilai;
    }

    // Ukuran ekskul menurut jumlah anggota aktif, untuk saringan katalog: kunci => [nama, rentang, batas bawah, batas atas]
    // Ubah angkanya di sini kalau batasnya perlu disesuaikan.
    public const UKURAN_ANGGOTA = [
        'kecil'  => ['Kecil',  '< 15',  0,  14],
        'sedang' => ['Sedang', '15–29', 15, 29],
        'besar'  => ['Besar',  '30+',   30, null],
    ];

    public static function ukuranAnggota(int $jumlah): string
    {
        foreach (self::UKURAN_ANGGOTA as $kunci => [$nama, $rentang, $bawah, $atas]) {
            if ($jumlah >= $bawah && ($atas === null || $jumlah <= $atas)) {
                return $kunci;
            }
        }

        return 'kecil';
    }

    // ---- Kategori (dipakai form ekskul dan form komunitas) ----

    public static function aturanKategori(): array
    {
        return [
            'kategori_form' => ['nullable', 'boolean'],
            'kategori_ids' => ['nullable', 'array'],
            'kategori_ids.*' => ['integer', Rule::exists('kategori_ekskuls', 'id')],
        ];
    }

    public static function pesanKategori(): array
    {
        return ['kategori_ids.*.exists' => 'Ada kategori yang tidak dikenal.'];
    }

    // Form selalu mengirim penanda kategori_form; tanpa penanda (permintaan lain) kategori dibiarkan apa adanya.
    // Kategori nonaktif hanya boleh dipertahankan kalau memang sudah melekat di ekskul ini.
    public function sinkronKategori(array $data): void
    {
        if (empty($data['kategori_form'])) {
            return;
        }

        $dipilih = collect($data['kategori_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $terpasang = $this->kategoris()->pluck('kategori_ekskuls.id');

        $boleh = KategoriEkskul::whereIn('id', $dipilih)
            ->where(fn ($q) => $q->where('aktif', true)->orWhereIn('id', $terpasang))
            ->pluck('id');

        $this->kategoris()->sync($boleh->all());
    }

    // ---- Kontak & sosial media (khusus anggota aktif) ----

    // kolom => [nama ikon (lihat App\Support\Ikon), nama, domain yang diizinkan]. Alamat harus https dan benar-benar milik platformnya.
    public const KONTAK_URL = [
        'wa_grup_url'   => ['whatsapp', 'Grup WhatsApp', ['whatsapp.com']],
        'telegram_url'  => ['telegram', 'Telegram',      ['t.me', 'telegram.me']],
        'instagram_url' => ['instagram', 'Instagram',     ['instagram.com']],
        'tiktok_url'    => ['musik', 'TikTok',        ['tiktok.com']],
    ];

    // Nomor WhatsApp pembina disimpan seperti users.no_hp: 62812xxxx (tanpa +, spasi, atau 0 di depan)
    public function setWaPembinaAttribute($nilai): void
    {
        $digit = ltrim(preg_replace('/[\s\-\.\(\)]/', '', (string) $nilai), '+');

        if ($digit !== '' && str_starts_with($digit, '0')) {
            $digit = '62' . substr($digit, 1);
        }

        $this->attributes['wa_pembina'] = $digit === '' ? null : $digit;
    }

    // Nomor untuk dibaca manusia / diisi ulang di form, contoh: 081234567890
    public function getWaPembinaTampilanAttribute(): ?string
    {
        if (! $this->wa_pembina) {
            return null;
        }

        return str_starts_with($this->wa_pembina, '62') ? '0' . substr($this->wa_pembina, 2) : '+' . $this->wa_pembina;
    }

    // Aturan validasi kolom kontak (dipakai form ekskul)
    public static function aturanKontak(): array
    {
        $aturan = [];

        foreach (self::KONTAK_URL as $kolom => [$ikon, $nama, $domain]) {
            $aturan[$kolom] = ['nullable', 'string', 'max:500', 'url', 'starts_with:https://', function ($atribut, $nilai, $gagal) use ($domain, $nama) {
                $host = strtolower((string) parse_url((string) $nilai, PHP_URL_HOST));

                foreach ($domain as $d) {
                    if ($host === $d || str_ends_with($host, '.' . $d)) {
                        return;
                    }
                }

                $gagal("Alamat tidak cocok dengan platform {$nama}.");
            }];
        }

        $aturan['wa_pembina'] = ['nullable', 'string', 'max:25', function ($atribut, $nilai, $gagal) {
            // Sama dengan aturan nomor HP di profil pengguna: nomor seluler Indonesia
            if (! preg_match('/^(\+?62|0)8[0-9]{7,12}$/', preg_replace('/[\s\-\.\(\)]/', '', (string) $nilai))) {
                $gagal('Nomor WhatsApp pembina tidak valid. Contoh: 081234567890 atau +6281234567890.');
            }
        }];

        return $aturan;
    }

    public static function pesanKontak(): array
    {
        $pesan = [];

        foreach (self::KONTAK_URL as $kolom => [$ikon, $nama]) {
            $pesan["{$kolom}.url"]         = "Alamat {$nama} tidak valid.";
            $pesan["{$kolom}.starts_with"] = "Tautan {$nama} harus diawali https://";
            $pesan["{$kolom}.max"]         = "Alamat {$nama} maksimal 500 karakter.";
        }

        return $pesan;
    }

    // Daftar kontak yang terisi, urut: Grup WhatsApp, chat pembina, Telegram, Instagram, TikTok.
    // PENTING: hanya dipanggil controller untuk anggota aktif/pengelola; jangan dipakai di tampilan umum.
    public function daftarKontak(): array
    {
        $daftar = [];

        if ($this->wa_grup_url) {
            $daftar[] = ['ikon' => self::KONTAK_URL['wa_grup_url'][0], 'nama' => 'Grup WhatsApp', 'url' => $this->wa_grup_url, 'keterangan' => null];
        }

        if ($this->wa_pembina) {
            $daftar[] = ['ikon' => 'telepon', 'nama' => 'Chat Pembina', 'url' => 'https://wa.me/' . $this->wa_pembina, 'keterangan' => $this->wa_pembina_tampilan];
        }

        foreach (['telegram_url', 'instagram_url', 'tiktok_url'] as $kolom) {
            if ($this->{$kolom}) {
                $daftar[] = ['ikon' => self::KONTAK_URL[$kolom][0], 'nama' => self::KONTAK_URL[$kolom][1], 'url' => $this->{$kolom}, 'keterangan' => null];
            }
        }

        return $daftar;
    }

    // Aturan validasi singkatan: opsional, maks 30 karakter, unik tanpa membedakan huruf besar/kecil.
    // Dipakai form ekskul dan form komunitas supaya aturannya satu.
    public static function aturanSingkatan(?int $abaikanId = null): array
    {
        return ['nullable', 'string', 'max:30', function ($atribut, $nilai, $gagal) use ($abaikanId) {
            $kembar = static::whereRaw('LOWER(nama_singkatan) = ?', [mb_strtolower((string) $nilai)])
                ->when($abaikanId, fn ($q) => $q->where('id', '!=', $abaikanId))
                ->exists();

            if ($kembar) {
                $gagal("Singkatan {$nilai} sudah dipakai ekskul lain.");
            }
        }];
    }

    // Kategori/label (boleh lebih dari satu), mis. Silat = Bela Diri + Seni & Olahraga. Beda dengan tipe di atas.
    public function kategoris()
    {
        return $this->belongsToMany(KategoriEkskul::class, 'ekstrakurikuler_kategori', 'ekstrakurikuler_id', 'kategori_ekskul_id');
    }

    // Ekskul yang pendaftarannya lewat seleksi bertahap: semua ekskul bertipe Ekskul Seleksi atau Organisasi.
    // Satu-satunya tempat aturan ini ditulis: kode lain cukup memanggil memakaiCasis() / alurCasis().
    // (Nama "Casis" dipertahankan dari versi OSIS-saja; sekarang berlaku untuk semua tipe berseleksi.)
    public function memakaiCasis(): bool
    {
        return (bool) $this->jenisEkskul?->memakaiCasis();
    }

    public function scopeAlurCasis($query)
    {
        return $query->whereHas('jenisEkskul', fn ($q) => $q->where('alur_pendaftaran', 'seleksi'));
    }

    public function scopeOrganisasi($query)
    {
        return $query->whereHas('jenisEkskul', fn ($q) => $q->where('kategori', 'organisasi'));
    }

    public function isOrganisasi(): bool
    {
        return (bool) $this->jenisEkskul?->isOrganisasi();
    }

    // Komunitas = ekskul baru lahir yang belum diresmikan admin
    public function isKomunitas(): bool
    {
        return (bool) $this->jenisEkskul?->isKomunitas();
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // Organisasi di atasnya (mis. OSIS berada di bawah MPK) dan organisasi di bawahnya
    public function induk()
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    public function bawahan()
    {
        return $this->hasMany(self::class, 'induk_id');
    }

    // Id semua organisasi di atasnya: induk, induk dari induk, dst (aman dari lingkaran)
    public function idLeluhur(): array
    {
        $hasil = [];
        $kini  = (int) $this->induk_id;

        while ($kini && $kini !== (int) $this->id && ! in_array($kini, $hasil, true)) {
            $hasil[] = $kini;
            $kini = (int) static::whereKey($kini)->value('induk_id');
        }

        return $hasil;
    }

    // Apakah menjadikan $indukId sebagai induk dari $ekskulId membuat lingkaran (A di bawah B, B di bawah A, dst)?
    public static function menimbulkanSiklus(int $ekskulId, int $indukId): bool
    {
        $dilihat = [];
        $kini    = $indukId;

        while ($kini) {
            if ($kini === $ekskulId || isset($dilihat[$kini])) {
                return true;
            }

            $dilihat[$kini] = true;
            $kini = (int) static::whereKey($kini)->value('induk_id');
        }

        return false;
    }

    // Sebutan untuk orang yang diterima: "pengurus" di organisasi, "anggota" di ekskul biasa/seleksi
    public function getSebutanAnggotaAttribute(): string
    {
        return $this->isOrganisasi() ? 'pengurus' : 'anggota';
    }

    public function getSebutanKeanggotaanAttribute(): string
    {
        return $this->isOrganisasi() ? 'kepengurusan' : 'keanggotaan';
    }

    // Sebutan pendaftar seleksi: "CaSis" untuk OSIS, "Calon Pengurus" untuk organisasi lain, "Calon Anggota" untuk ekskul seleksi
    public function getLabelCalonAttribute(): string
    {
        if (stripos((string) $this->nama_ekstrakurikuler, 'osis') !== false) {
            return 'CaSis';
        }

        return $this->isOrganisasi() ? 'Calon Pengurus' : 'Calon Anggota';
    }

    public function pendaftaran()
    {
        return $this->hasMany(PendaftaranEkskul::class);
    }

    public function haris()
    {
        return $this->hasMany(EkskulHari::class);
    }

    public function pengumuman()
    {
        return $this->hasMany(EkskulPengumuman::class);
    }

    public function prestasi()
    {
        return $this->hasMany(EkskulPrestasi::class);
    }

    public function events()
    {
        return $this->hasMany(EkskulEvent::class);
    }

    public function pertemuans()
    {
        return $this->hasMany(EkskulPertemuan::class);
    }

    public function materis()
    {
        return $this->hasMany(EkskulMateri::class)->latest();
    }

    public function prs()
    {
        return $this->hasMany(EkskulPr::class)->latest();
    }

    public function periodeOsis()
    {
        return $this->hasMany(OsisPeriode::class);
    }

    public function fotos()
    {
        return $this->hasMany(EkskulFoto::class)->latest();
    }

    public function tautans()
    {
        return $this->hasMany(EkskulTautan::class)->orderBy('urutan')->orderBy('id');
    }

    // ---- Buka/tutup pendaftaran ----

    // Hasil alasanPendaftaranTutup() yang sudah dihitung untuk daftar katalog. Diisi hanya lewat
    // ingatStatusPendaftaran(); di tempat lain status selalu dihitung segar.
    private ?array $statusPendaftaranDiingat = null;

    // Hitung sekali lalu pakai ulang (saringan, hitungan pill, dan kartu katalog membaca status yang sama)
    public function ingatStatusPendaftaran(): static
    {
        $this->statusPendaftaranDiingat = null;
        $this->statusPendaftaranDiingat = [$this->alasanPendaftaranTutup()];

        return $this;
    }

    // null = pendaftaran terbuka; selain itu berisi alasan: nonaktif | ditutup | kuota
    public function alasanPendaftaranTutup(): ?string
    {
        if ($this->statusPendaftaranDiingat !== null) {
            return $this->statusPendaftaranDiingat[0];
        }

        if ($this->status !== 'aktif') {
            return 'nonaktif';
        }

        if (!$this->pendaftaran_dibuka) {
            return 'ditutup';
        }

        // Ekskul berseleksi: pendaftaran = pendaftaran seleksi, hanya terbuka saat ada periode rekrutmen yang dibuka
        if ($this->memakaiCasis() && !OsisPeriode::terbuka()->where('ekstrakurikuler_id', $this->id)->exists()) {
            return 'ditutup';
        }

        if ($this->pendaftaran_tutup_pada && $this->pendaftaran_tutup_pada->isPast()) {
            return 'ditutup';
        }

        if ($this->kuota !== null && $this->jumlahAnggotaAktif() >= $this->kuota) {
            return 'kuota';
        }

        return null;
    }

    public function getPendaftaranTerbukaAttribute(): bool
    {
        return $this->alasanPendaftaranTutup() === null;
    }

    // Teks tombol daftar di sisi siswa
    public function getLabelPendaftaranAttribute(): string
    {
        return match ($this->alasanPendaftaranTutup()) {
            null       => 'Daftar Sekarang',
            'nonaktif' => 'Ekskul Nonaktif',
            'kuota'    => 'Kuota Penuh',
            default    => 'Pendaftaran Ditutup',
        };
    }

    // Pakai hasil withCount('... as anggota_count') kalau tersedia, supaya tidak query berulang
    public function jumlahAnggotaAktif(): int
    {
        return $this->anggota_count ?? $this->pendaftaran()->anggotaAktif()->count();
    }

    // Struktur jabatan milik ekskul ini, urut sesuai pengaturan
    public function jabatans()
    {
        return $this->hasMany(EkskulJabatan::class)->orderBy('urutan')->orderBy('nama')->orderBy('varian');
    }

    // Semua pembina (termasuk yang sudah selesai), lengkap dengan tanggal mulai/selesai
    public function pembinas()
    {
        return $this->belongsToMany(User::class, 'ekskul_pembinas', 'ekstrakurikuler_id', 'user_id')
            ->withPivot('mulai', 'selesai')
            ->withTimestamps();
    }

    // Pembina yang masih bertugas
    public function pembinaAktif()
    {
        return $this->pembinas()->wherePivotNull('selesai');
    }

    // Nama pembina untuk ditampilkan; cadangannya teks lama kalau belum ada pembina terhubung
    public function getPembinaTampilAttribute(): string
    {
        $nama = $this->pembinaAktif->pluck('name');

        return $nama->isNotEmpty() ? $nama->implode(', ') : (string) $this->pembina;
    }

    // Jadwal per hari, urut Senin -> Minggu
    public function getJadwalAttribute()
    {
        $urutan = array_map(fn ($c) => $c->value, Hari::cases());

        return $this->haris
            ->sortBy(fn ($h) => array_search($h->hari, $urutan))
            ->values();
    }

    // Contoh hasil: "Rabu, Jumat"
    public function getHariLabelAttribute(): string
    {
        return $this->jadwal->map(fn ($h) => ucfirst($h->hari))->implode(', ');
    }

    /**
     * Samakan jadwal di database dengan yang diisi di form.
     * $jadwal = [['hari' => 'rabu', 'jam_mulai' => '15:30', 'jam_selesai' => '17:00'], ...]
     */
    public function syncJadwal(array $jadwal): void
    {
        $this->haris()->whereNotIn('hari', array_column($jadwal, 'hari'))->delete();

        foreach ($jadwal as $j) {
            $this->haris()->updateOrCreate(
                ['hari' => $j['hari']],
                ['jam_mulai' => $j['jam_mulai'], 'jam_selesai' => $j['jam_selesai']]
            );
        }
    }
}