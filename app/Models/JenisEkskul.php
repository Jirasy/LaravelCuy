<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Tipe ekskul: Ekskul Biasa (tanpa seleksi), Ekskul Seleksi, Organisasi (OSIS, MPK, dst), Komunitas (belum resmi).
// Nama tabel/model tetap "jenis_ekskuls"/JenisEkskul; di antarmuka disebut "Tipe ekskul". Diatur admin.
class JenisEkskul extends Model
{
    protected $table = 'jenis_ekskuls';

    // Cara masuk: daftar langsung (disetujui pembina) atau lewat seleksi bertahap (jumlah tahap diatur per periode)
    public const ALUR = [
        'langsung' => 'Daftar langsung, disetujui pembina',
        'seleksi'  => 'Lewat seleksi bertahap',
    ];

    // Jenis wadahnya. "komunitas" = ekskul baru lahir yang belum diresmikan admin (tipe bawaan, tidak bisa dibuat admin lewat form ekskul)
    public const KATEGORI = [
        'ekskul'     => 'Ekskul',
        'organisasi' => 'Organisasi',
        'komunitas'  => 'Komunitas',
    ];

    protected $fillable = ['kode', 'nama', 'alur_pendaftaran', 'kategori', 'hanya_satu', 'aktif', 'bawaan', 'urutan'];

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

    // Tipe yang boleh dipilih admin di form ekskul (ekskul buatan admin langsung resmi, jadi bukan komunitas)
    public function scopeResmi($query)
    {
        return $query->where('kategori', '!=', 'komunitas');
    }

    // Pendaftarannya lewat seleksi bertahap. (Nama lama dipertahankan supaya pemanggil lama tetap jalan.)
    public function memakaiCasis(): bool
    {
        return $this->alur_pendaftaran === 'seleksi';
    }

    public function isOrganisasi(): bool
    {
        return $this->kategori === 'organisasi';
    }

    public function isKomunitas(): bool
    {
        return $this->kategori === 'komunitas';
    }
}
