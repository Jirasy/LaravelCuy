<?php

namespace App\Http\Controllers;

use App\Models\EkskulTugas;
use App\Models\EkskulTugasPenerima;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EkskulTugasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $semua = EkskulTugasPenerima::with(['tugas.pembuat', 'tugas.ekstrakurikuler'])
            ->where('user_id', $user->id)
            ->get();

        $terbuka = $semua->where('status', '!=', 'selesai')
            ->sortBy(fn ($p) => optional($p->tugas->tenggat)->timestamp ?? PHP_INT_MAX)
            ->values();

        $selesai = $semua->where('status', 'selesai')->sortByDesc('selesai_pada')->values()->take(20);

        $diberikan = EkskulTugas::with(['penerimas.user', 'ekstrakurikuler'])
            ->where('dibuat_oleh', $user->id)
            ->latest()
            ->get();

        return view('tugas.index', compact('terbuka', 'selesai', 'diberikan'));
    }

    // Semua user boleh membuat task untuk dirinya sendiri.
    // Admin & pembina juga bisa memberi task ke orang lain (dari halaman ekskul atau Kelola User).
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'judul'              => 'required|string|max:200',
            'deskripsi'          => 'nullable|string|max:2000',
            'tenggat'            => 'nullable|date|after_or_equal:today',
            'ekstrakurikuler_id' => 'nullable|integer|exists:ekstrakurikulers,id',
            'target'             => ['nullable', Rule::in(['semua_anggota', 'pilih'])],
            'penerima'           => 'nullable|array',
            'penerima.*'         => 'integer|exists:users,id',
        ], [
            'judul.required'            => 'Judul task wajib diisi.',
            'tenggat.after_or_equal'    => 'Tenggat tidak boleh di masa lalu.',
        ]);

        $ekskulId = null;
        $penerimaIds = [$user->id];   // bawaan: untuk diri sendiri

        if ($user->isStaff()) {
            $ekskulId = $data['ekstrakurikuler_id'] ?? null;
            $dipilih  = $data['penerima'] ?? [];

            if ($ekskulId) {
                Gate::authorize('kelola', Ekstrakurikuler::findOrFail($ekskulId));

                $anggotaIds = PendaftaranEkskul::anggotaAktif()
                    ->where('ekstrakurikuler_id', $ekskulId)
                    ->whereNotNull('user_id')
                    ->pluck('user_id')->unique()->values()->all();

                $penerimaIds = ($data['target'] ?? 'semua_anggota') === 'semua_anggota'
                    ? $anggotaIds
                    : array_values(array_intersect($dipilih, $anggotaIds));
            } elseif (!empty($dipilih)) {
                $boleh = $this->penerimaBoleh($user);   // null = admin (bebas)

                $penerimaIds = $boleh === null
                    ? User::whereIn('id', $dipilih)->where('is_active', true)->pluck('id')->all()
                    : array_values(array_intersect($dipilih, $boleh));
            }
        }

        if (empty($penerimaIds)) {
            return back()->withInput()->with('error', 'Tidak ada penerima yang valid. Pembina hanya bisa memberi task ke anggota aktif ekskul yang ia bina.');
        }

        $tugas = DB::transaction(function () use ($data, $ekskulId, $user, $penerimaIds) {
            $tugas = EkskulTugas::create([
                'ekstrakurikuler_id' => $ekskulId,
                'judul'              => $data['judul'],
                'deskripsi'          => $data['deskripsi'] ?? null,
                'tenggat'            => $data['tenggat'] ?? null,
                'dibuat_oleh'        => $user->id,
            ]);

            foreach ($penerimaIds as $id) {
                EkskulTugasPenerima::create(['tugas_id' => $tugas->id, 'user_id' => $id]);
            }

            return $tugas;
        });

        // Beri tahu penerima (kecuali pembuatnya sendiri)
        $tenggat = $tugas->tenggat ? ' (tenggat ' . $tugas->tenggat->format('d-m-Y') . ')' : '';
        Pemberitahuan::kirim(
            User::whereIn('id', $penerimaIds)->get(),
            'Task baru',
            "{$user->name} memberimu task: {$tugas->judul}{$tenggat}",
            route('tugas.index', [], false),
            '📝',
            $user->id
        );

        return back()->with('success', count($penerimaIds) > 1 || $penerimaIds[0] !== $user->id
            ? 'Task berhasil diberikan ke ' . count($penerimaIds) . ' orang.'
            : 'Task berhasil ditambahkan.');
    }

    // Penerima mengubah status task miliknya sendiri
    public function status(Request $request, EkskulTugasPenerima $penerima)
    {
        $user = $request->user();
        abort_unless($penerima->user_id === $user->id, 403);

        $data = $request->validate(['status' => ['required', Rule::in(array_keys(EkskulTugasPenerima::STATUS))]]);

        $penerima->update([
            'status'       => $data['status'],
            'selesai_pada' => $data['status'] === 'selesai' ? now() : null,
        ]);

        $tugas = $penerima->tugas;
        if ($data['status'] === 'selesai' && $tugas->pembuat && $tugas->dibuat_oleh !== $user->id) {
            Pemberitahuan::kirim([$tugas->pembuat], 'Task selesai', "{$user->name} menyelesaikan: {$tugas->judul}", route('tugas.index', [], false), '✅');
        }

        return back();
    }

    // Pembuat task (atau admin) boleh menghapus
    public function destroy(Request $request, EkskulTugas $tugas)
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $tugas->dibuat_oleh === $user->id, 403);

        $tugas->delete();

        return back()->with('success', 'Task dihapus.');
    }

    // Pembina hanya boleh memberi task ke anggota aktif ekskul yang ia bina. Admin: bebas (null).
    private function penerimaBoleh(User $pemberi): ?array
    {
        if ($pemberi->isAdmin()) {
            return null;
        }

        return PendaftaranEkskul::anggotaAktif()
            ->whereIn('ekstrakurikuler_id', $pemberi->idEkskulDikelola())
            ->whereNotNull('user_id')
            ->pluck('user_id')->unique()->values()->all();
    }
}