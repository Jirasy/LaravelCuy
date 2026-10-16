<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulPrestasi extends Model
{
    protected $table = 'ekskul_prestasis';

    protected $fillable = ['ekstrakurikuler_id', 'judul', 'tingkat', 'peringkat', 'tahun', 'penyelenggara', 'deskripsi'];

    public const TINGKAT = [
        'sekolah'       => 'Sekolah',
        'kecamatan'     => 'Kecamatan',
        'kabupaten'     => 'Kabupaten/Kota',
        'provinsi'      => 'Provinsi',
        'nasional'      => 'Nasional',
        'internasional' => 'Internasional',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function anggota()
    {
        return $this->belongsToMany(PendaftaranEkskul::class, 'ekskul_prestasi_anggota', 'prestasi_id', 'pendaftaran_id');
    }

    public function getTingkatLabelAttribute(): string
    {
        return self::TINGKAT[$this->tingkat] ?? ucfirst($this->tingkat);
    }
}