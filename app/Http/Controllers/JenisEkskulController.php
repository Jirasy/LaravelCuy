<?php

namespace App\Http\Controllers;

use App\Models\JenisEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// Mengatur tipe ekskul (Ekskul Biasa, Ekskul Seleksi, Organisasi, Komunitas, dan tipe tambahan). Hanya admin (dijaga di routes/web.php).
class JenisEkskulController extends Controller
{
    public function index()
    {
        $daftar = JenisEkskul::withCount('ekskuls')->urut()->get();

        return view('jenis-ekskul.index', compact('daftar'));
    }

    private function aturan(?JenisEkskul $jenis = null): array
    {
        return [
            'nama'       => ['required', 'string', 'max:60', Rule::unique('jenis_ekskuls', 'nama')->ignore($jenis?->id)],
            'urutan'     => ['nullable', 'integer', 'min:0', 'max:99'],
            'alur_pendaftaran' => ['nullable', Rule::in(array_keys(JenisEkskul::ALUR))],
            'kategori'   => ['nullable', Rule::in(['ekskul', 'organisasi'])],
            'hanya_satu' => ['nullable', 'boolean'],
            'aktif'      => ['nullable', 'boolean'],
        ];
    }

    private function pesan(): array
    {
        return [
            'nama.required' => 'Isi nama tipe ekskul.',
            'nama.max'      => 'Nama tipe maksimal 60 karakter.',
            'nama.unique'   => 'Tipe dengan nama itu sudah ada.',
            'urutan.integer' => 'Urutan harus berupa angka.',
            'urutan.min'    => 'Urutan minimal 0.',
            'urutan.max'    => 'Urutan maksimal 99.',
        ];
    }

    // Kode unik dari nama (dipakai sebagai pengenal tetap, tidak berubah walau nama diganti)
    private function buatKode(string $nama): string
    {
        $dasar = Str::limit(Str::slug($nama, '_') ?: 'jenis', 24, '');
        $kode  = $dasar;
        $i     = 2;

        while (JenisEkskul::where('kode', $kode)->exists()) {
            $kode = $dasar . '_' . $i++;
        }

        return $kode;
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        JenisEkskul::create([
            'kode'             => $this->buatKode($data['nama']),
            'nama'             => $data['nama'],
            'alur_pendaftaran' => $data['alur_pendaftaran'] ?? 'langsung',
            'kategori'         => $data['kategori'] ?? 'ekskul',   // komunitas hanya tipe bawaan
            'hanya_satu'       => $request->boolean('hanya_satu'),
            'aktif'            => true,
            'bawaan'           => false,
            'urutan'           => $data['urutan'] ?? 50,
        ]);

        return back()->with('success', "Tipe {$data['nama']} ditambahkan.");
    }

    public function update(Request $request, JenisEkskul $jenis)
    {
        $data = $request->validate($this->aturan($jenis), $this->pesan());

        $hanyaSatu = $request->boolean('hanya_satu');
        $aktif     = $request->boolean('aktif');

        if ($jenis->bawaan) {
            $aktif = true;       // tipe bawaan sistem selalu aktif
        }

        if ($hanyaSatu && ! $jenis->hanya_satu) {
            $jumlah = $jenis->ekskuls()->count();

            if ($jumlah > 1) {
                return back()->withInput()->with('error', "Sudah ada {$jumlah} ekskul bertipe {$jenis->nama}. Sisakan satu dulu sebelum menandainya \"hanya satu\".");
            }
        }

        $perubahan = [
            'nama'       => $data['nama'],
            'urutan'     => $data['urutan'] ?? $jenis->urutan,
            'hanya_satu' => $hanyaSatu,
            'aktif'      => $aktif,
        ];

        // Cara masuk & kategori tipe bawaan tidak bisa diubah (perilaku sistem bergantung padanya)
        if (! $jenis->bawaan) {
            $perubahan['alur_pendaftaran'] = $data['alur_pendaftaran'] ?? $jenis->alur_pendaftaran;
            $perubahan['kategori']         = $data['kategori'] ?? $jenis->kategori;
        }

        $jenis->update($perubahan);

        return back()->with('success', "Tipe {$jenis->nama} diperbarui.");
    }

    public function destroy(JenisEkskul $jenis)
    {
        if ($jenis->bawaan) {
            return back()->with('error', 'Tipe bawaan sistem tidak bisa dihapus.');
        }

        $dipakai = $jenis->ekskuls()->count();

        if ($dipakai > 0) {
            return back()->with('error', "Tipe {$jenis->nama} masih dipakai {$dipakai} ekskul. Pindahkan ekskulnya ke tipe lain dulu, atau cukup nonaktifkan tipenya.");
        }

        $nama = $jenis->nama;
        $jenis->delete();

        return back()->with('success', "Tipe {$nama} dihapus.");
    }
}
