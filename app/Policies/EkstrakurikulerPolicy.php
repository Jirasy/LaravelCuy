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

    // Mengelola JUMLAH anggota (kuota, buka/tutup pendaftaran, memutuskan pendaftar): pengelola ekskul, atau
    // penanggung jawab anggota, yaitu anggota aktif yang memegang jabatan bertanda "penanggung jawab anggota".
    // Jabatan, keanggotaan, dan mengeluarkan anggota tetap hanya lewat kelola().
    public function kelolaAnggota(User $user, Ekstrakurikuler $ekskul): bool
    {
        return $this->kelola($user, $ekskul)
            || in_array((int) $ekskul->id, $user->idEkskulPenanggungJawab(), true);
    }

    // Memutuskan pendaftar: pengelola boleh semuanya. Penanggung jawab anggota hanya boleh memutuskan pendaftar yang
    // masih menunggu, bukan miliknya sendiri, dan bukan di ekskul berseleksi (itu lewat alur seleksi/CaSis).
    public function putuskanPendaftaran(User $user, Ekstrakurikuler $ekskul, PendaftaranEkskul $pendaftaran): bool
    {
        if ($this->kelola($user, $ekskul)) {
            return true;
        }

        return $pendaftaran->status === 'pending'
            && (int) $pendaftaran->ekstrakurikuler_id === (int) $ekskul->id
            && (int) $pendaftaran->user_id !== (int) $user->id
            && $this->kelolaAnggota($user, $ekskul)
            && ! $ekskul->loadMissing('jenisEkskul')->memakaiCasis();
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
