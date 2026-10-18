<?php

namespace App\Policies;

use App\Models\EkskulPengunduranDiri;
use App\Models\User;

class EkskulPengunduranDiriPolicy
{
    // Memutuskan (setuju/tolak) pengajuan keluar.
    //  - Tidak boleh memutuskan pengajuan sendiri.
    //  - Pengajuan anggota: admin atau pembina ekskul itu.
    //  - Pengajuan pembina: HANYA admin.
    public function putuskan(User $user, EkskulPengunduranDiri $pengajuan): bool
    {
        if ($pengajuan->status !== 'menunggu' || $pengajuan->user_id === $user->id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPembina()
            && $pengajuan->peran === 'anggota'
            && in_array((int) $pengajuan->ekstrakurikuler_id, $user->idEkskulDikelola(), true);
    }

    // Pengaju boleh membatalkan pengajuannya selama belum diputuskan.
    public function batalkan(User $user, EkskulPengunduranDiri $pengajuan): bool
    {
        return $pengajuan->status === 'menunggu' && $pengajuan->diajukan_oleh === $user->id;
    }
}