<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulJabatan extends Model
{
    protected $table = 'ekskul_jabatans';

    protected $fillable = [
        'ekstrakurikuler_id', 'nama', 'varian', 'deskripsi', 'tugas', 'tanggung_jawab', 'urutan', 'kuota',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function penetapans()
    {
        return $this->hasMany(EkskulAnggotaJabatan::class, 'jabatan_id');
    }

    // Anggota (pendaftaran) yang saat ini memegang jabatan ini
    public function pemegangAktif()
    {
        return $this->belongsToMany(PendaftaranEkskul::class, 'ekskul_anggota_jabatans', 'jabatan_id', 'pendaftaran_id')
            ->withPivot('id', 'periode_mulai', 'periode_selesai')
            ->wherePivotNull('periode_selesai');
    }

    // Contoh: "Sekretaris 1"
    public function getLabelLengkapAttribute(): string
    {
        return trim($this->nama . ' ' . $this->varian);
    }

    public function getKuotaPenuhAttribute(): bool
    {
        return $this->kuota !== null && $this->pemegangAktif->count() >= $this->kuota;
    }
}