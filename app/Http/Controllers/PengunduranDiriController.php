<?php

namespace App\Http\Controllers;

use App\Models\EkskulPembina;
use App\Models\EkskulPengunduranDiri;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Services\KeanggotaanService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PengunduranDiriController extends Controller
{
    private const PESAN_ALASAN = [
        'alasan.required' => 'Alasan wajib diisi.',
        'alasan.min'      => 'Alasan minimal 10 karakter supaya jelas.',
        'alasan.max'      => 'Alasan maksimal 1000 karakter.',
    ];

    // Panel keputusan untuk admin & pembina
    public function index(Request $request)
    {
        $user = $request->user();

        $menunggu = EkskulPengunduranDiri::with(['ekstrakurikuler', 'user', 'pengaju'])
            ->menungguUntuk($user)
            ->oldest()
            ->get();

        $riwayat = EkskulPengunduranDiri::with(['ekstrakurikuler', 'user', 'pengaju', 'pemutus'])
            ->where('status', '!=', 'menunggu')
            ->when(!$user->isAdmin(), fn ($q) => $q->whereIn('ekstrakurikuler_id', $user->idEkskulDikelola()))
            ->latest('diputuskan_pada')
            ->limit(50)
            ->get();

        // Tugas admin: ekskul aktif yang tidak punya pembina bertugas -> perlu pengganti
        $tanpaPembina = $user->isAdmin()
            ? Ekstrakurikuler::where('status', 'aktif')
                ->whereNotIn('id', EkskulPembina::whereNull('selesai')->select('ekstrakurikuler_id'))
                ->orderBy('nama_ekstrakurikuler')
                ->get()
            : collect();

        return view('pengunduran.index', compact('menunggu', 'riwayat', 'tanpaPembina'));
    }

    // Siswa mengajukan keluar dari ekskul
    public function ajukanAnggota(Request $request, Ekstrakurikuler $ekstrakurikuler, KeanggotaanService $service)
    {
        $data = $request->validate(['alasan' => 'required|string|min:10|max:1000'], self::PESAN_ALASAN);

        $pendaftaran = PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('user_id', $request->user()->id)
            ->latest()
            ->first();

        if (!$pendaftaran) {
            return back()->with('error', 'Kamu bukan anggota aktif ekskul ini.');
        }

        try {
            $service->ajukanAnggota($pendaftaran, $request->user(), $data['alasan']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan pengunduran diri terkirim. Statusnya sah setelah disetujui pembina atau admin.');
    }

    // Pembina mengajukan keluar (disetujui admin)
    public function ajukanPembina(Request $request, Ekstrakurikuler $ekstrakurikuler, KeanggotaanService $service)
    {
        Gate::authorize('kelola', $ekstrakurikuler);

        $data = $request->validate(['alasan' => 'required|string|min:10|max:1000'], self::PESAN_ALASAN);

        try {
            $service->ajukanPembina($ekstrakurikuler, $request->user(), $data['alasan']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan pengunduran diri terkirim. Statusnya sah setelah disetujui admin.');
    }

    // Admin/pembina mengeluarkan anggota
    public function keluarkan(Request $request, PendaftaranEkskul $pendaftaran, KeanggotaanService $service)
    {
        Gate::authorize('kelola', $pendaftaran->ekstrakurikuler);

        $data = $request->validate(['alasan' => 'required|string|min:10|max:1000'], self::PESAN_ALASAN);

        try {
            $service->keluarkan($pendaftaran, $request->user(), $data['alasan']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $pendaftaran->nama_pendaftar . ' dikeluarkan dari ekskul.');
    }

    // Setujui / tolak pengajuan
    public function putuskan(Request $request, EkskulPengunduranDiri $pengunduran, KeanggotaanService $service)
    {
        Gate::authorize('putuskan', $pengunduran);

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['setuju', 'tolak'])],
            'catatan'   => ['nullable', 'string', 'max:1000', Rule::requiredIf($request->input('keputusan') === 'tolak')],
        ], [
            'catatan.required' => 'Beri catatan alasan penolakan.',
        ]);

        $setuju = $data['keputusan'] === 'setuju';

        try {
            $service->putuskan($pengunduran, $request->user(), $setuju, $data['catatan'] ?? null);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $pesan = $setuju ? 'Pengajuan disetujui.' : 'Pengajuan ditolak.';

        // Pembina terakhir keluar -> ingatkan admin untuk mencari pengganti
        if ($setuju && $pengunduran->peran === 'pembina'
            && !EkskulPembina::where('ekstrakurikuler_id', $pengunduran->ekstrakurikuler_id)->whereNull('selesai')->exists()) {
            $pesan .= ' Ekskul ' . $pengunduran->ekstrakurikuler->nama_ekstrakurikuler . ' sekarang belum punya pembina. Tugaskan pengganti lewat Edit Ekskul.';
        }

        return back()->with('success', $pesan);
    }

    // Pengaju membatalkan pengajuannya sendiri
    public function batalkan(EkskulPengunduranDiri $pengunduran)
    {
        Gate::authorize('batalkan', $pengunduran);

        $pengunduran->delete();

        return back()->with('success', 'Pengajuan pengunduran diri dibatalkan.');
    }
}