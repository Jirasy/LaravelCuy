<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikulers';

    protected $fillable = [
        'nama_ekstrakurikuler',
        'pembina',
        'deskripsi',
        'status',
    ];

    public function pendaftaran()
    {
        return $this->hasMany(PendaftaranEkskul::class);
    }

    public function haris()
    {
        return $this->hasMany(EkskulHari::class);
    }

    public function pengumuman()
    {
        return $this->hasMany(EkskulPengumuman::class);
    }

    public function prestasi()
    {
        return $this->hasMany(EkskulPrestasi::class);
    }

    public function events()
    {
        return $this->hasMany(EkskulEvent::class);
    }

    // Semua pembina (termasuk yang sudah selesai), lengkap dengan tanggal mulai/selesai
    public function pembinas()
    {
        return $this->belongsToMany(User::class, 'ekskul_pembinas', 'ekstrakurikuler_id', 'user_id')
            ->withPivot('mulai', 'selesai')
            ->withTimestamps();
    }

    // Pembina yang masih bertugas
    public function pembinaAktif()
    {
        return $this->pembinas()->wherePivotNull('selesai');
    }

    // Nama pembina untuk ditampilkan; cadangannya teks lama kalau belum ada pembina terhubung
    public function getPembinaTampilAttribute(): string
    {
        $nama = $this->pembinaAktif->pluck('name');

        return $nama->isNotEmpty() ? $nama->implode(', ') : (string) $this->pembina;
    }

    // Jadwal per hari, urut Senin -> Minggu
    public function getJadwalAttribute()
    {
        $urutan = array_map(fn ($c) => $c->value, Hari::cases());

        return $this->haris
            ->sortBy(fn ($h) => array_search($h->hari, $urutan))
            ->values();
    }

    // Contoh hasil: "Rabu, Jumat"
    public function getHariLabelAttribute(): string
    {
        return $this->jadwal->map(fn ($h) => ucfirst($h->hari))->implode(', ');
    }

    /**
     * Samakan jadwal di database dengan yang diisi di form.
     * $jadwal = [['hari' => 'rabu', 'jam_mulai' => '15:30', 'jam_selesai' => '17:00'], ...]
     */
    public function syncJadwal(array $jadwal): void
    {
        $this->haris()->whereNotIn('hari', array_column($jadwal, 'hari'))->delete();

        foreach ($jadwal as $j) {
            $this->haris()->updateOrCreate(
                ['hari' => $j['hari']],
                ['jam_mulai' => $j['jam_mulai'], 'jam_selesai' => $j['jam_selesai']]
            );
        }
    }
}