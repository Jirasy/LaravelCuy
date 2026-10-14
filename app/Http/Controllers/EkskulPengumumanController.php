<?php

namespace App\Http\Controllers;

use App\Models\EkskulPengumuman;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Semua method di sini cuma buat admin & pembina (dijaga di route)
class EkskulPengumumanController extends Controller
{
    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'isi'   => 'required|string',
        ]);

        $ekstrakurikuler->pengumuman()->create($data + ['user_id' => Auth::id()]);

        return $this->kembali($ekstrakurikuler->id, 'Pengumuman berhasil diposting!');
    }

    public function edit(EkskulPengumuman $pengumuman)
    {
        return view('ekskul_pengumuman.edit', compact('pengumuman'));
    }

    public function update(Request $request, EkskulPengumuman $pengumuman)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'isi'   => 'required|string',
        ]);

        $pengumuman->update($data);

        return $this->kembali($pengumuman->ekstrakurikuler_id, 'Pengumuman berhasil diperbarui!');
    }

    public function destroy(EkskulPengumuman $pengumuman)
    {
        $ekskulId = $pengumuman->ekstrakurikuler_id;
        $pengumuman->delete();

        return $this->kembali($ekskulId, 'Pengumuman berhasil dihapus!');
    }

    private function kembali(int $ekskulId, string $pesan)
    {
        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskulId, 'tab' => 'pengumuman'])
            ->with('success', $pesan);
    }
}