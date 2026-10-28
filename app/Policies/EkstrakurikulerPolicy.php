<?php

namespace App\Policies;

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
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

    // Membuka Materi & Task: pengelola ekskul, atau anggota aktif yang DIIZINKAN pembina/admin.
    public function lihatMateri(User $user, Ekstrakurikuler $ekskul): bool
    {
        if ($this->kelola($user, $ekskul)) {
            return true;
        }

        return PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $ekskul->id)
            ->where('user_id', $user->id)
            ->where('akses_materi', true)
            ->exists();
    }

    // Mengunggah ke galeri: pengelola ekskul, atau anggota aktif ekskul itu.
    // (Melihat foto yang sudah disetujui terbuka untuk semua user login, tidak lewat policy ini.)
    public function galeri(User $user, Ekstrakurikuler $ekskul): bool
    {
        if ($this->kelola($user, $ekskul)) {
            return true;
        }

        return PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $ekskul->id)
            ->where('user_id', $user->id)
            ->exists();
    }
}
