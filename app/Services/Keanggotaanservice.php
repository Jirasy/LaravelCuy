<?php

namespace App\Services;

use App\Models\EkskulAnggotaJabatan;
use App\Models\EkskulPembina;
use App\Models\EkskulPengunduranDiri;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
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

        return EkskulPengunduranDiri::create([
            'ekstrakurikuler_id' => $p->ekstrakurikuler_id,
            'user_id'            => $pengaju->id,
            'pendaftaran_id'     => $p->id,
            'peran'              => 'anggota',
            'jenis'              => 'mengundurkan_diri',
            'alasan'             => $alasan,
            'diajukan_oleh'      => $pengaju->id,
        ]);
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

        return EkskulPengunduranDiri::create([
            'ekstrakurikuler_id' => $ekskul->id,
            'user_id'            => $pembina->id,
            'peran'              => 'pembina',
            'jenis'              => 'mengundurkan_diri',
            'alasan'             => $alasan,
            'diajukan_oleh'      => $pembina->id,
        ]);
    }

    // Admin/pembina mengeluarkan anggota: langsung sah, alasan tercatat
    public function keluarkan(PendaftaranEkskul $p, User $pelaku, string $alasan): EkskulPengunduranDiri
    {
        if ($p->user_id !== null && $p->user_id === $pelaku->id) {
            throw new DomainException('Kamu tidak bisa mengeluarkan dirimu sendiri. Gunakan pengajuan pengunduran diri.');
        }

        $this->pastikanAnggotaAktif($p);

        return DB::transaction(function () use ($p, $pelaku, $alasan) {
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