<?php

namespace App\Http\Controllers;

use App\Models\EkskulAnggotaJabatan;
use App\Models\EkskulJabatan;
use App\Models\PendaftaranEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Memberikan dan mencabut jabatan ke anggota. Hanya admin & pembina ekskul itu.
class AnggotaJabatanController extends Controller
{
    public const MAKS_SEKALI_BERI = 20;

    public function store(Request $request, PendaftaranEkskul $pendaftaran)
    {
        Gate::authorize('kelola', $pendaftaran->ekstrakurikuler);

        // Satu anggota boleh memegang banyak jabatan sekaligus (mis. Sekretaris + Koordinator Humas)
        $data = $request->validate([
            'jabatan_ids' => ['required', 'array', 'min:1', 'max:'.self::MAKS_SEKALI_BERI],
            'jabatan_ids.*' => ['integer', 'distinct', Rule::exists('ekskul_jabatans', 'id')->where('ekstrakurikuler_id', $pendaftaran->ekstrakurikuler_id)],
            'periode_mulai' => 'required|integer|min:1990|max:'.(date('Y') + 1),
        ], [
            'jabatan_ids.required' => 'Pilih minimal satu jabatan yang akan diberikan.',
            'jabatan_ids.min' => 'Pilih minimal satu jabatan yang akan diberikan.',
            'jabatan_ids.max' => 'Maksimal '.self::MAKS_SEKALI_BERI.' jabatan sekali beri.',
            'jabatan_ids.*.exists' => 'Ada jabatan yang tidak ditemukan di ekskul ini.',
            'jabatan_ids.*.distinct' => 'Ada jabatan yang dipilih dua kali.',
        ]);

        if ($pendaftaran->status !== 'diterima') {
            return $this->kembali($pendaftaran, null, 'Jabatan hanya bisa diberikan ke anggota yang pendaftarannya sudah disetujui.');
        }

        // Satu transaksi: jabatan dikunci dulu supaya pengecekan kuota tidak bisa "tersalip" permintaan lain
        [$diberikan, $dilewati] = DB::transaction(function () use ($data, $pendaftaran) {
            $diberikan = [];
            $dilewati = [];

            $jabatans = EkskulJabatan::whereIn('id', $data['jabatan_ids'])
                ->orderBy('urutan')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($jabatans as $jabatan) {
                $sudahMemegang = EkskulAnggotaJabatan::where('pendaftaran_id', $pendaftaran->id)
                    ->where('jabatan_id', $jabatan->id)
                    ->whereNull('periode_selesai')
                    ->exists();

                if ($sudahMemegang) {
                    $dilewati[] = "{$jabatan->label_lengkap} (sudah dipegang)";

                    continue;
                }

                if ($jabatan->kuota !== null && $jabatan->pemegangAktif()->count() >= $jabatan->kuota) {
                    $dilewati[] = "{$jabatan->label_lengkap} (kuota {$jabatan->kuota} orang sudah penuh)";

                    continue;
                }

                EkskulAnggotaJabatan::create([
                    'pendaftaran_id' => $pendaftaran->id,
                    'jabatan_id' => $jabatan->id,
                    'periode_mulai' => $data['periode_mulai'],
                    'ditetapkan_oleh' => Auth::id(),
                ]);

                $diberikan[] = $jabatan->label_lengkap;
            }

            return [$diberikan, $dilewati];
        });

        return $this->kembali(
            $pendaftaran,
            $diberikan ? 'Jabatan '.implode(', ', $diberikan)." diberikan ke {$pendaftaran->nama_pendaftar}." : null,
            $dilewati ? 'Dilewati: '.implode(', ', $dilewati).'.' : null,
        );
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

    // Sukses dan galat bisa tampil bersamaan (mis. 2 jabatan diberikan, 1 dilewati karena kuota penuh)
    private function kembali(PendaftaranEkskul $pendaftaran, ?string $sukses, ?string $galat = null)
    {
        $redirect = redirect()->route('ekstrakurikuler.show', ['id' => $pendaftaran->ekstrakurikuler_id, 'tab' => 'anggota']);

        if ($sukses) {
            $redirect->with('success', $sukses);
        }

        if ($galat) {
            $redirect->with('error', $galat);
        }

        return $redirect;
    }
}
