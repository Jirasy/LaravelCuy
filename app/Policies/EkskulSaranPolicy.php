<?php

namespace App\Policies;

use App\Models\EkskulSaran;
use App\Models\User;

class EkskulSaranPolicy
{
    // Menanggapi (ubah status / membalas): admin untuk semua, pembina hanya untuk ekskul yang ia bina.
    // Saran umum (tanpa ekskul) hanya admin.
    public function tanggapi(User $user, EkskulSaran $saran): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPembina()
            && $saran->ekstrakurikuler_id !== null
            && in_array((int) $saran->ekstrakurikuler_id, $user->idEkskulDikelola(), true);
    }
}