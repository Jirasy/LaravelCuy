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
        'jabatan',
        'angkatan_ekskul',
        'tahun_mulai',
        'tahun_selesai',
        'kelas',
        'nisn',
        'alasan_mendaftar',
        'status',
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

    // Jabatan di ekskul ini (bukan role akun)
    public const JABATAN = [
        'anggota'            => 'Anggota',
        'ketua'              => 'Ketua',
        'wakil_ketua'        => 'Wakil Ketua',
        'sekretaris'         => 'Sekretaris',
        'bendahara'          => 'Bendahara',
        'koordinator'        => 'Koordinator',
        'pelatih'            => 'Pelatih',
        'pembina_pendamping' => 'Pembina Pendamping',
    ];

    public function prestasis()
    {
        return $this->belongsToMany(EkskulPrestasi::class, 'ekskul_prestasi_anggota', 'pendaftaran_id', 'prestasi_id');
    }

    public function getJabatanLabelAttribute(): string
    {
        return self::JABATAN[$this->jabatan] ?? 'Anggota';
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