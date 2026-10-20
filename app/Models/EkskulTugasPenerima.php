<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulTugasPenerima extends Model
{
    protected $table = 'ekskul_tugas_penerimas';

    protected $fillable = ['tugas_id', 'user_id', 'status', 'selesai_pada'];

    protected $casts = ['selesai_pada' => 'datetime'];

    public const STATUS = [
        'belum'      => 'Belum',
        'dikerjakan' => 'Dikerjakan',
        'selesai'    => 'Selesai',
    ];

    public function tugas()
    {
        return $this->belongsTo(EkskulTugas::class, 'tugas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Belum selesai dan tenggatnya sudah lewat
    public function getTerlambatAttribute(): bool
    {
        return $this->status !== 'selesai'
            && $this->tugas
            && $this->tugas->tenggat
            && $this->tugas->tenggat->lt(today());
    }
}