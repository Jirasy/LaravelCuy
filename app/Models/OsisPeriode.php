<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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