<?php

namespace App\Http\Controllers;

use App\Models\EkskulPrestasi;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Http\Request;

// Riwayat ekskul & prestasi seorang user. Bisa dilihat pemilik akun, admin, dan pembina.
class RiwayatController extends Controller
{
    public function show(Request $request, ?User $user = null)
    {
        $me   = $request->user();
        $user = $user ?? $me;

        if ($user->id !== $me->id && !$me->isStaff()) {
            abort(403, 'Kamu hanya bisa melihat riwayatmu sendiri.');
        }

        $riwayat = PendaftaranEkskul::with(['ekstrakurikuler', 'jabatanAktif', 'penetapanJabatans.jabatan', 'nilais'])
            ->where('user_id', $user->id)
            ->orderByDesc('tahun_mulai')
            ->orderByDesc('created_at')
            ->get();

        $prestasis = EkskulPrestasi::with('ekstrakurikuler')
            ->whereHas('anggota', fn ($q) => $q->where('pendaftaran_ekskuls.user_id', $user->id))
            ->orderByDesc('tahun')
            ->get();

        return view('riwayat.show', compact('user', 'riwayat', 'prestasis'));
    }
}