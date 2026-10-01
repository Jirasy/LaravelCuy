<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EksSiswa extends Model
{
    protected $table = 'eks_siswa';

    protected $fillable = [
        'pengguna_id',
        'nis',
        'kelas',
        'jurusan',
        'no_tlp',
    ];

    // Relasi balik ke akun pengguna (eks_pengguna)
    public function pengguna()
    {
        return $this->belongsTo(EksPengguna::class, 'pengguna_id');
    }

    // Relasi ke riwayat pendaftaran ekskul
    public function pendaftaran()
    {
        return $this->hasMany(EksPendaftaran::class, 'siswa_id');
    }

    // Relasi ke catatan absensi
    public function absensi()
    {
        return $this->hasMany(EksAbsensi::class, 'siswa_id');
    }
}