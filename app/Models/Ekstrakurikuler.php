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
        'jam_mulai',
        'jam_selesai',
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

    // Contoh hasil: "Rabu, Jumat" (urut Senin -> Minggu)
    public function getHariLabelAttribute(): string
    {
        $urutan = array_map(fn ($c) => $c->value, Hari::cases());

        return $this->haris
            ->pluck('hari')
            ->sortBy(fn ($h) => array_search($h, $urutan))
            ->map(fn ($h) => ucfirst($h))
            ->implode(', ');
    }

    // Samakan daftar hari di database dengan yang dicentang di form
    public function syncHari(array $hari): void
    {
        $hari = array_values(array_unique($hari));

        $this->haris()->whereNotIn('hari', $hari)->delete();

        foreach ($hari as $h) {
            $this->haris()->firstOrCreate(['hari' => $h]);
        }
    }
}