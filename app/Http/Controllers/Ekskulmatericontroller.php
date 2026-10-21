<?php

namespace App\Http\Controllers;

use App\Models\EkskulMateri;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Support\BerkasPrivat;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EkskulMateriController extends Controller
{
    // Menambah materi: admin & pembina ekskul itu
    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('kelola', $ekstrakurikuler);

        $aturan = [
            'judul'     => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:2000',
            'jenis'     => ['required', Rule::in(array_keys(EkskulMateri::JENIS))],
        ];

        // Aturan berkas berbeda tiap jenis (ukuran dalam KB)
        $aturan += match ($request->input('jenis')) {
            'video'   => ['berkas' => 'required|file|mimes:mp4,webm,mov|max:102400'],                                  // 100 MB
            'foto'    => ['berkas' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120'],                               // 5 MB
            'dokumen' => ['berkas' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt|max:20480'],            // 20 MB
            'link'    => ['url' => 'required|string|max:500|url|starts_with:https://'],
            default   => [],
        };

        $data = $request->validate($aturan, [
            'berkas.required' => 'Pilih berkas yang akan diunggah.',
            'berkas.uploaded' => 'Berkas gagal diunggah. Kemungkinan ukurannya melebihi batas server (upload_max_filesize dan post_max_size di php.ini).',
            'berkas.max'      => 'Ukuran berkas melebihi batas untuk jenis ini (video 100 MB, foto 5 MB, dokumen 20 MB).',
            'berkas.mimes'    => 'Format berkas tidak diizinkan untuk jenis ini.',
            'url.url'         => 'Alamat tautan tidak valid.',
            'url.starts_with' => 'Tautan harus diawali https://',
        ]);

        $isi = [
            'ekstrakurikuler_id' => $ekstrakurikuler->id,
            'judul'              => $data['judul'],
            'deskripsi'          => $data['deskripsi'] ?? null,
            'jenis'              => $data['jenis'],
            'diunggah_oleh'      => Auth::id(),
        ];

        if ($data['jenis'] === 'link') {
            $isi['url'] = $data['url'];
        } else {
            $isi += BerkasPrivat::simpan($request->file('berkas'), "materi/{$ekstrakurikuler->id}");
        }

        EkskulMateri::create($isi);

        Pemberitahuan::kirim(
            $this->anggotaBerakses($ekstrakurikuler),
            'Materi baru',
            "{$data['judul']} ditambahkan di {$ekstrakurikuler->nama_ekstrakurikuler}.",
            route('ekstrakurikuler.show', ['id' => $ekstrakurikuler->id, 'tab' => 'materi'], false),
            '📚',
            Auth::id()
        );

        return $this->kembali($ekstrakurikuler->id, 'Materi berhasil ditambahkan!');
    }

    public function destroy(EkskulMateri $materi)
    {
        $ekskulId = $materi->ekstrakurikuler_id;
        Gate::authorize('kelola', $materi->ekstrakurikuler);

        $materi->delete();   // berkas fisiknya ikut dihapus

        return $this->kembali($ekskulId, 'Materi dihapus.');
    }

    // Menyajikan berkas: hanya pengelola dan anggota yang diizinkan
    public function berkas(Request $request, EkskulMateri $materi)
    {
        Gate::authorize('lihatMateri', $materi->ekstrakurikuler);
        abort_unless($materi->berkas, 404);

        $langsung = $materi->tampil_langsung && !$request->boolean('unduh');

        return BerkasPrivat::kirim($materi->berkas, $materi->nama_berkas, $materi->mime, $langsung);
    }

    // Pengguna akun dari anggota aktif yang punya akses materi
    private function anggotaBerakses(Ekstrakurikuler $ekskul)
    {
        return PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $ekskul->id)
            ->where('akses_materi', true)
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->pluck('user');
    }

    private function kembali(int $ekskulId, string $pesan)
    {
        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskulId, 'tab' => 'materi'])
            ->with('success', $pesan);
    }
}