<?php

namespace App\Policies;

use App\Models\PendaftaranEkskul;
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

    // Melihat nomor HP seseorang sesuai pilihan pemiliknya (default: hanya admin & pembina).
    public function lihatNoHp(User $viewer, User $target): bool
    {
        if (blank($target->no_hp)) {
            return false;
        }

        if ($viewer->id === $target->id || $viewer->isStaff()) {
            return true;
        }

        return match ($target->no_hp_tampil) {
            'semua'          => true,
            'anggota_ekskul' => $this->satuEkskul($viewer, $target),
            default          => false,
        };
    }

    private function satuEkskul(User $a, User $b): bool
    {
        $milikA = PendaftaranEkskul::anggotaAktif()->where('user_id', $a->id)->pluck('ekstrakurikuler_id');

        if ($milikA->isEmpty()) {
            return false;
        }

        return PendaftaranEkskul::anggotaAktif()
            ->where('user_id', $b->id)
            ->whereIn('ekstrakurikuler_id', $milikA)
            ->exists();
    }
}