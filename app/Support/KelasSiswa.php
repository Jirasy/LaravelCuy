<?php

namespace App\Support;

// Kelas siswa disimpan sebagai satu teks, mis. "X", "XI PPLG", "XII TKJ 2". Kelas di sini = tingkat (X, XI, XII),
// jurusan = singkatan jurusannya (PPLG, TKJ, ...). Dipakai untuk menyaring daftar user/anggota tanpa kolom tambahan.
class KelasSiswa
{
    // Urutan tampil untuk pilihan tingkat
    private const URUTAN_TINGKAT = ['VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12, 'XIII' => 13];

    /**
     * "XI PPLG 2" -> ['tingkat' => 'XI', 'jurusan' => 'PPLG']; "X" -> ['tingkat' => 'X', 'jurusan' => null].
     * Nomor rombel di belakang jurusan ("2", "-2") dibuang. Teks yang tidak diawali tingkat dikembalikan
     * sebagai jurusan saja.
     *
     * @return array{tingkat: ?string, jurusan: ?string}
     */
    public static function pecah(?string $kelas): array
    {
        $kelas = trim((string) preg_replace('/[\s\-_.]+/u', ' ', mb_strtoupper((string) $kelas)));

        if ($kelas === '') {
            return ['tingkat' => null, 'jurusan' => null];
        }

        $bagian  = explode(' ', $kelas);
        $tingkat = null;

        if (preg_match('/^(XIII|XII|XI|X|IX|VIII|VII|\d{1,2})$/', $bagian[0])) {
            $tingkat = array_shift($bagian);
        }

        // Nomor rombel di akhir bukan bagian dari jurusan
        while ($bagian !== [] && preg_match('/^\d+$/', end($bagian))) {
            array_pop($bagian);
        }

        $jurusan = $bagian === [] ? null : implode(' ', $bagian);

        return ['tingkat' => $tingkat, 'jurusan' => $jurusan];
    }

    public static function tingkat(?string $kelas): ?string
    {
        return self::pecah($kelas)['tingkat'];
    }

    public static function jurusan(?string $kelas): ?string
    {
        return self::pecah($kelas)['jurusan'];
    }

    // Pilihan tingkat unik dari daftar kelas, berurutan (X, XI, XII)
    public static function daftarTingkat(iterable $semuaKelas): array
    {
        $tingkat = collect($semuaKelas)->map(fn ($k) => self::tingkat($k))->filter()->unique()->values();

        return $tingkat->sortBy(fn ($t) => self::URUTAN_TINGKAT[$t] ?? (is_numeric($t) ? (int) $t : 99))->values()->all();
    }

    public static function daftarJurusan(iterable $semuaKelas): array
    {
        return collect($semuaKelas)->map(fn ($k) => self::jurusan($k))->filter()->unique()->sort()->values()->all();
    }

    // Kelas-kelas (teks asli) yang cocok dengan tingkat dan/atau jurusan yang dipilih
    public static function yangCocok(iterable $semuaKelas, ?string $tingkat, ?string $jurusan): array
    {
        return collect($semuaKelas)->filter(function ($k) use ($tingkat, $jurusan) {
            $p = self::pecah($k);

            return ($tingkat === null || $tingkat === '' || $p['tingkat'] === $tingkat)
                && ($jurusan === null || $jurusan === '' || $p['jurusan'] === $jurusan);
        })->values()->all();
    }
}
