<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\EkskulKehadiran;
use App\Models\EkskulNilai;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

// Laporan per ekskul (anggota / absensi / nilai) dalam tiga format: cetak (HTML), PDF, Excel.
// Hanya admin & pembina ekskul itu. PDF dan Excel butuh paket composer (lihat pesan di bawah).
class LaporanController extends Controller
{
    public function unduh(Request $request, Ekstrakurikuler $ekstrakurikuler, string $jenis, string $format)
    {
        Gate::authorize('kelola', $ekstrakurikuler);

        $request->validate([
            'dari'    => 'nullable|date',
            'sampai'  => 'nullable|date|after_or_equal:dari',
            'periode' => 'nullable|string|max:60',
        ]);

        [$judul, $info, $d] = match ($jenis) {
            'anggota' => $this->dataAnggota($ekstrakurikuler),
            'absensi' => $this->dataAbsensi($ekstrakurikuler, $request),
            'nilai'   => $this->dataNilai($ekstrakurikuler, $request),
        };

        $data = [
            'jenis'       => $jenis,
            'judul'       => $judul,
            'info'        => $info,
            'd'           => $d,
            'ekskul'      => $ekstrakurikuler,
            'dicetakOleh' => $request->user()->name,
            'dicetakPada' => now(),
        ];

        $nama = Str::slug($ekstrakurikuler->nama_ekstrakurikuler) . '-' . $jenis . '-' . now()->format('Ymd');

        return match ($format) {
            'cetak' => response()->view('laporan.dokumen', $data + ['mode' => 'cetak', 'query' => $request->query()]),
            'pdf'   => $this->pdf($data, $nama, $jenis),
            'excel' => $this->excel($data, $nama),
        };
    }

    private function pdf(array $data, string $nama, string $jenis)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'Ekspor PDF butuh paket barryvdh/laravel-dompdf. Jalankan: composer require barryvdh/laravel-dompdf. Sementara itu, pakai tombol Cetak lalu simpan sebagai PDF dari browser.');
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.dokumen', $data + ['mode' => 'pdf'])
            ->setPaper('a4', $jenis === 'absensi' ? 'landscape' : 'portrait')
            ->download($nama . '.pdf');
    }

    private function excel(array $data, string $nama)
    {
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return back()->with('error', 'Ekspor Excel butuh paket maatwebsite/excel. Jalankan: composer require maatwebsite/excel');
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new LaporanExport('laporan.excel', $data), $nama . '.xlsx');
    }

    // ---------- penyusun data ----------

    private function dataAnggota(Ekstrakurikuler $e): array
    {
        $baris = PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $e->id)
            ->with('jabatanAktif')
            ->orderBy('nama_pendaftar')
            ->get();

        return [
            'Rekap Anggota Ekskul',
            ["Ekskul: {$e->nama_ekstrakurikuler}", "Pembina: {$e->pembina_tampil}", "Jumlah anggota aktif: {$baris->count()}"],
            ['baris' => $baris],
        ];
    }

    private function dataAbsensi(Ekstrakurikuler $e, Request $request): array
    {
        $dari   = $request->filled('dari') ? Carbon::parse($request->query('dari'))->startOfDay() : now()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->query('sampai'))->endOfDay() : now()->endOfDay();

        $pertemuans = $e->pertemuans()
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->get();

        $kehadiran = EkskulKehadiran::whereIn('pertemuan_id', $pertemuans->pluck('id'))->get()->groupBy('pendaftaran_id');

        $anggota = PendaftaranEkskul::where('ekstrakurikuler_id', $e->id)
            ->where('status', 'diterima')
            ->where(function ($q) use ($kehadiran) {
                $q->where('status_anggota', 'aktif')->orWhereIn('id', $kehadiran->keys());
            })
            ->orderBy('nama_pendaftar')
            ->get();

        $baris = $anggota->map(function ($a) use ($pertemuans, $kehadiran) {
            $punya  = ($kehadiran[$a->id] ?? collect())->keyBy('pertemuan_id');
            $hitung = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
            $sel    = [];

            foreach ($pertemuans as $p) {
                $k = $punya[$p->id] ?? null;
                $sel[$p->id] = $k ? $k->kode : '-';
                if ($k) {
                    $hitung[$k->status]++;
                }
            }

            $tercatat = array_sum($hitung);

            return [
                'nama'   => $a->nama_pendaftar,
                'kelas'  => $a->kelas,
                'sel'    => $sel,
                'H'      => $hitung['hadir'],
                'I'      => $hitung['izin'],
                'S'      => $hitung['sakit'],
                'A'      => $hitung['alpa'],
                'persen' => $tercatat ? round($hitung['hadir'] / $tercatat * 100) : null,
            ];
        });

        return [
            'Rekap Absensi Ekskul',
            [
                "Ekskul: {$e->nama_ekstrakurikuler}",
                'Periode: ' . $dari->format('d-m-Y') . ' s/d ' . $sampai->format('d-m-Y'),
                "Jumlah pertemuan: {$pertemuans->count()}",
                'Keterangan: H = hadir, I = izin, S = sakit, A = alpa, - = belum tercatat',
            ],
            ['pertemuans' => $pertemuans, 'baris' => $baris],
        ];
    }

    private function dataNilai(Ekstrakurikuler $e, Request $request): array
    {
        $idPendaftar = PendaftaranEkskul::where('ekstrakurikuler_id', $e->id)->select('id');

        $periode = trim((string) $request->query('periode'))
            ?: EkskulNilai::whereIn('pendaftaran_id', $idPendaftar)->latest()->value('periode')
            ?: EkskulNilai::periodeSaatIni();

        $nilais = EkskulNilai::whereIn('pendaftaran_id', $idPendaftar)->where('periode', $periode)->get()->keyBy('pendaftaran_id');

        $anggota = PendaftaranEkskul::where('ekstrakurikuler_id', $e->id)
            ->where('status', 'diterima')
            ->where(function ($q) use ($nilais) {
                $q->where('status_anggota', 'aktif')->orWhereIn('id', $nilais->keys());
            })
            ->orderBy('nama_pendaftar')
            ->get();

        $baris = $anggota->map(fn ($a) => ['nama' => $a->nama_pendaftar, 'kelas' => $a->kelas, 'nilai' => $nilais[$a->id] ?? null]);

        return [
            'Rekap Nilai Ekskul',
            ["Ekskul: {$e->nama_ekstrakurikuler}", "Periode: {$periode}", 'Predikat: A >= 90, B >= 80, C >= 70, D >= 60, E < 60'],
            ['baris' => $baris],
        ];
    }
}