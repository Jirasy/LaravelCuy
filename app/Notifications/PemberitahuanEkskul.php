<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// Satu kelas notifikasi umum untuk semua kejadian (disimpan di tabel notifications).
class PemberitahuanEkskul extends Notification
{
    public function __construct(
        private string $judul,
        private string $pesan,
        private ?string $url = null,   // alamat RELATIF, mis. /ekstrakurikuler/3
        private string $ikon = '🔔',
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'url'   => $this->url,
            'ikon'  => $this->ikon,
        ];
    }
}