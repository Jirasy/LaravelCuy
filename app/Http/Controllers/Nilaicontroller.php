<?php

namespace App\Http\Controllers;

use App\Models\EkskulNilai;
use App\Models\PendaftaranEkskul;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

// Penilaian anggota: HANYA admin & pembina ekskul itu. Siswa hanya melihat nilainya sendiri.
class NilaiController extends Controller
{
    public function edit(Request $request, PendaftaranEkskul $pendaftaran)
    {
        Gate::authorize('kelola', $pendaftaran->ekstrakurikuler);

        $periode = trim((string) $request->query('periode')) ?: EkskulNilai::periodeSaatIni();
        $nilai   = $pendaftaran->nilais()->where('periode', $periode)->first();
        $riwayat = $pendaftaran->nilais()->latest()->get();
        $ekskul  = $pendaftaran->ekstrakurikuler;

        return view('nilai.edit', compact('pendaftaran', 'ekskul', 'periode', 'nilai', 'riwayat'));
    }

    // Menyimpan nilai: kalau periode yang sama sudah ada, nilainya diperbarui
    public function store(Request $request, PendaftaranEkskul $pendaftaran)
    {
        $ekskul = $pendaftaran->ekstrakurikuler;
        Gate::authorize('kelola', $ekskul);

        if ($pendaftaran->status !== 'diterima') {
            return back()->with('error', 'Hanya anggota yang pendaftarannya sudah disetujui yang bisa dinilai.');
        }

        $aturanAspek = 'required|integer|between:0,100';
        $data = $request->validate([
            'periode'      => 'required|string|max:60',
            'keterampilan' => $aturanAspek,
            'disiplin'     => $aturanAspek,
            'kerja_sama'   => $aturanAspek,
            'sikap'        => $aturanAspek,
            'saran'        => 'nullable|string|max:2000',
            'kritik'       => 'nullable|string|max:2000',
        ], [
            'between' => 'Nilai harus antara 0 sampai 100.',
        ]);

        $nilai = EkskulNilai::updateOrCreate(
            ['pendaftaran_id' => $pendaftaran->id, 'periode' => trim($data['periode'])],
            [
                'keterampilan' => $data['keterampilan'],
                'disiplin'     => $data['disiplin'],
                'kerja_sama'   => $data['kerja_sama'],
                'sikap'        => $data['sikap'],
                'saran'        => $data['saran'] ?? null,
                'kritik'       => $data['kritik'] ?? null,
                'penilai_id'   => Auth::id(),
            ]
        );

        if ($pendaftaran->user) {
            Pemberitahuan::kirim(
                [$pendaftaran->user],
                'Nilai ekskul baru',
                "Nilai {$nilai->periode} di {$ekskul->nama_ekstrakurikuler}: rata-rata " . number_format($nilai->rata_rata, 1) . " (predikat {$nilai->predikat}).",
                route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'nilai'], false),
                '📊',
                Auth::id()
            );
        }

        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'nilai'])
            ->with('success', 'Nilai ' . $pendaftaran->nama_pendaftar . ' (' . $nilai->periode . ') disimpan.');
    }

    public function destroy(EkskulNilai $nilai)
    {
        $ekskul = $nilai->pendaftaran->ekstrakurikuler;
        Gate::authorize('kelola', $ekskul);

        $nilai->delete();

        return back()->with('success', 'Nilai dihapus.');
    }
}