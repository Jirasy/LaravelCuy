<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranEkskul extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_ekskuls';

    protected $fillable = [
        'user_id',
        'ekstrakurikuler_id',
        'nama_pendaftar',
        'peran',
        'angkatan_ekskul',
        'tahun_mulai',
        'tahun_selesai',
        'kelas',
        'nisn',
        'alasan_mendaftar',
        'status',
        'status_anggota',
        'akses_materi',
        'tanggal_keluar',
        'alasan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    // Semua penetapan jabatan (termasuk yang sudah selesai) = riwayat jabatan
    public function penetapanJabatans()
    {
        return $this->hasMany(EkskulAnggotaJabatan::class, 'pendaftaran_id');
    }

    // Jabatan yang sedang dipegang (boleh lebih dari satu)
    public function jabatanAktif()
    {
        return $this->belongsToMany(EkskulJabatan::class, 'ekskul_anggota_jabatans', 'pendaftaran_id', 'jabatan_id')
            ->withPivot('id', 'periode_mulai', 'periode_selesai')
            ->wherePivotNull('periode_selesai');
    }

    // Contoh: "Sekretaris 1, Koordinator Humas" atau "Anggota" kalau tidak punya jabatan
    public function getJabatanLabelAttribute(): string
    {
        $daftar = $this->jabatanAktif->map(fn ($j) => $j->label_lengkap);

        return $daftar->isNotEmpty() ? $daftar->implode(', ') : 'Anggota';
    }

    protected $casts = [
        'tanggal_keluar' => 'date',
        'akses_materi'   => 'boolean',
    ];

    // Anggota yang disetujui DAN masih aktif (belum keluar / dikeluarkan)
    public function scopeAnggotaAktif($query)
    {
        return $query->where('status', 'diterima')->where('status_anggota', 'aktif');
    }

    // ---- Anti-double registration ----
    // Siswa TIDAK boleh mendaftar lagi ke ekskul yang sama kalau masih ada pendaftaran yang berlaku:
    // menunggu, anggota aktif, ditolak, atau dikeluarkan. Satu-satunya pengecualian: status_anggota
    // "keluar" (mengundurkan diri yang sudah disetujui). Dikeluarkan hanya bisa didaftarkan ulang oleh admin/pembina.
    public static function penghalangDaftar(int $userId, int $ekskulId): ?self
    {
        return static::where('user_id', $userId)
            ->where('ekstrakurikuler_id', $ekskulId)
            ->where('status_anggota', '!=', 'keluar')
            ->latest('id')
            ->first();
    }

    // Id semua ekskul yang sedang terhalang untuk user ini (satu query, dipakai menyaring form pendaftaran)
    public static function idEkskulTerhalang(int $userId): \Illuminate\Support\Collection
    {
        return static::where('user_id', $userId)
            ->where('status_anggota', '!=', 'keluar')
            ->pluck('ekstrakurikuler_id')
            ->unique()
            ->values();
    }

    // Alasan yang ditampilkan ke siswa saat pendaftarannya ditolak sistem karena double registration
    public function getPesanPenghalangAttribute(): string
    {
        if ($this->status_anggota === 'dikeluarkan') {
            return 'Kamu pernah dikeluarkan dari ekskul ini, jadi tidak bisa mendaftar lagi.';
        }

        return match ($this->status) {
            'diterima' => 'Kamu sudah menjadi anggota aktif ekskul ini.',
            'ditolak'  => 'Pendaftaranmu sebelumnya tidak disetujui. Hubungi pembina kalau ingin diproses ulang.',
            default    => 'Pendaftaranmu masih menunggu persetujuan.',
        };
    }

    public function kehadirans()
    {
        return $this->hasMany(EkskulKehadiran::class, 'pendaftaran_id');
    }

    public function nilais()
    {
        return $this->hasMany(EkskulNilai::class, 'pendaftaran_id');
    }

    public function prestasis()
    {
        return $this->belongsToMany(EkskulPrestasi::class, 'ekskul_prestasi_anggota', 'pendaftaran_id', 'prestasi_id');
    }

    // Contoh: "2024 – sekarang" atau "2023 – 2025"
    public function getPeriodeAttribute(): string
    {
        if (!$this->tahun_mulai) {
            return '-';
        }
        return $this->tahun_mulai . ' – ' . ($this->tahun_selesai ?? 'sekarang');
    }

    // Label tampilan: diterima = Disetujui, ditolak = Tidak Disetujui
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'diterima' => 'Disetujui',
            'ditolak'  => 'Tidak Disetujui',
            default    => 'Menunggu',
        };
    }
}