<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// Riwayat pesan privat untuk admin/pembina yang mengunggah berkas. Id notifikasi sengaja diisi sendiri
// (sama dengan id rekap di URL), jadi halaman rekap cukup mencarinya di notifikasi milik user yang login.
// Password TIDAK disimpan di sini: tabel notifications tidak terenkripsi. Password ada di kredensial_sementara.
class RekapImportSiswa extends Notification
{
    /**
     * @param  list<int>  $idSiswa
     * @param  list<string>  $namaSiswa
     * @param  list<array{nama: string, alasan: string}>  $dilewati
     */
    public function __construct(
        string $id,
        private string $namaBerkas,
        private array $idSiswa,
        private array $namaSiswa,
        private array $dilewati,
        private int $diabaikan,
    ) {
        $this->id = $id;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $jumlah = count($this->idSiswa);
        $tampil = array_slice($this->namaSiswa, 0, 15);
        $sisa = $jumlah - count($tampil);

        $pesan = "{$jumlah} akun siswa baru dibuat dari berkas \"{$this->namaBerkas}\".\n"
            .'Nama: '.implode(', ', $tampil).($sisa > 0 ? " dan {$sisa} lainnya" : '').".\n";

        if ($this->dilewati !== []) {
            $pesan .= count($this->dilewati)." nama dilewati (sudah terdaftar atau kembar di berkas).\n";
        }

        $pesan .= 'Buka untuk melihat email dan password sementara tiap siswa (tersedia 7 hari).';

        return [
            'judul' => "Import siswa selesai: {$jumlah} akun dibuat",
            'pesan' => $pesan,
            'url' => route('import-siswa.hasil', $this->id, false),
            'ikon' => '📥',
            'berkas' => $this->namaBerkas,
            'id_siswa' => $this->idSiswa,
            'dilewati' => $this->dilewati,
            'diabaikan' => $this->diabaikan,
        ];
    }
}
