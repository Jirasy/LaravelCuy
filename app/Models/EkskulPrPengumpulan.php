<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulPrPengumpulan extends Model
{
    protected $table = 'ekskul_pr_pengumpulans';

    protected $fillable = [
        'pr_id', 'pendaftaran_id', 'jawaban', 'berkas', 'nama_berkas', 'mime',
        'dikumpulkan_pada', 'nilai', 'komentar', 'dinilai_oleh', 'dinilai_pada',
    ];

    protected $casts = [
        'dikumpulkan_pada' => 'datetime',
        'dinilai_pada'     => 'datetime',
    ];

    public function pr()
    {
        return $this->belongsTo(EkskulPr::class, 'pr_id');
    }

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranEkskul::class, 'pendaftaran_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    public function getSudahDinilaiAttribute(): bool
    {
        return $this->nilai !== null;
    }

    // Dikumpulkan setelah akhir hari tenggat
    public function getTerlambatAttribute(): bool
    {
        $pr = $this->pr;

        return $pr && $pr->tenggat && $this->dikumpulkan_pada->gt($pr->tenggat->copy()->endOfDay());
    }
}