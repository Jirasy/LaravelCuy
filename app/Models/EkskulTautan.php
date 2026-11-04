<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulTautan extends Model
{
    protected $table = 'ekskul_tautans';

    protected $fillable = ['ekstrakurikuler_id', 'platform', 'label', 'url', 'urutan', 'dibuat_oleh'];

    // platform => [nama ikon (lihat App\Support\Ikon), nama, domain yang diizinkan (null = bebas, asal https)]
    public const PLATFORM = [
        'youtube'   => ['youtube', 'YouTube',   ['youtube.com', 'youtu.be']],
        'instagram' => ['instagram', 'Instagram', ['instagram.com']],
        'tiktok'    => ['musik', 'TikTok',    ['tiktok.com']],
        'whatsapp'  => ['whatsapp', 'WhatsApp',  ['wa.me', 'whatsapp.com']],
        'website'   => ['globe', 'Website',   null],
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function getIkonAttribute(): string
    {
        return self::PLATFORM[$this->platform][0] ?? 'tautan';
    }
}