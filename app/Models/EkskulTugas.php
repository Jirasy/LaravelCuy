<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulTugas extends Model
{
    protected $table = 'ekskul_tugas';

    protected $fillable = ['ekstrakurikuler_id', 'judul', 'deskripsi', 'tenggat', 'dibuat_oleh'];

    protected $casts = ['tenggat' => 'date'];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penerimas()
    {
        return $this->hasMany(EkskulTugasPenerima::class, 'tugas_id');
    }
}