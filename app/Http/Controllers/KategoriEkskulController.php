<?php

namespace App\Http\Controllers;

use App\Models\KategoriEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// Mengatur kategori/label ekskul (Bela Diri, Seni & Olahraga, dst). Hanya admin (dijaga di routes/web.php).
class KategoriEkskulController extends Controller
{
    public function index()
    {
        $daftar = KategoriEkskul::withCount('ekskuls')->urut()->get();

        return view('kategori-ekskul.index', compact('daftar'));
    }

    private function aturan(?KategoriEkskul $kategori = null): array
    {
        return [
            'nama' => ['required', 'string', 'max:60', Rule::unique('kategori_ekskuls', 'nama')->ignore($kategori?->id)],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:99'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    private function pesan(): array
    {
        return [
            'nama.required' => 'Isi nama kategori.',
            'nama.max' => 'Nama kategori maksimal 60 karakter.',
            'nama.unique' => 'Kategori dengan nama itu sudah ada.',
            'urutan.integer' => 'Urutan harus berupa angka.',
            'urutan.min' => 'Urutan minimal 0.',
            'urutan.max' => 'Urutan maksimal 99.',
        ];
    }

    // Slug unik dari nama (dipakai di URL filter katalog, tidak berubah walau nama diganti)
    private function buatSlug(string $nama): string
    {
        $dasar = Str::limit(Str::slug($nama) ?: 'kategori', 60, '');
        $slug = $dasar;
        $i = 2;

        while (KategoriEkskul::where('slug', $slug)->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        return $slug;
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        KategoriEkskul::create([
            'nama' => $data['nama'],
            'slug' => $this->buatSlug($data['nama']),
            'urutan' => $data['urutan'] ?? 50,
            'aktif' => true,
        ]);

        return back()->with('success', "Kategori {$data['nama']} ditambahkan.");
    }

    public function update(Request $request, KategoriEkskul $kategori)
    {
        $data = $request->validate($this->aturan($kategori), $this->pesan());

        $kategori->update([
            'nama' => $data['nama'],
            'urutan' => $data['urutan'] ?? $kategori->urutan,
            'aktif' => $request->boolean('aktif'),
        ]);

        return back()->with('success', "Kategori {$kategori->nama} diperbarui.");
    }

    public function destroy(KategoriEkskul $kategori)
    {
        $dipakai = $kategori->ekskuls()->count();

        if ($dipakai > 0) {
            return back()->with('error', "Kategori {$kategori->nama} masih dipakai {$dipakai} ekskul. Cukup nonaktifkan kategorinya, atau lepaskan dulu dari ekskulnya.");
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return back()->with('success', "Kategori {$nama} dihapus.");
    }
}
