<?php

namespace App\Support;

use App\Models\EkskulTautan;
use App\Models\EkskulTugasPenerima;
use App\Models\User;

// Data untuk tombol Pesan/Task di navbar. Di-cache selama satu request.
class NavbarData
{
    private static array $cache = [];

    public static function untuk(User $user): array
    {
        return self::$cache[$user->id] ??= self::muat($user);
    }

    private static function muat(User $user): array
    {
        $tugas = EkskulTugasPenerima::with('tugas')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'selesai')
            ->latest()
            ->limit(50)
            ->get()
            ->sortBy(fn ($p) => optional($p->tugas->tenggat)->timestamp ?? PHP_INT_MAX)
            ->values();

        return [
            'notifikasi'  => $user->unreadNotifications()->latest()->limit(6)->get(),
            'jumlahNotif' => $user->unreadNotifications()->count(),
            'tugas'       => $tugas->take(6),
            'jumlahTugas' => $tugas->count(),
            'tautan'      => EkskulTautan::whereNull('ekstrakurikuler_id')->orderBy('urutan')->orderBy('id')->get(),
        ];
    }
}