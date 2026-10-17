<?php

namespace App\Http\Controllers;

use App\Models\EkskulAnggotaJabatan;
use App\Models\EkskulJabatan;
use App\Models\PendaftaranEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Memberikan dan mencabut jabatan ke anggota. Hanya admin & pembina ekskul itu.
class AnggotaJabatanController extends Controller
{
    public function store(Request $request, PendaftaranEkskul $pendaftaran)
    {
        Gate::authorize('kelola', $pendaftaran->ekstrakurikuler);

        $data = $request->validate([
            'jabatan_id'    => ['required', Rule::exists('ekskul_jabatans', 'id')->where('ekstrakurikuler_id', $pendaftaran->ekstrakurikuler_id)],
            'periode_mulai' => 'required|integer|min:1990|max:' . (date('Y') + 1),
        ], [
            'jabatan_id.required' => 'Pilih jabatan yang akan diberikan.',
            'jabatan_id.exists'   => 'Jabatan tidak ditemukan di ekskul ini.',
        ]);

        if ($pendaftaran->status !== 'diterima') {
            return $this->kembali($pendaftaran, null, 'Jabatan hanya bisa diberikan ke anggota yang pendaftarannya sudah disetujui.');
        }

        $jabatan = EkskulJabatan::findOrFail($data['jabatan_id']);

        $sudahMemegang = EkskulAnggotaJabatan::where('pendaftaran_id', $pendaftaran->id)
            ->where('jabatan_id', $jabatan->id)
            ->whereNull('periode_selesai')
            ->exists();

        if ($sudahMemegang) {
            return $this->kembali($pendaftaran, null, "{$pendaftaran->nama_pendaftar} sudah memegang jabatan {$jabatan->label_lengkap}.");
        }

        if ($jabatan->kuota !== null && $jabatan->pemegangAktif()->count() >= $jabatan->kuota) {
            return $this->kembali($pendaftaran, null, "Kuota jabatan {$jabatan->label_lengkap} sudah penuh ({$jabatan->kuota} orang).");
        }

        EkskulAnggotaJabatan::create([
            'pendaftaran_id'  => $pendaftaran->id,
            'jabatan_id'      => $jabatan->id,
            'periode_mulai'   => $data['periode_mulai'],
            'ditetapkan_oleh' => Auth::id(),
        ]);

        return $this->kembali($pendaftaran, "Jabatan {$jabatan->label_lengkap} diberikan ke {$pendaftaran->nama_pendaftar}.");
    }

    // Mencabut = mengakhiri periode (riwayat tetap tersimpan)
    public function destroy(EkskulAnggotaJabatan $penetapan)
    {
        $pendaftaran = $penetapan->pendaftaran;
        Gate::authorize('kelola', $pendaftaran->ekstrakurikuler);

        if ($penetapan->periode_selesai === null) {
            // Periode selesai tidak boleh lebih kecil dari periode mulai
            $penetapan->update(['periode_selesai' => max((int) date('Y'), (int) $penetapan->periode_mulai)]);
        }

        return $this->kembali($pendaftaran, 'Jabatan dicabut. Riwayatnya tetap tersimpan.');
    }

    private function kembali(PendaftaranEkskul $pendaftaran, ?string $sukses, ?string $galat = null)
    {
        $redirect = redirect()->route('ekstrakurikuler.show', ['id' => $pendaftaran->ekstrakurikuler_id, 'tab' => 'anggota']);

        return $galat ? $redirect->with('error', $galat) : $redirect->with('success', $sukses);
    }
}