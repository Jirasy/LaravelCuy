<?php

namespace App\Policies;

use App\Models\ProfilTag;
use App\Models\User;

class ProfilTagPolicy
{
    // Memberi tag ke profil orang lain: bukan ke diri sendiri, dan akun tujuannya harus aktif.
    public function beri(User $actor, User $pemilik): bool
    {
        return $actor->id !== $pemilik->id && (bool) $pemilik->is_active;
    }

    // Moderasi tag: aturannya sama dengan Kelola User (admin ke semua, pembina kecuali profil admin).
    public function moderasi(User $actor, ProfilTag $tag): bool
    {
        return $actor->can('kelola', $tag->pemilik);
    }

    // Menampilkan / menyembunyikan tag: pemilik profil atau moderator.
    public function ubahStatus(User $actor, ProfilTag $tag): bool
    {
        return $actor->id === $tag->user_id || $this->moderasi($actor, $tag);
    }

    // Mengubah teks tag: hanya moderator (pemilik tidak bisa mengubah kata-kata orang lain).
    public function ubahTeks(User $actor, ProfilTag $tag): bool
    {
        return $this->moderasi($actor, $tag);
    }

    // Menghapus tag: pemilik profil, pemberinya (menarik kembali), atau moderator.
    public function hapus(User $actor, ProfilTag $tag): bool
    {
        return $actor->id === $tag->user_id
            || $actor->id === $tag->pemberi_id
            || $this->moderasi($actor, $tag);
    }
}
