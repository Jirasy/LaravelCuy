<?php

namespace App\Services;

use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel as TipeExcel;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Smalot\PdfParser\Parser;
use ZipArchive;

/**
 * Membaca daftar siswa dari berkas unggahan lalu menyaring kolom yang relevan saja.
 *
 * - xlsx/xls  : Maatwebsite\Excel (sudah terpasang)
 * - csv/txt   : dibaca sendiri (pemisah , ; tab | dideteksi otomatis; Excel Indonesia sering memakai ;)
 * - docx      : dibaca langsung dari word/document.xml (paragraf dan tabel), tanpa paket tambahan
 * - pdf       : hanya aktif bila paket smalot/pdfparser terpasang (composer require smalot/pdfparser)
 *
 * Kolom yang dipakai: Nama (wajib), NISN, Kelas, Email (opsional). Kolom lain (mis. nomor kelompok CAMP) diabaikan.
 */
class DaftarSiswaPembaca
{
    public const MAKS_BARIS = 500;

    private const EKSTENSI_DASAR = ['xlsx', 'xls', 'csv', 'txt', 'docx'];

    private const POLA_JUDUL_NAMA = '/^(nama(\s+(lengkap|siswa|murid|peserta|santri))?|name|full\s*name|siswa|murid|peserta)$/iu';

    private const POLA_JUDUL_NISN = '/^nisn$/iu';

    private const POLA_JUDUL_KELAS = '/^kelas$/iu';

    private const POLA_JUDUL_EMAIL = '/^e-?mail$/iu';

    // Kata yang bentuknya mirip nama tetapi sebenarnya judul kolom / label
    private const POLA_BUKAN_NAMA = '/^(no|nomor|nama|name|kelas|kelompok|nisn|jumlah|total|absen|keterangan|ket|jk|jenis kelamin|laki-laki|perempuan)$|^(daftar|rekap|list)\s/iu';

    /**
     * @return list<string>
     */
    public static function ekstensiDidukung(): array
    {
        return class_exists(Parser::class)
            ? [...self::EKSTENSI_DASAR, 'pdf']
            : self::EKSTENSI_DASAR;
    }

    /**
     * @return array{siswa: list<array{nama: string, nisn: ?string, kelas: ?string, email: ?string}>, diabaikan: int}
     */
    public function baca(UploadedFile $berkas): array
    {
        $ekstensi = strtolower($berkas->getClientOriginalExtension());
        $jalur = (string) $berkas->getRealPath();

        $baris = match ($ekstensi) {
            'xlsx', 'xls' => $this->barisSpreadsheet($berkas, $ekstensi),
            'csv', 'txt' => $this->barisTeks((string) file_get_contents($jalur)),
            'docx' => $this->barisDocx($jalur),
            'pdf' => $this->barisPdf($jalur),
            default => throw ValidationException::withMessages(['berkas' => 'Format berkas tidak didukung.']),
        };

        $hasil = $this->ekstrak($baris);

        if (count($hasil['siswa']) > self::MAKS_BARIS) {
            throw ValidationException::withMessages([
                'berkas' => 'Maksimal '.self::MAKS_BARIS.' nama per unggahan, berkasmu berisi '.count($hasil['siswa']).'. Bagi menjadi beberapa berkas.',
            ]);
        }

        return $hasil;
    }

    // ---------------------------------------------------------------------
    // Pembacaan per format -> baris-baris sel (list<list<string>>)
    // ---------------------------------------------------------------------

    /**
     * @return list<list<string>>
     */
    private function barisSpreadsheet(UploadedFile $berkas, string $ekstensi): array
    {
        $tipe = $ekstensi === 'xls' ? TipeExcel::XLS : TipeExcel::XLSX;

        // Objek import kosong: toArray() hanya perlu pembaca, bukan logika import
        $lembar = Excel::toArray(new class {}, $berkas, null, $tipe);

        foreach ($lembar as $baris) {
            $bersih = array_values(array_map(
                fn ($b) => array_map(fn ($sel) => $this->teks($sel), array_values((array) $b)),
                $baris
            ));

            if ($this->adaIsi($bersih)) {
                return $bersih;
            }
        }

        return [];
    }

    /**
     * @return list<list<string>>
     */
    private function barisTeks(string $isi): array
    {
        $isi = $this->keUtf8($isi);

        $garis = array_values(array_filter(
            array_map('trim', preg_split('/\R/u', $isi) ?: []),
            fn ($g) => $g !== ''
        ));

        $pemisah = $this->deteksiPemisah($garis);

        return array_map(function (string $g) use ($pemisah) {
            $sel = $pemisah === null ? [$g] : str_getcsv($g, $pemisah, '"', '');

            return array_map(fn ($s) => $this->teks($s), $sel);
        }, $garis);
    }

    /**
     * @return list<list<string>>
     */
    private function barisDocx(string $jalur): array
    {
        $zip = new ZipArchive;

        if ($zip->open($jalur) !== true) {
            throw new RuntimeException('Berkas docx tidak bisa dibuka.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('Isi docx tidak ditemukan.');
        }

        $dom = new DOMDocument;

        if (! $dom->loadXML($xml, LIBXML_NONET)) {
            throw new RuntimeException('Isi docx rusak.');
        }

        $xp = new DOMXPath($dom);
        $xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $badan = $xp->query('/w:document/w:body')->item(0);
        $baris = [];

        foreach ($badan?->childNodes ?? [] as $node) {
            if ($node->nodeName === 'w:tbl') {
                foreach ($xp->query('./w:tr', $node) as $tr) {
                    $sel = [];
                    foreach ($xp->query('./w:tc', $tr) as $tc) {
                        $sel[] = $this->teks(str_replace("\t", ' ', $this->teksNode($xp, $tc)));
                    }
                    $baris[] = $sel;
                }
            } elseif ($node->nodeName === 'w:p') {
                $baris[] = array_map(fn ($s) => $this->teks($s), explode("\t", $this->teksNode($xp, $node)));
            }
        }

        return $baris;
    }

    /**
     * @return list<list<string>>
     */
    private function barisPdf(string $jalur): array
    {
        if (! class_exists(Parser::class)) {
            throw ValidationException::withMessages([
                'berkas' => 'Pembaca PDF belum dipasang. Gunakan xlsx, csv, txt, atau docx.',
            ]);
        }

        $teks = (new Parser)->parseFile($jalur)->getText();

        return $this->barisTeks($teks);
    }

    // ---------------------------------------------------------------------
    // Menyaring kolom: judul "Nama" bila ada, kalau tidak ditebak dari isi
    // ---------------------------------------------------------------------

    /**
     * @param  list<list<string>>  $baris
     * @return array{siswa: list<array{nama: string, nisn: ?string, kelas: ?string, email: ?string}>, diabaikan: int}
     */
    private function ekstrak(array $baris): array
    {
        $baris = array_values(array_filter($baris, fn ($b) => implode('', $b) !== ''));

        // Bentuk 1: beberapa kelompok berdampingan (No | Nama kelompok | Kelas | No | ...)
        $berdampingan = $this->deteksiBlokBerdampingan($baris);

        if ($berdampingan !== null) {
            $blok = $berdampingan['blok'];
            $mulai = $berdampingan['mulai'];
        } else {
            // Bentuk 2: ada judul kolom "Nama"; bentuk 3: tidak ada judul, kolom nama ditebak dari isi
            $kolom = ['nama' => null, 'nisn' => null, 'kelas' => null, 'email' => null];
            $mulai = 0;

            foreach (array_slice($baris, 0, 15, true) as $i => $b) {
                $peta = $this->petaJudul($b);

                if ($peta['nama'] !== null) {
                    $kolom = $peta;
                    $mulai = $i + 1;
                    break;
                }
            }

            if ($kolom['nama'] === null) {
                $kolom['nama'] = $this->tebakKolomNama($baris);
            }

            $blok = $kolom['nama'] === null ? [] : [$kolom];
        }

        if ($blok === []) {
            return ['siswa' => [], 'diabaikan' => count($baris)];
        }

        $siswa = [];
        $diabaikan = 0;

        foreach ($blok as $kolom) {
            foreach (array_slice($baris, $mulai) as $b) {
                $sel = $b[$kolom['nama']] ?? '';

                if ($sel === '') {
                    continue;
                }

                $nama = $this->bersihkanNama($sel);

                if ($nama === null) {
                    $diabaikan++;

                    continue;
                }

                $siswa[] = [
                    'nama' => $nama,
                    'nisn' => $this->ambilNisn($b[$kolom['nisn'] ?? -1] ?? ''),
                    'kelas' => $this->ambilKelas($b[$kolom['kelas'] ?? -1] ?? ''),
                    'email' => $this->ambilEmail($b[$kolom['email'] ?? -1] ?? ''),
                ];
            }
        }

        return ['siswa' => $siswa, 'diabaikan' => $diabaikan];
    }

    /**
     * Lembar berisi beberapa kelompok yang diletakkan menyamping, mis. 11 kelompok CPD:
     * "No | Kelompok 1 = <pembina> | Kelas | (kosong) | No | Kelompok 2 = ... | Kelas".
     * Dikenali dari judul "No" (atau "Kelas") yang berulang di satu baris; kolom nama = kolom sesudah "No".
     * Judul kolom nama berisi nama pembina/kelompok, jadi tidak pernah dianggap sebagai siswa.
     *
     * @param  list<list<string>>  $baris
     * @return array{blok: list<array{nama: int, nisn: null, kelas: ?int, email: null}>, mulai: int}|null
     */
    private function deteksiBlokBerdampingan(array $baris): ?array
    {
        foreach (array_slice($baris, 0, 15, true) as $i => $b) {
            $noKolom = [];
            $kelasKolom = [];

            foreach ($b as $k => $sel) {
                $judul = mb_strtolower(trim($sel, " \t:."));

                if ($judul === 'no' || $judul === 'nomor') {
                    $noKolom[] = $k;
                } elseif ($judul === 'kelas') {
                    $kelasKolom[] = $k;
                }
            }

            if (count($noKolom) >= 2) {
                $namaKolom = array_map(fn ($k) => $k + 1, $noKolom);
            } elseif (count($kelasKolom) >= 2) {
                $namaKolom = array_filter(array_map(fn ($k) => $k - 1, $kelasKolom), fn ($k) => $k >= 0);
            } else {
                continue;
            }

            $blok = array_map(fn ($k) => [
                'nama' => $k,
                'nisn' => null,
                'kelas' => in_array($k + 1, $kelasKolom, true) ? $k + 1 : null,
                'email' => null,
            ], array_values($namaKolom));

            return ['blok' => $blok, 'mulai' => $i + 1];
        }

        return null;
    }

    /**
     * @param  list<string>  $baris
     * @return array{nama: ?int, nisn: ?int, kelas: ?int, email: ?int}
     */
    private function petaJudul(array $baris): array
    {
        $peta = ['nama' => null, 'nisn' => null, 'kelas' => null, 'email' => null];

        foreach ($baris as $i => $sel) {
            $judul = trim($sel, " \t:.");

            if ($peta['nama'] === null && preg_match(self::POLA_JUDUL_NAMA, $judul)) {
                $peta['nama'] = $i;
            } elseif ($peta['nisn'] === null && preg_match(self::POLA_JUDUL_NISN, $judul)) {
                $peta['nisn'] = $i;
            } elseif ($peta['kelas'] === null && preg_match(self::POLA_JUDUL_KELAS, $judul)) {
                $peta['kelas'] = $i;
            } elseif ($peta['email'] === null && preg_match(self::POLA_JUDUL_EMAIL, $judul)) {
                $peta['email'] = $i;
            }
        }

        return $peta;
    }

    /**
     * Tanpa judul kolom: pilih kolom dengan nilai "mirip nama" paling beragam.
     * Kolom kelompok/kelas hanya punya sedikit nilai unik sehingga kalah.
     *
     * @param  list<list<string>>  $baris
     */
    private function tebakKolomNama(array $baris): ?int
    {
        $unik = [];

        foreach ($baris as $b) {
            foreach ($b as $i => $sel) {
                $nama = $this->bersihkanNama($sel);
                if ($nama !== null) {
                    $unik[$i][mb_strtolower($nama)] = true;
                }
            }
        }

        if ($unik === []) {
            return null;
        }

        $skor = array_map('count', $unik);
        arsort($skor);
        $terbaik = max($skor);

        // Seri -> kolom paling kiri
        return (int) min(array_keys(array_filter($skor, fn ($n) => $n === $terbaik)));
    }

    private function bersihkanNama(string $sel): ?string
    {
        $nama = preg_replace('/^\s*\(?\d{1,4}(?:\s*[\.\)\-:]\s*|\s+)(?=\p{L})/u', '', $sel) ?? $sel;
        $nama = preg_replace('/^[\-\*•·]\s+/u', '', $nama) ?? $nama;
        $nama = preg_replace('/\s*\([^)]*\)\s*$/u', '', $nama) ?? $nama;
        $nama = trim($nama, " \t,;");

        $panjang = mb_strlen($nama);

        if ($panjang < 2 || $panjang > 100) {
            return null;
        }

        // Hanya huruf, spasi, titik, apostrof, dan strip: angka berarti bukan nama
        if (! preg_match('/^\p{L}[\p{L}\p{M}\s\.\'’\-]*$/u', $nama)) {
            return null;
        }

        if (preg_match(self::POLA_BUKAN_NAMA, $nama)) {
            return null;
        }

        // SEMUA HURUF BESAR / kecil semua -> Huruf Awal Besar
        if ($nama === mb_strtoupper($nama) || $nama === mb_strtolower($nama)) {
            $nama = mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8');
        }

        return $nama;
    }

    private function ambilNisn(string $sel): ?string
    {
        $digit = preg_replace('/\D+/', '', $sel) ?? '';

        return strlen($digit) >= 5 && strlen($digit) <= 30 ? $digit : null;
    }

    private function ambilKelas(string $sel): ?string
    {
        $kelas = mb_substr(trim($sel), 0, 50);

        return $kelas === '' ? null : $kelas;
    }

    private function ambilEmail(string $sel): ?string
    {
        $email = mb_strtolower(trim($sel));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    // ---------------------------------------------------------------------
    // Utilitas
    // ---------------------------------------------------------------------

    private function teks(mixed $nilai): string
    {
        if ($nilai instanceof \DateTimeInterface) {
            return $nilai->format('Y-m-d');
        }

        if (! is_scalar($nilai)) {
            return '';
        }

        // Excel menyimpan angka (mis. NISN) sebagai float: 3.0012345678E9 -> 3001234567
        if (is_float($nilai) && floor($nilai) === $nilai) {
            return number_format($nilai, 0, '', '');
        }

        return trim(preg_replace('/\s+/u', ' ', (string) $nilai) ?? '');
    }

    /**
     * @param  list<list<string>>  $baris
     */
    private function adaIsi(array $baris): bool
    {
        foreach ($baris as $b) {
            if (implode('', $b) !== '') {
                return true;
            }
        }

        return false;
    }

    private function keUtf8(string $isi): string
    {
        if (str_starts_with($isi, "\xFF\xFE")) {
            return mb_convert_encoding(substr($isi, 2), 'UTF-8', 'UTF-16LE');
        }

        if (str_starts_with($isi, "\xFE\xFF")) {
            return mb_convert_encoding(substr($isi, 2), 'UTF-8', 'UTF-16BE');
        }

        $isi = preg_replace('/^\xEF\xBB\xBF/', '', $isi) ?? $isi;

        return mb_check_encoding($isi, 'UTF-8') ? $isi : mb_convert_encoding($isi, 'UTF-8', 'Windows-1252');
    }

    /**
     * Pemisah yang muncul di (hampir) semua baris contoh; null = satu kolom saja.
     *
     * @param  list<string>  $garis
     */
    private function deteksiPemisah(array $garis): ?string
    {
        $contoh = array_slice($garis, 0, 20);

        if ($contoh === []) {
            return null;
        }

        $terbaik = null;
        $terbanyak = 0;

        foreach ([';', "\t", '|', ','] as $kandidat) {
            $jumlah = count(array_filter($contoh, fn ($g) => str_contains($g, $kandidat)));

            if ($jumlah > $terbanyak && $jumlah >= ceil(count($contoh) * 0.6)) {
                $terbaik = $kandidat;
                $terbanyak = $jumlah;
            }
        }

        return $terbaik;
    }

    private function teksNode(DOMXPath $xp, DOMNode $node): string
    {
        $hasil = '';

        // w:r/w:tab = karakter tab sungguhan (w:pPr/w:tabs/w:tab hanya definisi posisi tab, diabaikan)
        foreach ($xp->query('.//w:t | .//w:r/w:tab | .//w:r/w:br', $node) as $n) {
            $hasil .= match ($n->localName) {
                't' => $n->textContent,
                'tab' => "\t",
                default => ' ',
            };
        }

        return $hasil;
    }
}
