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
    ];

    // Anggota yang disetujui DAN masih aktif (belum keluar / dikeluarkan)
    public function scopeAnggotaAktif($query)
    {
        return $query->where('status', 'diterima')->where('status_anggota', 'aktif');
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