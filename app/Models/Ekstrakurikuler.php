<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikulers';

    protected $fillable = [
        'nama_ekstrakurikuler',
        'pembina',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'deskripsi',
        'status',
    ];

    public function pendaftaran()
    {
        return $this->hasMany(PendaftaranEkskul::class);
    }
}