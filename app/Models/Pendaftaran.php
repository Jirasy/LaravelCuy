<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    protected $fillable = [
        'ekstrakurikuler_id',
        'nama_siswa',
        'kelas',
        'pembina',
        'admin',
        'alasan',
        'status',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }
}
