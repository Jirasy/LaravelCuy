<?php

namespace App\Http\Controllers;

use App\Models\EkskulFoto;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Support\BerkasPrivat;
use App\Support\Gambar;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// Galeri dokumentasi kegiatan per ekskul.
// Lihat: semua user yang login, khusus foto yang sudah disetujui.
// Unggah: pengelola (langsung tampil) dan anggota aktif (menunggu persetujuan pengelola).
class EkskulFotoController extends Controller
{
    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('galeri', $ekstrakurikuler);

        $pengelola = $request->user()->can('kelola', $ekstrakurikuler);

        $data = $request->validate([
            'foto' => 'required|array|min:1|max:10',
            'foto.*' => 'file|image|mimes:jpg,jpeg,png,webp|max:8192',
            'kategori' => ['required', Rule::in(array_keys(EkskulFoto::KATEGORI))],
            'caption' => 'nullable|string|max:200',
            'diambil_pada' => 'nullable|date|before_or_equal:today',
            'event_id' => ['nullable', Rule::exists('ekskul_events', 'id')->where('ekstrakurikuler_id', $ekstrakurikuler->id)],
        ], [
            'foto.required' => 'Pilih minimal satu foto.',
            'foto.max' => 'Maksimal 10 foto per unggahan.',
            'foto.*.uploaded' => 'Salah satu foto gagal diunggah. Kemungkinan ukurannya melebihi batas server (upload_max_filesize dan post_max_size di php.ini).',
            'foto.*.max' => 'Ukuran tiap foto maksimal 8 MB.',
            'foto.*.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'foto.*.image' => 'File harus berupa gambar.',
            'diambil_pada.before_or_equal' => 'Tanggal foto tidak boleh di masa depan.',
        ]);

        $jumlah = 0;

        foreach ($request->file('foto') as $file) {
            $simpan = BerkasPrivat::simpan($file, "galeri/{$ekstrakurikuler->id}");

            EkskulFoto::create([
                'ekstrakurikuler_id' => $ekstrakurikuler->id,
                'event_id' => $data['event_id'] ?? null,
                'diunggah_oleh' => $request->user()->id,
                'berkas' => $simpan['berkas'],
                'thumb' => $this->buatThumbnail($simpan['berkas']),
                'nama_berkas' => $simpan['nama_berkas'],
                'mime' => $simpan['mime'],
                'caption' => trim($data['caption'] ?? '') ?: null,
                'kategori' => $data['kategori'],
                'diambil_pada' => $data['diambil_pada'] ?? now()->toDateString(),
                'disetujui' => $pengelola,
            ]);

            $jumlah++;
        }

        $this->beritahu($request, $ekstrakurikuler, $pengelola, $jumlah);

        return $this->kembali($ekstrakurikuler->id, $pengelola
            ? "{$jumlah} foto ditambahkan ke galeri."
            : "{$jumlah} foto terkirim dan menunggu persetujuan pembina atau admin.");
    }

    // Menyajikan foto: asli atau thumbnail (?thumb=1).
    // Galeri terbuka untuk semua user yang login (rute ini ada di grup auth), jadi tidak perlu jadi anggota.
    public function berkas(Request $request, EkskulFoto $foto)
    {
        $ekskul = $foto->ekstrakurikuler;
        $user = $request->user();

        // Yang belum disetujui tetap privat: hanya pengelola dan pengunggahnya
        abort_unless(
            $foto->disetujui || $foto->diunggah_oleh === $user->id || $user->can('kelola', $ekskul),
            403
        );

        $pakaiThumb = $request->boolean('thumb') && $foto->thumb && Storage::disk('local')->exists($foto->thumb);
        $path = $pakaiThumb ? $foto->thumb : $foto->berkas;
        $mime = $pakaiThumb ? 'image/jpeg' : $foto->mime;

        return BerkasPrivat::kirim($path, $foto->nama_berkas, $mime, true, ['Cache-Control' => 'private, max-age=86400']);
    }

    public function setujui(Request $request, EkskulFoto $foto)
    {
        Gate::authorize('kelola', $foto->ekstrakurikuler);

        if (! $foto->disetujui) {
            $foto->update(['disetujui' => true]);

            if ($foto->pengunggah) {
                Pemberitahuan::kirim(
                    [$foto->pengunggah],
                    'Foto disetujui',
                    "Fotomu di galeri {$foto->ekstrakurikuler->nama_ekstrakurikuler} sudah tampil.",
                    route('ekstrakurikuler.show', ['id' => $foto->ekstrakurikuler_id, 'tab' => 'galeri'], false),
                    '📷',
                    $request->user()->id
                );
            }
        }

        return back()->with('success', 'Foto disetujui.');
    }

    public function setujuiSemua(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('kelola', $ekstrakurikuler);

        $menunggu = $ekstrakurikuler->fotos()->where('disetujui', false)->with('pengunggah')->get();
        $ekstrakurikuler->fotos()->where('disetujui', false)->update(['disetujui' => true]);

        // Satu notifikasi per pengunggah
        $menunggu->groupBy('diunggah_oleh')->each(function ($kelompok) use ($ekstrakurikuler, $request) {
            $orang = $kelompok->first()->pengunggah;
            if ($orang) {
                Pemberitahuan::kirim(
                    [$orang],
                    'Foto disetujui',
                    "{$kelompok->count()} fotomu di galeri {$ekstrakurikuler->nama_ekstrakurikuler} sudah tampil.",
                    route('ekstrakurikuler.show', ['id' => $ekstrakurikuler->id, 'tab' => 'galeri'], false),
                    '📷',
                    $request->user()->id
                );
            }
        });

        return back()->with('success', $menunggu->count().' foto disetujui.');
    }

    // Pengelola boleh menghapus foto apa pun; pengunggah boleh menghapus fotonya sendiri
    public function destroy(Request $request, EkskulFoto $foto)
    {
        $user = $request->user();

        abort_unless($foto->diunggah_oleh === $user->id || $user->can('kelola', $foto->ekstrakurikuler), 403);

        $foto->delete();   // foto asli dan thumbnail ikut dihapus

        return back()->with('success', 'Foto dihapus.');
    }

    // ---------- bantu ----------

    private function buatThumbnail(string $berkas): ?string
    {
        try {
            $thumb = dirname($berkas).'/thumb_'.pathinfo($berkas, PATHINFO_FILENAME).'.jpg';
            $disk = Storage::disk('local');

            return Gambar::thumbnail($disk->path($berkas), $disk->path($thumb)) ? $thumb : null;
        } catch (\Throwable) {
            return null;   // gagal membuat thumbnail tidak boleh menggagalkan unggahan
        }
    }

    private function beritahu(Request $request, Ekstrakurikuler $ekskul, bool $pengelola, int $jumlah): void
    {
        $aktor = $request->user();
        $url = route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'galeri'], false);

        if ($pengelola) {
            // Foto resmi: beri tahu anggota aktif
            $anggota = PendaftaranEkskul::anggotaAktif()
                ->where('ekstrakurikuler_id', $ekskul->id)
                ->whereNotNull('user_id')
                ->with('user')
                ->get()
                ->pluck('user');

            Pemberitahuan::kirim($anggota, 'Foto baru di galeri', "{$jumlah} foto baru ditambahkan di {$ekskul->nama_ekstrakurikuler}.", $url, '📷', $aktor->id);

            return;
        }

        // Foto siswa: minta persetujuan pengelola
        Pemberitahuan::kirim(
            Pemberitahuan::pengelola($ekskul),
            'Foto menunggu persetujuan',
            "{$aktor->name} mengunggah {$jumlah} foto ke galeri {$ekskul->nama_ekstrakurikuler}.",
            $url,
            '🕒',
            $aktor->id
        );
    }

    private function kembali(int $ekskulId, string $pesan)
    {
        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskulId, 'tab' => 'galeri'])
            ->with('success', $pesan);
    }
}
