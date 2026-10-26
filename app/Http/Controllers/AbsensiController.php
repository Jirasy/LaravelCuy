<?php

namespace App\Http\Controllers;

use App\Models\EkskulKehadiran;
use App\Models\EkskulPertemuan;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Mencatat pertemuan dan kehadiran. Hanya admin & pembina ekskul itu.
class AbsensiController extends Controller
{
    // Membuat pertemuan baru lalu langsung ke lembar absensinya
    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('kelola', $ekstrakurikuler);

        $data = $request->validate($this->aturanPertemuan(), $this->pesan());

        $pertemuan = $ekstrakurikuler->pertemuans()->create($data + ['dibuat_oleh' => Auth::id()]);

        return redirect()->route('absensi.edit', $pertemuan->id)
            ->with('success', 'Pertemuan dibuat. Isi kehadiran anggota di bawah ini.');
    }

    // Lembar absensi: daftar anggota + pilihan status
    public function edit(EkskulPertemuan $pertemuan)
    {
        $ekskul = $pertemuan->ekstrakurikuler;
        Gate::authorize('kelola', $ekskul);

        $kehadiran = $pertemuan->kehadirans()->get()->keyBy('pendaftaran_id');

        // Anggota aktif + anggota yang sudah keluar tapi pernah tercatat di pertemuan ini
        $anggota = PendaftaranEkskul::where('ekstrakurikuler_id', $ekskul->id)
            ->where('status', 'diterima')
            ->where(function ($q) use ($kehadiran) {
                $q->where('status_anggota', 'aktif')->orWhereIn('id', $kehadiran->keys());
            })
            ->orderBy('nama_pendaftar')
            ->get();

        return view('absensi.edit', compact('pertemuan', 'ekskul', 'anggota', 'kehadiran'));
    }

    public function update(Request $request, EkskulPertemuan $pertemuan)
    {
        $ekskul = $pertemuan->ekstrakurikuler;
        Gate::authorize('kelola', $ekskul);

        $data = $request->validate($this->aturanPertemuan() + [
            'status'    => 'nullable|array',
            'status.*'  => ['required', Rule::in(array_keys(EkskulKehadiran::STATUS))],
            'catatan'   => 'nullable|array',
            'catatan.*' => 'nullable|string|max:200',
        ], $this->pesan());

        // Hanya pendaftar milik ekskul ini yang boleh dicatat
        $boleh = PendaftaranEkskul::where('ekstrakurikuler_id', $ekskul->id)
            ->where('status', 'diterima')
            ->pluck('id')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($pertemuan, $data, $boleh) {
            $pertemuan->update(Arr::only($data, ['tanggal', 'jam_mulai', 'jam_selesai', 'topik']));

            foreach (($data['status'] ?? []) as $pendaftaranId => $status) {
                if (!in_array((int) $pendaftaranId, $boleh, true)) {
                    continue;
                }

                EkskulKehadiran::updateOrCreate(
                    ['pertemuan_id' => $pertemuan->id, 'pendaftaran_id' => (int) $pendaftaranId],
                    ['status' => $status, 'catatan' => $data['catatan'][$pendaftaranId] ?? null]
                );
            }
        });

        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'absensi'])
            ->with('success', 'Absensi pertemuan ' . $pertemuan->tanggal->format('d-m-Y') . ' disimpan.');
    }

    public function destroy(EkskulPertemuan $pertemuan)
    {
        $ekskul = $pertemuan->ekstrakurikuler;
        Gate::authorize('kelola', $ekskul);

        $pertemuan->delete();   // catatan kehadirannya ikut terhapus

        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'absensi'])
            ->with('success', 'Pertemuan dihapus.');
    }

    private function aturanPertemuan(): array
    {
        return [
            'tanggal'     => 'required|date|before_or_equal:today',
            'jam_mulai'   => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'topik'       => 'nullable|string|max:200',
        ];
    }

    private function pesan(): array
    {
        return [
            'tanggal.before_or_equal' => 'Tanggal pertemuan tidak boleh di masa depan.',
            'jam_selesai.after'       => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}