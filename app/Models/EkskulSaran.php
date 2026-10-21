<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulSaran extends Model
{
    protected $table = 'ekskul_sarans';

    protected $fillable = [
        'user_id', 'ekstrakurikuler_id', 'jenis', 'isi', 'anonim', 'status', 'balasan', 'dibalas_oleh', 'dibalas_pada',
    ];

    protected $casts = [
        'anonim'       => 'boolean',
        'dibalas_pada' => 'datetime',
    ];

    public const STATUS = [
        'baru'            => 'Baru',
        'dibaca'          => 'Sudah dibaca',
        'ditindaklanjuti' => 'Ditindaklanjuti',
    ];

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function penjawab()
    {
        return $this->belongsTo(User::class, 'dibalas_oleh');
    }

    // Yang boleh dibaca $u: admin = semua (termasuk yang umum); pembina = hanya untuk ekskul yang ia bina
    public function scopeUntukPengelola($query, User $u)
    {
        if ($u->isAdmin()) {
            return $query;
        }

        return $query->whereIn('ekstrakurikuler_id', $u->isPembina() ? $u->idEkskulDikelola() : []);
    }

    // Nama pengirim untuk tampilan pengelola (anonim = disembunyikan)
    public function getNamaPengirimAttribute(): string
    {
        return $this->anonim ? 'Anonim' : ($this->pengirim->name ?? 'Pengguna dihapus');
    }
}