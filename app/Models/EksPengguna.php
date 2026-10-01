<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class EksPengguna extends Authenticatable
{
    use Notifiable;

    // Menentukan nama tabel khusus yang dipakai
    protected $table = 'eks_pengguna';

    // Kolom yang dapat diisi secara massal (mass assignable)
    protected $fillable = [
        'username',
        'nama',
        'password',
        'peran',
    ];

    // Kolom yang disembunyikan saat data di-convert ke array/JSON
    protected $hidden = [
        'password',
    ];

    // Relasi ke data Siswa jika perannya adalah 'Siswa'
    public function siswa()
    {
        return $this->hasOne(EksSiswa::class, 'pengguna_id');
    }

    // Relasi ke data Ekskul jika perannya adalah 'Pembina'
    public function eskulDibinai()
    {
        return $this->hasMany(EksEskul::class, 'eks_pembina_id');
    }
}