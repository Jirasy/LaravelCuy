<?php

namespace App\Console\Commands;

use App\Models\PendaftaranEkskul;
use App\Services\KeanggotaanService;
use Illuminate\Console\Command;

// Admin & pembina bukan siswa dan tidak mendaftar ekskul. Perintah ini merapikan sisa data lama
// (dari form pendaftaran manual yang dulu masih mengizinkan admin/pembina) supaya mereka tidak
// muncul sebagai anggota di daftar anggota, absensi, laporan, dan bentrok jadwal.
class BersihkanPendaftarStaf extends Command
{
    protected $signature = 'ekskul:bersihkan-pendaftar-staf {--terapkan : Terapkan perubahan. Tanpa opsi ini hanya menampilkan daftar (dry-run)}';

    protected $description = 'Rapikan pendaftaran ekskul milik admin/pembina (bukan siswa). Bawaan: dry-run, tidak mengubah data';

    public function handle(KeanggotaanService $keanggotaan): int
    {
        $baris = PendaftaranEkskul::with(['user:id,name,role', 'ekstrakurikuler:id,nama_ekstrakurikuler'])
            ->whereIn('status', ['pending', 'diterima'])
            ->where('status_anggota', 'aktif')
            ->where(function ($q) {
                $q->whereIn('peran', ['admin', 'pembina'])
                    ->orWhereHas('user', fn ($u) => $u->whereIn('role', ['admin', 'pembina']));
            })
            ->orderBy('ekstrakurikuler_id')
            ->get();

        if ($baris->isEmpty()) {
            $this->info('Tidak ada pendaftaran admin/pembina yang perlu dirapikan.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Nama', 'Peran', 'Ekskul', 'Status', 'Tindakan'],
            $baris->map(fn ($p) => [
                $p->id,
                $p->nama_pendaftar,
                $p->user?->role ?? $p->peran,
                $p->ekstrakurikuler?->nama_ekstrakurikuler,
                $p->status,
                $p->status === 'diterima' ? 'ditandai keluar' : 'ditolak',
            ])->all()
        );

        if (! $this->option('terapkan')) {
            $this->warn('Dry-run: belum ada data yang diubah. Jalankan lagi dengan --terapkan untuk menerapkan.');

            return self::SUCCESS;
        }

        foreach ($baris as $p) {
            if ($p->status === 'diterima') {
                // Riwayat tetap tersimpan; hanya tidak lagi dihitung sebagai anggota aktif
                $keanggotaan->terapkanKeluarAnggota($p, 'keluar');
            } else {
                $p->update(['status' => 'ditolak']);
            }

            $p->update(['alasan' => 'Admin/pembina tidak mendaftar ekskul (pembersihan data).']);
        }

        $this->info("Selesai: {$baris->count()} pendaftaran dirapikan.");

        return self::SUCCESS;
    }
}
