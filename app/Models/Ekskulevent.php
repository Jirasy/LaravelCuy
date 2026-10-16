<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulEvent extends Model
{
    protected $table = 'ekskul_events';

    protected $fillable = ['ekstrakurikuler_id', 'judul', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'deskripsi'];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public const JENIS = [
        'lomba'   => 'Lomba',
        'pentas'  => 'Pentas / Penampilan',
        'latihan' => 'Latihan Gabungan',
        'rapat'   => 'Rapat',
        'lainnya' => 'Lainnya',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst($this->jenis);
    }

    // Event yang belum lewat (termasuk yang sedang berlangsung)
    public function getAkanDatangAttribute(): bool
    {
        $akhir = $this->tanggal_selesai ?? $this->tanggal_mulai;
        return $akhir->greaterThanOrEqualTo(now()->startOfDay());
    }
}