<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulPengunduranDiri extends Model
{
    protected $table = 'ekskul_pengunduran_diris';

    protected $fillable = [
        'ekstrakurikuler_id', 'user_id', 'pendaftaran_id', 'peran', 'jenis', 'alasan',
        'diajukan_oleh', 'status', 'diputuskan_oleh', 'catatan_keputusan', 'diputuskan_pada',
    ];

    protected $casts = ['diputuskan_pada' => 'datetime'];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranEkskul::class);
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function pemutus()
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }

    // Pengajuan yang menunggu dan BOLEH diputuskan oleh $u:
    //  admin  = semua (kecuali miliknya sendiri)
    //  pembina = hanya pengajuan anggota di ekskul yang ia bina (bukan miliknya sendiri)
    public function scopeMenungguUntuk($query, User $u)
    {
        $query->where('status', 'menunggu')->where('user_id', '!=', $u->id);

        if ($u->isAdmin()) {
            return $query;
        }

        return $query->where('peran', 'anggota')
            ->whereIn('ekstrakurikuler_id', $u->isPembina() ? $u->idEkskulDikelola() : []);
    }

    public function getJenisLabelAttribute(): string
    {
        return $this->jenis === 'dikeluarkan' ? 'Dikeluarkan' : 'Mengundurkan diri';
    }
}