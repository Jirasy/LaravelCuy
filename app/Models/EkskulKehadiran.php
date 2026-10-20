<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulKehadiran extends Model
{
    protected $table = 'ekskul_kehadirans';

    protected $fillable = ['pertemuan_id', 'pendaftaran_id', 'status', 'catatan'];

    // status => [nama, kode singkat]
    public const STATUS = [
        'hadir' => ['Hadir', 'H'],
        'izin'  => ['Izin', 'I'],
        'sakit' => ['Sakit', 'S'],
        'alpa'  => ['Alpa', 'A'],
    ];

    public function pertemuan()
    {
        return $this->belongsTo(EkskulPertemuan::class, 'pertemuan_id');
    }

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranEkskul::class, 'pendaftaran_id');
    }

    public function getKodeAttribute(): string
    {
        return self::STATUS[$this->status][1] ?? '-';
    }
}