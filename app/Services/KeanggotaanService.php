<?php

namespace App\Services;

use App\Models\EkskulAnggotaJabatan;
use App\Models\EkskulPembina;
use App\Models\EkskulPengunduranDiri;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use App\Support\Pemberitahuan;
use DomainException;
use Illuminate\Support\Facades\DB;

// Semua perpindahan status keanggotaan lewat sini supaya aturannya konsisten.
// Pelanggaran aturan dilempar sebagai DomainException (pesannya boleh ditampilkan ke user).
class KeanggotaanService
{
    // Siswa mengajukan keluar -> menunggu persetujuan pembina atau admin
    public function ajukanAnggota(PendaftaranEkskul $p, User $pengaju, string $alasan): EkskulPengunduranDiri
    {
        if ($p->user_id !== $pengaju->id) {
            throw new DomainException('Pendaftaran ini bukan milikmu.');
        }

        $this->pastikanAnggotaAktif($p);
        $this->pastikanBelumAdaPengajuan($p->ekstrakurikuler_id, $pengaju->id);

        $pengajuan = EkskulPengunduranDiri::create([
            'ekstrakurikuler_id' => $p->ekstrakurikuler_id,
            'user_id'            => $pengaju->id,
            'pendaftaran_id'     => $p->id,
            'peran'              => 'anggota',
            'jenis'              => 'mengundurkan_diri',
            'alasan'             => $alasan,
            'diajukan_oleh'      => $pengaju->id,
        ]);

        $ekskul = $p->ekstrakurikuler;
        Pemberitahuan::kirim(
            Pemberitahuan::pengelola($ekskul),
            'Pengajuan keluar ekskul',
            "{$pengaju->name} mengajukan keluar dari {$ekskul->nama_ekstrakurikuler}.",
            route('pengunduran.index', [], false),
            '📤',
            $pengaju->id
        );

        return $pengajuan;
    }

    // Pembina mengajukan keluar -> menunggu persetujuan ADMIN
    public function ajukanPembina(Ekstrakurikuler $ekskul, User $pembina, string $alasan): EkskulPengunduranDiri
    {
        $bertugas = EkskulPembina::where('ekstrakurikuler_id', $ekskul->id)
            ->where('user_id', $pembina->id)
            ->whereNull('selesai')
            ->exists();

        if (!$bertugas) {
            throw new DomainException('Kamu bukan pembina yang bertugas di ekskul ini.');
        }

        $this->pastikanBelumAdaPengajuan($ekskul->id, $pembina->id);

        $pengajuan = EkskulPengunduranDiri::create([
            'ekstrakurikuler_id' => $ekskul->id,
            'user_id'            => $pembina->id,
            'peran'              => 'pembina',
            'jenis'              => 'mengundurkan_diri',
            'alasan'             => $alasan,
            'diajukan_oleh'      => $pembina->id,
        ]);

        Pemberitahuan::kirim(
            Pemberitahuan::admin(),
            'Pembina mengajukan keluar',
            "{$pembina->name} mengajukan keluar sebagai pembina {$ekskul->nama_ekstrakurikuler}.",
            route('pengunduran.index', [], false),
            '📤',
            $pembina->id
        );

        return $pengajuan;
    }

    // Admin/pembina mengeluarkan anggota: langsung sah, alasan tercatat
    public function keluarkan(PendaftaranEkskul $p, User $pelaku, string $alasan): EkskulPengunduranDiri
    {
        if ($p->user_id !== null && $p->user_id === $pelaku->id) {
            throw new DomainException('Kamu tidak bisa mengeluarkan dirimu sendiri. Gunakan pengajuan pengunduran diri.');
        }

        $this->pastikanAnggotaAktif($p);

        $catatan = DB::transaction(function () use ($p, $pelaku, $alasan) {
            $catatan = EkskulPengunduranDiri::create([
                'ekstrakurikuler_id' => $p->ekstrakurikuler_id,
                'user_id'            => $p->user_id,
                'pendaftaran_id'     => $p->id,
                'peran'              => 'anggota',
                'jenis'              => 'dikeluarkan',
                'alasan'             => $alasan,
                'diajukan_oleh'      => $pelaku->id,
                'status'             => 'disetujui',
                'diputuskan_oleh'    => $pelaku->id,
                'diputuskan_pada'    => now(),
            ]);

            $this->terapkanKeluarAnggota($p, 'dikeluarkan');

            return $catatan;
        });

        if ($p->user) {
            Pemberitahuan::kirim(
                [$p->user],
                'Dikeluarkan dari ekskul',
                "Kamu dikeluarkan dari {$p->ekstrakurikuler->nama_ekstrakurikuler}. Alasan: {$alasan}",
                route('ekstrakurikuler.show', $p->ekstrakurikuler_id, false),
                '⛔',
                $pelaku->id
            );
        }

        return $catatan;
    }

    // Memutuskan pengajuan (hak akses sudah dicek di Policy)
    public function putuskan(EkskulPengunduranDiri $pengajuan, User $pemutus, bool $setuju, ?string $catatan): void
    {
        if ($pengajuan->status !== 'menunggu') {
            throw new DomainException('Pengajuan ini sudah diputuskan.');
        }

        DB::transaction(function () use ($pengajuan, $pemutus, $setuju, $catatan) {
            $pengajuan->update([
                'status'            => $setuju ? 'disetujui' : 'ditolak',
                'diputuskan_oleh'   => $pemutus->id,
                'catatan_keputusan' => $catatan,
                'diputuskan_pada'   => now(),
            ]);

            if (!$setuju) {
                return;
            }

            if ($pengajuan->peran === 'pembina') {
                EkskulPembina::where('ekstrakurikuler_id', $pengajuan->ekstrakurikuler_id)
                    ->where('user_id', $pengajuan->user_id)
                    ->whereNull('selesai')
                    ->update(['selesai' => now()->toDateString()]);

                return;
            }

            $p = PendaftaranEkskul::find($pengajuan->pendaftaran_id);
            if ($p && $p->status_anggota === 'aktif') {
                $this->terapkanKeluarAnggota($p, 'keluar');
            }
        });

        $ekskul = $pengajuan->ekstrakurikuler;

        // Beri tahu yang mengajukan
        if ($pengajuan->user) {
            Pemberitahuan::kirim(
                [$pengajuan->user],
                $setuju ? 'Pengunduran diri disetujui' : 'Pengunduran diri ditolak',
                'Pengajuanmu keluar dari ' . $ekskul->nama_ekstrakurikuler . ($setuju ? ' disetujui.' : ' ditolak.')
                    . (!$setuju && $catatan ? " Catatan: {$catatan}" : ''),
                route('ekstrakurikuler.show', $ekskul->id, false),
                $setuju ? '✅' : '❌',
                $pemutus->id
            );
        }

        // Pembina terakhir keluar -> tugas untuk semua admin: cari pengganti
        if ($setuju && $pengajuan->peran === 'pembina'
            && !EkskulPembina::where('ekstrakurikuler_id', $ekskul->id)->whereNull('selesai')->exists()) {
            Pemberitahuan::kirim(
                Pemberitahuan::admin(),
                'Butuh pembina pengganti',
                "{$ekskul->nama_ekstrakurikuler} sekarang belum punya pembina. Tugaskan pengganti.",
                route('ekstrakurikuler.edit', $ekskul->id, false),
                '🛡️',
                $pemutus->id
            );
        }
    }

    private function terapkanKeluarAnggota(PendaftaranEkskul $p, string $status): void
    {
        $p->update([
            'status_anggota' => $status,
            'tanggal_keluar' => now()->toDateString(),
            'tahun_selesai'  => $p->tahun_selesai ?: now()->year,
        ]);

        // Jabatan yang masih dipegang otomatis diakhiri (riwayat tetap tersimpan)
        EkskulAnggotaJabatan::where('pendaftaran_id', $p->id)
            ->whereNull('periode_selesai')
            ->get()
            ->each(fn ($t) => $t->update(['periode_selesai' => max((int) now()->year, (int) $t->periode_mulai)]));
    }

    private function pastikanAnggotaAktif(PendaftaranEkskul $p): void
    {
        if ($p->status !== 'diterima' || $p->status_anggota !== 'aktif') {
            throw new DomainException('Hanya anggota aktif yang bisa keluar atau dikeluarkan.');
        }
    }

    private function pastikanBelumAdaPengajuan(int $ekskulId, int $userId): void
    {
        $ada = EkskulPengunduranDiri::where('ekstrakurikuler_id', $ekskulId)
            ->where('user_id', $userId)
            ->where('status', 'menunggu')
            ->exists();

        if ($ada) {
            throw new DomainException('Pengajuan pengunduran diri sebelumnya masih menunggu keputusan.');
        }
    }
}