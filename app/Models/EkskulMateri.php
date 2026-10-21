<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EkskulMateri extends Model
{
    protected $table = 'ekskul_materis';

    protected $fillable = [
        'ekstrakurikuler_id', 'judul', 'deskripsi', 'jenis', 'berkas', 'nama_berkas', 'mime', 'url', 'diunggah_oleh',
    ];

    // jenis => [ikon, nama]
    public const JENIS = [
        'video'   => ['🎬', 'Video'],
        'foto'    => ['🖼️', 'Foto'],
        'dokumen' => ['📄', 'Dokumen'],
        'link'    => ['🔗', 'Tautan'],
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function pengunggah()
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    // ID video kalau tautannya YouTube (untuk pemutar tertanam), selain itu null
    public function getYoutubeIdAttribute(): ?string
    {
        if ($this->jenis !== 'link' || !$this->url) {
            return null;
        }

        return preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~', $this->url, $m)
            ? $m[1]
            : null;
    }

    // Aman ditampilkan langsung di browser (video, foto, PDF). Selain itu selalu diunduh.
    public function getTampilLangsungAttribute(): bool
    {
        return in_array($this->jenis, ['video', 'foto'], true) || $this->mime === 'application/pdf';
    }

    protected static function booted(): void
    {
        // Hapus berkas fisik saat materi dihapus
        static::deleting(function (self $materi) {
            if ($materi->berkas) {
                Storage::disk('local')->delete($materi->berkas);
            }
        });
    }
}