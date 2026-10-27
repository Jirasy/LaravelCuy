<?php

namespace App\Services;

use App\Enums\Hari;
use App\Models\BentrokJadwalPilihan;
use App\Models\EkskulHari;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Mendeteksi jadwal ekskul yang tumpang tindih di hari yang sama.
 * Dua jadwal bentrok kalau rentang jamnya beririsan; selesai tepat saat yang lain mulai tidak dihitung bentrok.
 *
 * @phpstan-type BarisJadwal array{ekskul_id: int, ekskul: string, hari: string, mulai: string, selesai: string, peran: string}
 * @phpstan-type Bentrok array{hari: string, a: BarisJadwal, b: BarisJadwal}
 */
class BentrokJadwalService
{
    /**
     * @return array{ekskul_id: int, ekskul: string, hari: string, mulai: string, selesai: string, peran: string}
     */
    public static function barisJadwal(int $ekskulId, string $nama, EkskulHari $jadwal, string $peran): array
    {
        return [
            'ekskul_id' => $ekskulId,
            'ekskul' => $nama,
            'hari' => $jadwal->hari,
            'mulai' => substr($jadwal->jam_mulai, 0, 5),
            'selesai' => substr($jadwal->jam_selesai, 0, 5),
            'peran' => $peran,
        ];
    }

    /**
     * Cari semua pasangan jadwal yang bentrok. Pasangan diurutkan: ekskul "a" selalu ber-id lebih kecil.
     *
     * @param  Collection<int, array{ekskul_id: int, ekskul: string, hari: string, mulai: string, selesai: string, peran: string}>  $jadwal
     * @return Collection<int, array{hari: string, a: array<string, mixed>, b: array<string, mixed>}>
     */
    public function cari(Collection $jadwal): Collection
    {
        $hasil = collect();

        foreach ($jadwal->groupBy('hari') as $hari => $barisHari) {
            $urut = $barisHari->sortBy('mulai')->values();

            foreach ($urut as $i => $baris) {
                foreach ($urut->slice($i + 1) as $lain) {
                    // Sudah urut menurut jam mulai, jadi kalau yang ini tidak beririsan, sisanya juga tidak
                    if ($lain['mulai'] >= $baris['selesai']) {
                        break;
                    }

                    if ($lain['ekskul_id'] === $baris['ekskul_id']) {
                        continue;
                    }

                    [$a, $b] = $baris['ekskul_id'] < $lain['ekskul_id'] ? [$baris, $lain] : [$lain, $baris];
                    $hasil->push(['hari' => $hari, 'a' => $a, 'b' => $b]);
                }
            }
        }

        $urutanHari = array_map(fn (Hari $h) => $h->value, Hari::cases());

        return $hasil
            ->sortBy(fn (array $bentrok) => array_search($bentrok['hari'], $urutanHari).'-'.$bentrok['a']['mulai'])
            ->values();
    }

    /**
     * Bentrok di antara ekskul yang SEDANG diikuti user (disetujui dan masih aktif).
     *
     * @return Collection<int, array{hari: string, a: array<string, mixed>, b: array<string, mixed>}>
     */
    public function bentrokUntukUser(User $user): Collection
    {
        $anggota = PendaftaranEkskul::with('ekstrakurikuler.haris')
            ->anggotaAktif()
            ->where('user_id', $user->id)
            ->get();

        return $this->cari($this->jadwalDariPendaftaran($anggota));
    }

    /**
     * Bentrok yang melibatkan $ekskul bila user mengikutinya, dibandingkan dengan ekskul lain miliknya
     * yang sudah disetujui atau masih menunggu keputusan.
     *
     * @return Collection<int, array{hari: string, a: array<string, mixed>, b: array<string, mixed>}>
     */
    public function bentrokUntukEkskulBaru(User $user, Ekstrakurikuler $ekskul): Collection
    {
        $lain = PendaftaranEkskul::with('ekstrakurikuler.haris')
            ->where('user_id', $user->id)
            ->where('ekstrakurikuler_id', '!=', $ekskul->id)
            ->where('status', '!=', 'ditolak')
            ->where('status_anggota', 'aktif')
            ->get();

        $jadwal = $this->jadwalDariPendaftaran($lain)->merge(
            $ekskul->haris->map(fn (EkskulHari $h) => self::barisJadwal($ekskul->id, $ekskul->nama_ekstrakurikuler, $h, 'Anggota'))
        );

        return $this->cari($jadwal)
            ->filter(fn (array $bentrok) => $bentrok['a']['ekskul_id'] === $ekskul->id || $bentrok['b']['ekskul_id'] === $ekskul->id)
            ->values();
    }

    /**
     * Tempelkan pilihan tersimpan milik user (atau null kalau belum memilih) ke tiap bentrok.
     *
     * @param  Collection<int, array{hari: string, a: array<string, mixed>, b: array<string, mixed>}>  $bentrok
     * @return Collection<int, array<string, mixed>>
     */
    public function denganPilihan(Collection $bentrok, User $user): Collection
    {
        if ($bentrok->isEmpty()) {
            return $bentrok;
        }

        $pilihans = BentrokJadwalPilihan::where('user_id', $user->id)
            ->get()
            ->keyBy(fn (BentrokJadwalPilihan $p) => BentrokJadwalPilihan::kunci($p->hari, $p->ekskul_a_id, $p->ekskul_b_id));

        return $bentrok->map(fn (array $b) => $b + [
            'pilihan' => $pilihans->get(BentrokJadwalPilihan::kunci($b['hari'], $b['a']['ekskul_id'], $b['b']['ekskul_id'])),
        ]);
    }

    /**
     * Kalimat singkat untuk peringatan dan notifikasi.
     *
     * @param  array{hari: string, a: array<string, mixed>, b: array<string, mixed>}  $bentrok
     */
    public function ringkas(array $bentrok): string
    {
        $hari = Hari::from($bentrok['hari'])->label();
        $a = $bentrok['a'];
        $b = $bentrok['b'];

        return "{$a['ekskul']} ({$a['mulai']}–{$a['selesai']}) bentrok dengan {$b['ekskul']} ({$b['mulai']}–{$b['selesai']}) pada hari {$hari}";
    }

    /**
     * @param  Collection<int, PendaftaranEkskul>  $pendaftarans
     * @return Collection<int, array{ekskul_id: int, ekskul: string, hari: string, mulai: string, selesai: string, peran: string}>
     */
    private function jadwalDariPendaftaran(Collection $pendaftarans): Collection
    {
        return $pendaftarans
            ->filter(fn (PendaftaranEkskul $p) => $p->ekstrakurikuler !== null)
            ->flatMap(fn (PendaftaranEkskul $p) => $p->ekstrakurikuler->haris->map(
                fn (EkskulHari $h) => self::barisJadwal($p->ekstrakurikuler->id, $p->ekstrakurikuler->nama_ekstrakurikuler, $h, 'Anggota')
            ))
            ->values();
    }
}
