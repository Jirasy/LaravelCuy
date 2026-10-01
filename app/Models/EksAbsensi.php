<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EksAbsensi extends Model
{
    protected $table = 'eks_absensi';

    protected $fillable = [
        'eskul_id',
        'siswa_id',
        'tanggal',
        'status_kehadiran',
    ];

    // Relasi ke ekskul yang mengadakan absensi
    public function eskul()
    {
        return $this->belongsTo(EksEskul::class, 'eskul_id');
    }

    // Relasi ke siswa yang diabsen
    public function siswa()
    {
        return $this->belongsTo(EksSiswa::class, 'siswa_id');
    }
}