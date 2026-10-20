<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulTautan extends Model
{
    protected $table = 'ekskul_tautans';

    protected $fillable = ['ekstrakurikuler_id', 'platform', 'label', 'url', 'urutan', 'dibuat_oleh'];

    // platform => [ikon, nama, domain yang diizinkan (null = bebas, asal https)]
    public const PLATFORM = [
        'youtube'   => ['▶️', 'YouTube',   ['youtube.com', 'youtu.be']],
        'instagram' => ['📸', 'Instagram', ['instagram.com']],
        'tiktok'    => ['🎵', 'TikTok',    ['tiktok.com']],
        'whatsapp'  => ['💬', 'WhatsApp',  ['wa.me', 'whatsapp.com']],
        'website'   => ['🔗', 'Website',   null],
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function getIkonAttribute(): string
    {
        return self::PLATFORM[$this->platform][0] ?? '🔗';
    }
}