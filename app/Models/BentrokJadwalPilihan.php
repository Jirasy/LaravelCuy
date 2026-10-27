<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pilihan siswa untuk satu pasangan ekskul yang jadwalnya bentrok di satu hari.
 * Berlaku setiap minggu selama jadwal keduanya masih bentrok.
 */
class BentrokJadwalPilihan extends Model
{
    protected $table = 'bentrok_jadwal_pilihans';

    public const PILIHAN = ['ekskul_a', 'ekskul_b', 'bagi_waktu'];

    protected $fillable = [
        'user_id',
        'hari',
        'ekskul_a_id',
        'ekskul_b_id',
        'pilihan',
        'catatan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ekskulA(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekskul_a_id');
    }

    public function ekskulB(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekskul_b_id');
    }

    // Kunci pasangan, contoh: "sabtu:3:7" (dipakai untuk mencocokkan bentrok dengan pilihan tersimpan)
    public static function kunci(string $hari, int $ekskulAId, int $ekskulBId): string
    {
        return "{$hari}:{$ekskulAId}:{$ekskulBId}";
    }
}
