<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\EkskulJabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

// Membuat dan mengatur jabatan milik satu ekskul. Hanya admin & pembina ekskul itu.
class EkskulJabatanController extends Controller
{
    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('kelola', $ekstrakurikuler);

        $data = $this->validasi($request, $ekstrakurikuler->id);
        $ekstrakurikuler->jabatans()->create($data);

        return $this->kembali($ekstrakurikuler->id, 'Jabatan berhasil ditambahkan!');
    }

    public function edit(EkskulJabatan $jabatan)
    {
        Gate::authorize('kelola', $jabatan->ekstrakurikuler);

        return view('ekskul_jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, EkskulJabatan $jabatan)
    {
        Gate::authorize('kelola', $jabatan->ekstrakurikuler);

        $data = $this->validasi($request, $jabatan->ekstrakurikuler_id, $jabatan);

        // Kuota baru tidak boleh lebih kecil dari jumlah pemegang saat ini
        $pemegang = $jabatan->pemegangAktif()->count();
        if ($data['kuota'] !== null && $data['kuota'] < $pemegang) {
            throw ValidationException::withMessages([
                'kuota' => "Kuota tidak boleh di bawah jumlah pemegang saat ini ({$pemegang} orang). Cabut jabatan sebagian anggota dulu.",
            ]);
        }

        $jabatan->update($data);

        return $this->kembali($jabatan->ekstrakurikuler_id, 'Jabatan berhasil diperbarui!');
    }

    public function destroy(EkskulJabatan $jabatan)
    {
        Gate::authorize('kelola', $jabatan->ekstrakurikuler);

        // Jabatan yang pernah dipegang tidak dihapus supaya riwayat anggota tidak hilang
        if ($jabatan->penetapans()->exists()) {
            return $this->kembali($jabatan->ekstrakurikuler_id, null, 'Jabatan ini pernah dipegang anggota, jadi tidak bisa dihapus (riwayatnya akan hilang). Kamu bisa mengubah namanya atau mencabut pemegangnya.');
        }

        $ekskulId = $jabatan->ekstrakurikuler_id;
        $jabatan->delete();

        return $this->kembali($ekskulId, 'Jabatan berhasil dihapus!');
    }

    private function validasi(Request $request, int $ekskulId, ?EkskulJabatan $ini = null): array
    {
        $data = $request->validate([
            'nama'           => 'required|string|max:100',
            'varian'         => 'nullable|string|max:20',
            'deskripsi'      => 'nullable|string',
            'tugas'          => 'nullable|string',
            'tanggung_jawab' => 'nullable|string',
            'urutan'         => 'nullable|integer|min:0|max:255',
            'kuota'          => 'nullable|integer|min:1|max:50',
        ]);

        $data['nama']   = trim($data['nama']);
        $data['varian'] = trim((string) ($data['varian'] ?? ''));   // '' = tanpa variasi
        $data['urutan'] = $data['urutan'] ?? 0;
        $data['kuota']  = $data['kuota'] ?? null;

        $kembar = EkskulJabatan::where('ekstrakurikuler_id', $ekskulId)
            ->where('nama', $data['nama'])
            ->where('varian', $data['varian'])
            ->when($ini, fn ($q) => $q->where('id', '!=', $ini->id))
            ->exists();

        if ($kembar) {
            throw ValidationException::withMessages([
                'nama' => 'Jabatan dengan nama dan variasi yang sama sudah ada di ekskul ini. Beri variasi berbeda, mis. "1" dan "2".',
            ]);
        }

        return $data;
    }

    private function kembali(int $ekskulId, ?string $sukses, ?string $galat = null)
    {
        $redirect = redirect()->route('ekstrakurikuler.show', ['id' => $ekskulId, 'tab' => 'struktur']);

        return $galat ? $redirect->with('error', $galat) : $redirect->with('success', $sukses);
    }
}