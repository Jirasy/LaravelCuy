<?php

namespace App\Policies;

use App\Models\Ekstrakurikuler;
use App\Models\User;

class EkstrakurikulerPolicy
{
    // Boleh mengelola isi ekskul ini: admin (semua ekskul) atau pembina yang ditugaskan di ekskul ini.
    public function kelola(User $user, Ekstrakurikuler $ekskul): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPembina() && in_array((int) $ekskul->id, $user->idEkskulDikelola(), true);
    }
}