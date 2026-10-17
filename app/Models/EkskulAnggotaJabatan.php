<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulAnggotaJabatan extends Model
{
    protected $table = 'ekskul_anggota_jabatans';

    protected $fillable = ['pendaftaran_id', 'jabatan_id', 'periode_mulai', 'periode_selesai', 'ditetapkan_oleh'];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranEkskul::class, 'pendaftaran_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(EkskulJabatan::class, 'jabatan_id');
    }

    // Contoh: "2024 – sekarang"
    public function getPeriodeAttribute(): string
    {
        return $this->periode_mulai . ' – ' . ($this->periode_selesai ?? 'sekarang');
    }
}