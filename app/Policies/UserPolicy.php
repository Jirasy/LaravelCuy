<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // Mengelola akun orang lain (edit, nonaktifkan, hapus).
    // Admin: semua akun. Pembina: semua KECUALI akun admin.
    public function kelola(User $actor, User $target): bool
    {
        if (!$actor->isStaff()) {
            return false;
        }

        return $actor->isAdmin() || !$target->isAdmin();
    }

    // Mengubah role: hanya admin, dan tidak boleh untuk akunnya sendiri.
    public function ubahRole(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->id !== $target->id;
    }
}