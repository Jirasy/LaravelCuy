<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulNilai extends Model
{
    protected $table = 'ekskul_nilais';

    protected $fillable = [
        'pendaftaran_id', 'periode', 'keterampilan', 'disiplin', 'kerja_sama', 'sikap', 'saran', 'kritik', 'penilai_id',
    ];

    public const ASPEK = [
        'keterampilan' => 'Keterampilan',
        'disiplin'     => 'Disiplin',
        'kerja_sama'   => 'Kerja Sama',
        'sikap'        => 'Sikap',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranEkskul::class, 'pendaftaran_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    // Rata-rata empat aspek, satu angka desimal
    public function getRataRataAttribute(): float
    {
        return round(($this->keterampilan + $this->disiplin + $this->kerja_sama + $this->sikap) / 4, 1);
    }

    // A >= 90, B >= 80, C >= 70, D >= 60, selain itu E
    public function getPredikatAttribute(): string
    {
        $n = $this->rata_rata;

        return match (true) {
            $n >= 90 => 'A',
            $n >= 80 => 'B',
            $n >= 70 => 'C',
            $n >= 60 => 'D',
            default  => 'E',
        };
    }

    // Suggestion periode berjalan, mis. "Semester Ganjil 2026/2027"
    public static function periodeSaatIni(): string
    {
        $y = (int) now()->year;

        return now()->month >= 7
            ? "Semester Ganjil {$y}/" . ($y + 1)
            : 'Semester Genap ' . ($y - 1) . "/{$y}";
    }
}