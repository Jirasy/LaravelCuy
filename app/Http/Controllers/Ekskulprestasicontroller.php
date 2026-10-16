<?php

namespace App\Http\Controllers;

use App\Models\EkskulPrestasi;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Hanya admin & pembina (dijaga di route)
class EkskulPrestasiController extends Controller
{
    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $data = $this->validasi($request, $ekstrakurikuler->id);
        $anggota = $data['anggota'] ?? [];
        unset($data['anggota']);

        $prestasi = $ekstrakurikuler->prestasi()->create($data);
        $prestasi->anggota()->sync($anggota);

        return $this->kembali($ekstrakurikuler->id, 'Prestasi berhasil ditambahkan!');
    }

    public function edit(EkskulPrestasi $prestasi)
    {
        $prestasi->load('anggota');
        $anggotaOpsi = PendaftaranEkskul::where('ekstrakurikuler_id', $prestasi->ekstrakurikuler_id)
            ->where('status', 'diterima')
            ->orderBy('nama_pendaftar')
            ->get();

        return view('ekskul_prestasi.edit', compact('prestasi', 'anggotaOpsi'));
    }

    public function update(Request $request, EkskulPrestasi $prestasi)
    {
        $data = $this->validasi($request, $prestasi->ekstrakurikuler_id);
        $anggota = $data['anggota'] ?? [];
        unset($data['anggota']);

        $prestasi->update($data);
        $prestasi->anggota()->sync($anggota);

        return $this->kembali($prestasi->ekstrakurikuler_id, 'Prestasi berhasil diperbarui!');
    }

    public function destroy(EkskulPrestasi $prestasi)
    {
        $ekskulId = $prestasi->ekstrakurikuler_id;
        $prestasi->delete();

        return $this->kembali($ekskulId, 'Prestasi berhasil dihapus!');
    }

    private function validasi(Request $request, int $ekskulId): array
    {
        return $request->validate([
            'judul'         => 'required|string|max:255',
            'tingkat'       => ['required', Rule::in(array_keys(EkskulPrestasi::TINGKAT))],
            'peringkat'     => 'required|string|max:100',
            'tahun'         => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'penyelenggara' => 'nullable|string|max:255',
            'deskripsi'     => 'nullable|string',
            'anggota'       => 'nullable|array',
            // Hanya pendaftar milik ekskul ini yang boleh dipilih
            'anggota.*'     => [Rule::exists('pendaftaran_ekskuls', 'id')->where('ekstrakurikuler_id', $ekskulId)],
        ]);
    }

    private function kembali(int $ekskulId, string $pesan)
    {
        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskulId, 'tab' => 'prestasi'])
            ->with('success', $pesan);
    }
}