<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Satu tahap seleksi dalam sebuah periode (mis. tes tulis, wawancara, praktik). Jumlah tahap bebas;
// waktu dan tempatnya diatur admin, pembina, atau senior penilai periode itu.
class SeleksiTahap extends Model
{
    protected $table = 'seleksi_tahaps';

    protected $fillable = ['osis_periode_id', 'urutan', 'nama', 'mulai_pada', 'selesai_pada', 'tempat', 'keterangan', 'dibuat_oleh'];

    protected $casts = [
        'mulai_pada'   => 'datetime',
        'selesai_pada' => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(OsisPeriode::class, 'osis_periode_id');
    }

    public function hasils()
    {
        return $this->hasMany(SeleksiHasil::class);
    }

    public function reviews()
    {
        return $this->hasMany(CasisReview::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // Contoh: "03-11-2026 08:00 - 10:00" atau "03-11-2026 08:00 - 04-11-2026 10:00"; null kalau belum dijadwalkan
    public function getWaktuTeksAttribute(): ?string
    {
        if (! $this->mulai_pada) {
            return null;
        }

        $teks = $this->mulai_pada->format('d-m-Y H:i');

        if ($this->selesai_pada) {
            $teks .= ' - '.($this->selesai_pada->isSameDay($this->mulai_pada)
                ? $this->selesai_pada->format('H:i')
                : $this->selesai_pada->format('d-m-Y H:i'));
        }

        return $teks;
    }

    // Satu baris ringkas jadwal untuk notifikasi: "Wawancara - 03-11-2026 08:00 - 10:00 di Aula"
    public function getRingkasanAttribute(): string
    {
        $bagian = [$this->nama];

        if ($this->waktu_teks) {
            $bagian[] = $this->waktu_teks;
        }

        $teks = implode(' - ', $bagian);

        return $this->tempat ? "{$teks} di {$this->tempat}" : $teks;
    }
}
