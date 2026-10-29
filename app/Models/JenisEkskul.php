<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Jenis/kategori ekskul: Ekskul Biasa, OSIS, MPK, Pramuka, PMR, Paskibra, dst. Diatur admin.
class JenisEkskul extends Model
{
    protected $table = 'jenis_ekskuls';

    // Alur pendaftaran yang sudah didukung sistem. "seleksi_casis" hanya untuk OSIS (alurnya terikat satu ekskul OSIS).
    public const ALUR = [
        'langsung'      => 'Daftar langsung, disetujui pembina',
        'seleksi_casis' => 'Seleksi CaSis (khusus OSIS)',
    ];

    protected $fillable = ['kode', 'nama', 'alur_pendaftaran', 'hanya_satu', 'aktif', 'bawaan', 'urutan'];

    protected function casts(): array
    {
        return [
            'hanya_satu' => 'boolean',
            'aktif'      => 'boolean',
            'bawaan'     => 'boolean',
            'urutan'     => 'integer',
        ];
    }

    public function ekskuls()
    {
        return $this->hasMany(Ekstrakurikuler::class, 'jenis_ekskul_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function memakaiCasis(): bool
    {
        return $this->alur_pendaftaran === 'seleksi_casis';
    }
}
