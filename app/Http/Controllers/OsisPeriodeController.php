<?php

namespace App\Http\Controllers;

use App\Models\CasisPendaftaran;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use App\Services\OsisService;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Mengatur periode rekrutmen OSIS dan senior penilai. Hanya admin & pembina OSIS.
class OsisPeriodeController extends Controller
{
    public function store(Request $request, OsisService $osis)
    {
        $ekskul = $osis->osis();
        abort_unless($ekskul, 404, 'Belum ada ekskul bertipe OSIS. Admin bisa menandainya lewat Edit Ekskul.');
        Gate::authorize('kelola', $ekskul);

        $data = $request->validate([
            'nama'       => 'required|string|max:20',
            'buka_pada'  => 'nullable|date',
            'tutup_pada' => 'nullable|date|after_or_equal:buka_pada',
        ], ['nama.max' => 'Nama periode maksimal 20 karakter, mis. 2026/2027.']);

        if (OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)->where('nama', $data['nama'])->exists()) {
            return back()->withInput()->with('error', 'Periode dengan nama itu sudah ada.');
        }

        if (OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)->where('status', 'rekrutmen')->exists()) {
            return back()->withInput()->with('error', 'Masih ada periode yang dalam tahap pendaftaran. Selesaikan tahapnya dulu sebelum membuat periode baru.');
        }

        $periode = OsisPeriode::create($data + ['ekstrakurikuler_id' => $ekskul->id, 'status' => 'rekrutmen']);

        return redirect()->route('casis.index', ['periode' => $periode->id])->with('success', "Periode {$periode->nama} dibuat. Pendaftaran CaSis dibuka.");
    }

    // Maju satu tahap: rekrutmen -> seleksi -> berjalan -> selesai
    public function status(Request $request, OsisPeriode $periode)
    {
        Gate::authorize('kelola', $periode->ekstrakurikuler);

        $data = $request->validate(['aksi' => ['required', Rule::in(['seleksi', 'berjalan', 'selesai'])]]);

        $urutan = ['rekrutmen' => 0, 'seleksi' => 1, 'berjalan' => 2, 'selesai' => 3];
        $tujuan = $data['aksi'];

        if ($urutan[$tujuan] !== $urutan[$periode->status] + 1) {
            return back()->with('error', 'Tahap harus berurutan: pendaftaran, seleksi, berjalan, lalu selesai.');
        }

        if ($tujuan === 'berjalan') {
            $belum = $periode->casis()->where('status', 'diajukan')->count();

            if ($belum > 0) {
                return back()->with('error', "Masih ada {$belum} pendaftar yang belum diputuskan.");
            }
        }

        $periode->update(['status' => $tujuan]);

        if ($tujuan === 'seleksi') {
            $jumlah  = $periode->casis()->where('status', 'diajukan')->count();
            $penilai = Pemberitahuan::pengelola($periode->ekstrakurikuler)->merge($periode->seniors);

            Pemberitahuan::kirim(
                $penilai,
                'Seleksi CaSis dimulai',
                "Seleksi CaSis {$periode->nama} dimulai: {$jumlah} pendaftar menunggu penilaian.",
                route('casis.index', ['periode' => $periode->id], false),
                '🗳️',
                $request->user()->id
            );
        }

        return back()->with('success', 'Periode ' . $periode->nama . ' sekarang: ' . OsisPeriode::STATUS[$tujuan] . '.');
    }

    // Menunjuk senior (pengurus sebelumnya) sebagai penilai
    public function seniorTambah(Request $request, OsisPeriode $periode)
    {
        Gate::authorize('kelola', $periode->ekstrakurikuler);

        if ($periode->status === 'selesai') {
            return back()->with('error', 'Periode ini sudah selesai.');
        }

        $data = $request->validate(['user_id' => 'required|integer|exists:users,id'], ['user_id.required' => 'Pilih seniornya.']);
        $user = User::findOrFail($data['user_id']);

        $pengurusAktif = PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $periode->ekstrakurikuler_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$pengurusAktif) {
            return back()->with('error', 'Senior harus pengurus OSIS yang masih aktif.');
        }

        if (CasisPendaftaran::where('osis_periode_id', $periode->id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Pendaftar CaSis tidak boleh menjadi penilai di periode yang sama.');
        }

        if ($periode->seniors()->where('users.id', $user->id)->exists()) {
            return back()->with('error', "{$user->name} sudah menjadi penilai.");
        }

        $periode->seniors()->attach($user->id);

        Pemberitahuan::kirim(
            [$user],
            'Ditunjuk sebagai penilai CaSis',
            "Kamu ditunjuk sebagai senior penilai CaSis OSIS periode {$periode->nama}.",
            route('casis.index', ['periode' => $periode->id], false),
            '🗳️',
            $request->user()->id
        );

        return back()->with('success', "{$user->name} ditunjuk sebagai penilai.");
    }

    public function seniorHapus(OsisPeriode $periode, User $user)
    {
        Gate::authorize('kelola', $periode->ekstrakurikuler);

        $periode->seniors()->detach($user->id);

        return back()->with('success', "{$user->name} tidak lagi menjadi penilai.");
    }
}