<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EkskulPr extends Model
{
    protected $table = 'ekskul_prs';

    protected $fillable = ['ekstrakurikuler_id', 'judul', 'deskripsi', 'tenggat', 'nilai_maks', 'dibuat_oleh'];

    protected $casts = ['tenggat' => 'date'];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pengumpulans()
    {
        return $this->hasMany(EkskulPrPengumpulan::class, 'pr_id');
    }

    protected static function booted(): void
    {
        // Hapus berkas pengumpulan saat task dihapus (baris pengumpulannya terhapus lewat cascade)
        static::deleting(function (self $pr) {
            $pr->pengumpulans()->whereNotNull('berkas')->pluck('berkas')->each(
                fn ($path) => Storage::disk('local')->delete($path)
            );
        });
    }
}