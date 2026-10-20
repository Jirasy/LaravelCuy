<?php

namespace App\Support;

use App\Models\Ekstrakurikuler;
use App\Models\User;
use App\Notifications\PemberitahuanEkskul;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class Pemberitahuan
{
    // Kirim ke satu atau banyak user. $kecuali = id user yang tidak perlu diberi tahu (biasanya pelaku).
    public static function kirim(iterable $penerima, string $judul, string $pesan, ?string $url = null, string $ikon = '🔔', ?int $kecuali = null): void
    {
        $users = collect($penerima)
            ->filter()
            ->unique('id')
            ->reject(fn ($u) => $kecuali !== null && $u->id === $kecuali)
            ->values();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new PemberitahuanEkskul($judul, $pesan, $url, $ikon));
    }

    // Semua admin yang akunnya aktif
    public static function admin(): Collection
    {
        return User::where('role', 'admin')->where('is_active', true)->get();
    }

    // Yang berhak mengelola ekskul: semua admin + pembina yang sedang bertugas di ekskul itu
    public static function pengelola(Ekstrakurikuler $ekskul): Collection
    {
        return self::admin()->merge(
            $ekskul->pembinaAktif()->where('users.is_active', true)->get()
        )->unique('id')->values();
    }
}