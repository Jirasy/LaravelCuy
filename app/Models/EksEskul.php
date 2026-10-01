<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EksEskul extends Model
{
    protected $table = 'eks_eskul';

    protected $fillable = [
        'eks_name',
        'eks_description',
        'eks_pembina_id',
        'eks_jadwal',
    ];

    // Relasi ke Pembina (eks_pengguna)
    public function pembina()
    {
        return $this->belongsTo(EksPengguna::class, 'eks_pembina_id');
    }

    // Relasi ke pendaftaran siswa
    public function pendaftaran()
    {
        return $this->hasMany(EksPendaftaran::class, 'eskul_id');
    }

    // Relasi ke catatan absensi ekskul
    public function absensi()
    {
        return $this->hasMany(EksAbsensi::class, 'eskul_id');
    }
}