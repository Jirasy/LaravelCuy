<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikulers';

    protected $fillable = [
        'nama_ekstrakurikuler',
        'pembina',
        'deskripsi',
        'status',
        'pendaftaran_dibuka',
        'pendaftaran_tutup_pada',
        'kuota',
        'jenis',
        'jenis_ekskul_id',
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

        // Menghapus ekskul = menghapus juga berkas fisik materi, task, dan galerinya
        static::deleting(function (self $ekskul) {
            foreach (['materi', 'pr', 'galeri'] as $folder) {
                Storage::disk('local')->deleteDirectory("{$folder}/{$ekskul->id}");
            }
        });
    }

    public function jenisEkskul()
    {
        return $this->belongsTo(JenisEkskul::class, 'jenis_ekskul_id');
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

    // null = pendaftaran terbuka; selain itu berisi alasan: nonaktif | ditutup | kuota
    public function alasanPendaftaranTutup(): ?string
    {
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