<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Kategori/label ekskul yang diatur admin; satu ekskul boleh memiliki banyak kategori (pivot ekstrakurikuler_kategori).
// Tidak ada hubungannya dengan perilaku sistem: itu tugas tipe ekskul (JenisEkskul).
class KategoriEkskul extends Model
{
    protected $table = 'kategori_ekskuls';

    protected $fillable = ['nama', 'slug', 'urutan', 'aktif'];

    protected function casts(): array
    {
        return [
            'aktif'  => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function ekskuls()
    {
        return $this->belongsToMany(Ekstrakurikuler::class, 'ekstrakurikuler_kategori', 'kategori_ekskul_id', 'ekstrakurikuler_id');
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
