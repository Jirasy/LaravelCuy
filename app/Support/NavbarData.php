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

        // Seleksi: siswa yang formulir pendaftarannya masih draft, dan penilai yang masih punya pendaftar untuk dinilai
        // di tahap yang sedang berjalan. Tiap tautan menuju ekskul pertama yang relevan.
        $casisDraft       = false;
        $casisDraftEkskul = null;
        $casisTugas       = 0;
        $casisTugasEkskul = null;
        $layanan          = app(OsisService::class);

        if ($user->role === 'siswa') {
            $draft = CasisPendaftaran::with('periode')
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->whereHas('periode', fn ($q) => $q->where('status', 'rekrutmen'))
                ->first();

            if ($draft) {
                $casisDraft       = true;
                $casisDraftEkskul = $draft->periode->ekstrakurikuler_id;
            }
        }

        foreach (OsisPeriode::where('status', 'seleksi')->get() as $periode) {
            if (! $layanan->tipeReviewer($user, $periode)) {
                continue;
            }

            $tahaps = $periode->tahapSemua();

            $jumlah = CasisPendaftaran::with(['hasils', 'reviews'])
                ->where('osis_periode_id', $periode->id)
                ->where('status', 'diajukan')
                ->where('user_id', '!=', $user->id)
                ->get()
                ->filter(function ($c) use ($tahaps, $user) {
                    $sudah = $c->hasils->pluck('seleksi_tahap_id')->all();
                    $tahap = $tahaps->first(fn ($t) => ! in_array($t->id, $sudah, true));

                    return $tahap && ! $c->reviews->contains(fn ($r) => $r->reviewer_id === $user->id && $r->seleksi_tahap_id === $tahap->id);
                })
                ->count();

            if ($jumlah > 0) {
                $casisTugas += $jumlah;
                $casisTugasEkskul ??= $periode->ekstrakurikuler_id;
            }
        }

        return [
            'notifikasi'  => $user->unreadNotifications()->latest()->limit(6)->get(),
            'jumlahNotif' => $user->unreadNotifications()->count(),
            'tugas'       => $tugas->take(6),
            'jumlahTugas' => $tugas->count(),
            'tautan'      => EkskulTautan::whereNull('ekstrakurikuler_id')->orderBy('urutan')->orderBy('id')->get(),
            'casisDraft'  => $casisDraft,
            'casisDraftEkskul' => $casisDraftEkskul,
            'casisTugas'  => $casisTugas,
            'casisTugasEkskul' => $casisTugasEkskul,
        ];
    }
}