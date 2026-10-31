<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasisPendaftaran extends Model
{
    protected $table = 'casis_pendaftarans';

    protected $fillable = [
        'osis_periode_id', 'user_id', 'motivasi', 'pengalaman', 'visi_misi', 'status',
        'didaftarkan_oleh', 'diputuskan_oleh', 'catatan_keputusan', 'diputuskan_pada',
    ];

    protected $casts = ['diputuskan_pada' => 'datetime'];

    public const STATUS = [
        'draft'             => 'Draft',
        'diajukan'          => 'Diajukan',
        'diterima'          => 'Diterima',
        'ditolak'           => 'Tidak diterima',
        'mengundurkan_diri' => 'Mengundurkan diri',
    ];

    public function periode()
    {
        return $this->belongsTo(OsisPeriode::class, 'osis_periode_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(CasisReview::class, 'casis_pendaftaran_id');
    }

    public function hasils()
    {
        return $this->hasMany(SeleksiHasil::class, 'casis_pendaftaran_id');
    }

    // Tahap yang sedang dijalani: tahap pertama (berurutan) yang belum ada hasilnya. Null kalau sudah tidak dalam proses.
    public function tahapSekarang(): ?SeleksiTahap
    {
        if ($this->status !== 'diajukan') {
            return null;
        }

        $sudah = $this->hasils()->pluck('seleksi_tahap_id')->all();

        return $this->periode->tahapSemua()->first(fn ($t) => ! in_array($t->id, $sudah, true));
    }

    public function pemutus()
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }

    // Pendaftar masih boleh mengubah/mengajukan: berstatus draft atau diajukan DAN periode masih rekrutmen
    public function getBisaDiubahAttribute(): bool
    {
        return in_array($this->status, ['draft', 'diajukan'], true)
            && $this->periode
            && $this->periode->status === 'rekrutmen';
    }
}