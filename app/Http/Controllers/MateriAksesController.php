<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Izin membuka Materi & Task per anggota. Diatur admin & pembina ekskul itu.
class MateriAksesController extends Controller
{
    // Izinkan / cabut satu anggota
    public function toggle(Request $request, PendaftaranEkskul $pendaftaran)
    {
        $ekskul = $pendaftaran->ekstrakurikuler;
        Gate::authorize('kelola', $ekskul);

        if ($pendaftaran->status !== 'diterima' || $pendaftaran->status_anggota !== 'aktif') {
            return back()->with('error', 'Akses hanya bisa diatur untuk anggota aktif.');
        }

        $izin = !$pendaftaran->akses_materi;
        $pendaftaran->update(['akses_materi' => $izin]);

        if ($izin && $pendaftaran->user) {
            Pemberitahuan::kirim(
                [$pendaftaran->user],
                'Akses materi diberikan',
                "Kamu sekarang bisa membuka Materi & Task {$ekskul->nama_ekstrakurikuler}.",
                route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'materi'], false),
                '🔓',
                $request->user()->id
            );
        }

        return back()->with('success', $pendaftaran->nama_pendaftar . ($izin ? ' diberi akses materi.' : ' dicabut akses materinya.'));
    }

    // Izinkan / cabut semua anggota aktif sekaligus
    public function semua(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('kelola', $ekstrakurikuler);

        $data  = $request->validate(['aksi' => ['required', Rule::in(['izinkan', 'cabut'])]]);
        $izin  = $data['aksi'] === 'izinkan';

        $anggota = PendaftaranEkskul::anggotaAktif()->where('ekstrakurikuler_id', $ekstrakurikuler->id);

        $berubah = (clone $anggota)->where('akses_materi', !$izin)->with('user')->get();
        $anggota->update(['akses_materi' => $izin]);

        if ($izin) {
            Pemberitahuan::kirim(
                $berubah->pluck('user'),
                'Akses materi diberikan',
                "Kamu sekarang bisa membuka Materi & Task {$ekstrakurikuler->nama_ekstrakurikuler}.",
                route('ekstrakurikuler.show', ['id' => $ekstrakurikuler->id, 'tab' => 'materi'], false),
                '🔓',
                $request->user()->id
            );
        }

        return back()->with('success', $berubah->count() . ' anggota ' . ($izin ? 'diberi akses materi.' : 'dicabut akses materinya.'));
    }

    // Anggota meminta izin ke pembina/admin
    public function mintaAkses(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $user = $request->user();

        $anggota = PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$anggota) {
            return back()->with('error', 'Hanya anggota aktif yang bisa meminta akses.');
        }

        if ($anggota->akses_materi) {
            return back()->with('error', 'Kamu sudah punya akses materi.');
        }

        Pemberitahuan::kirim(
            Pemberitahuan::pengelola($ekstrakurikuler),
            'Permintaan akses materi',
            "{$user->name} meminta akses Materi & Task {$ekstrakurikuler->nama_ekstrakurikuler}.",
            route('ekstrakurikuler.show', ['id' => $ekstrakurikuler->id, 'tab' => 'materi'], false),
            '🔑',
            $user->id
        );

        return back()->with('success', 'Permintaan akses dikirim ke pembina.');
    }
}