<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Satu periode rekrutmen/kepengurusan sebuah ekskul berseleksi (OSIS, MPK, ekskul seleksi, dst), mis. "2026/2027".
// Nama OSIS dipertahankan dari versi awal; sekarang dipakai semua ekskul bertipe Ekskul Seleksi atau Organisasi.
class OsisPeriode extends Model
{
    protected $table = 'osis_periodes';

    protected $fillable = ['ekstrakurikuler_id', 'nama', 'status', 'buka_pada', 'tutup_pada'];

    protected $casts = [
        'buka_pada'  => 'date',
        'tutup_pada' => 'date',
    ];

    public const STATUS = [
        'rekrutmen' => 'Pendaftaran dibuka',
        'seleksi'   => 'Seleksi',
        'berjalan'  => 'Berjalan',
        'selesai'   => 'Selesai',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function casis()
    {
        return $this->hasMany(CasisPendaftaran::class);
    }

    // Tahap seleksi periode ini, berurutan. Jumlahnya bebas; periode baru otomatis punya tahap 1.
    public function tahaps()
    {
        return $this->hasMany(SeleksiTahap::class, 'osis_periode_id')->orderBy('urutan');
    }

    // Semua tahap berurutan. Periode yang belum punya tahap otomatis diberi tahap 1 ("Seleksi").
    public function tahapSemua(): \Illuminate\Support\Collection
    {
        $tahaps = $this->tahaps()->get();

        if ($tahaps->isEmpty()) {
            $tahaps = collect([$this->tahaps()->create(['urutan' => 1, 'nama' => 'Seleksi'])]);
        }

        return $tahaps;
    }

    public function seniors()
    {
        return $this->belongsToMany(User::class, 'osis_periode_seniors', 'osis_periode_id', 'user_id')->withTimestamps();
    }

    // Periode yang masih menerima pendaftaran CaSis: status rekrutmen dan dalam rentang tanggal (kalau diisi)
    public function scopeTerbuka($query)
    {
        return $query->where('status', 'rekrutmen')
            ->where(fn ($q) => $q->whereNull('buka_pada')->orWhere('buka_pada', '<=', today()))
            ->where(fn ($q) => $q->whereNull('tutup_pada')->orWhere('tutup_pada', '>=', today()));
    }
}