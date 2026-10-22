<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EkskulFoto extends Model
{
    protected $table = 'ekskul_fotos';

    protected $fillable = [
        'ekstrakurikuler_id', 'event_id', 'diunggah_oleh', 'berkas', 'thumb', 'nama_berkas', 'mime',
        'caption', 'kategori', 'diambil_pada', 'disetujui',
    ];

    protected $casts = [
        'diambil_pada' => 'date',
        'disetujui'    => 'boolean',
    ];

    public const KATEGORI = [
        'latihan' => 'Latihan',
        'tanding' => 'Tanding',
        'lomba'   => 'Lomba',
        'lainnya' => 'Lainnya',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function pengunggah()
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function event()
    {
        return $this->belongsTo(EkskulEvent::class, 'event_id');
    }

    protected static function booted(): void
    {
        // Hapus foto asli dan thumbnail dari penyimpanan saat baris dihapus
        static::deleting(function (self $foto) {
            Storage::disk('local')->delete(array_filter([$foto->berkas, $foto->thumb]));
        });
    }
}