<?php

namespace App\Support;

use App\Models\CasisPendaftaran;
use App\Models\EkskulTautan;
use App\Models\EkskulTugasPenerima;
use App\Models\OsisPeriode;
use App\Models\User;
use App\Services\OsisService;

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

        // OSIS: siswa yang formulir CaSis-nya masih draft, dan penilai yang masih punya pendaftar untuk dinilai
        $casisDraft = false;
        $casisTugas = 0;
        $layanan    = app(OsisService::class);
        $ekskulOsis = $layanan->osis();

        if ($ekskulOsis) {
            if ($user->role === 'siswa') {
                $casisDraft = CasisPendaftaran::where('user_id', $user->id)
                    ->where('status', 'draft')
                    ->whereHas('periode', fn ($q) => $q->where('status', 'rekrutmen'))
                    ->exists();
            }

            $periodeSeleksi = OsisPeriode::where('ekstrakurikuler_id', $ekskulOsis->id)->where('status', 'seleksi')->latest()->first();

            if ($periodeSeleksi && $layanan->tipeReviewer($user, $periodeSeleksi)) {
                $casisTugas = CasisPendaftaran::where('osis_periode_id', $periodeSeleksi->id)
                    ->where('status', 'diajukan')
                    ->where('user_id', '!=', $user->id)
                    ->whereDoesntHave('reviews', fn ($q) => $q->where('reviewer_id', $user->id))
                    ->count();
            }
        }

        return [
            'notifikasi'  => $user->unreadNotifications()->latest()->limit(6)->get(),
            'jumlahNotif' => $user->unreadNotifications()->count(),
            'tugas'       => $tugas->take(6),
            'jumlahTugas' => $tugas->count(),
            'tautan'      => EkskulTautan::whereNull('ekstrakurikuler_id')->orderBy('urutan')->orderBy('id')->get(),
            'casisDraft'  => $casisDraft,
            'casisTugas'  => $casisTugas,
        ];
    }
}