<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EksPendaftaran extends Model
{
    protected $table = 'eks_pendaftaran';

    protected $fillable = [
        'siswa_id',
        'eskul_id',
        'tanggal_daftar',
        'status_pendaftaran',
    ];

    // Relasi ke data siswa yang mendaftar
    public function siswa()
    {
        return $this->belongsTo(EksSiswa::class, 'siswa_id');
    }

    // Relasi ke ekskul yang dipilih
    public function eskul()
    {
        return $this->belongsTo(EksEskul::class, 'eskul_id');
    }
}