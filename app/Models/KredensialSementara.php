<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Support\Facades\Crypt;

// Password sementara yang bisa dibuka lagi oleh admin (terenkripsi, berbatas waktu).
class KredensialSementara extends Model
{
    use MassPrunable;

    public const MASA_BERLAKU_HARI = 7;

    protected $table = 'kredensial_sementara';

    protected $fillable = [
        'user_id',
        'ekstrakurikuler_id',
        'dibuat_oleh',
        'password_terenkripsi',
        'kedaluwarsa_pada',
        'terakhir_dilihat_pada',
        'terakhir_dilihat_oleh',
    ];

    protected $hidden = ['password_terenkripsi'];

    protected function casts(): array
    {
        return [
            'kedaluwarsa_pada'      => 'datetime',
            'terakhir_dilihat_pada' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ekskul()
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pelihat()
    {
        return $this->belongsTo(User::class, 'terakhir_dilihat_oleh');
    }

    // Catat (atau timpa) password sementara sebuah akun
    public static function catat(User $user, string $password, ?Ekstrakurikuler $ekskul, ?User $oleh): self
    {
        return static::updateOrCreate(['user_id' => $user->id], [
            'ekstrakurikuler_id'    => $ekskul?->id,
            'dibuat_oleh'           => $oleh?->id,
            'password_terenkripsi'  => Crypt::encryptString($password),
            'kedaluwarsa_pada'      => now()->addDays(self::MASA_BERLAKU_HARI),
            'terakhir_dilihat_pada' => null,
            'terakhir_dilihat_oleh' => null,
        ]);
    }

    public function sudahKedaluwarsa(): bool
    {
        return $this->kedaluwarsa_pada->isPast();
    }

    // null kalau tidak bisa dibaca (mis. APP_KEY sudah diganti)
    public function passwordAsli(): ?string
    {
        try {
            return Crypt::decryptString($this->password_terenkripsi);
        } catch (DecryptException) {
            return null;
        }
    }

    // Dipakai `php artisan model:prune`
    public function prunable(): Builder
    {
        return static::where('kedaluwarsa_pada', '<', now());
    }
}
