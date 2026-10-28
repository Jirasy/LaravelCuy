<?php

namespace App\Http\Controllers;

use App\Models\EkskulPr;
use App\Models\EkskulPrPengumpulan;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Support\BerkasPrivat;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EkskulPrController extends Controller
{
    // ---------- pengelola ----------

    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('kelola', $ekstrakurikuler);

        $data = $request->validate([
            'judul'      => 'required|string|max:200',
            'deskripsi'  => 'nullable|string|max:5000',
            'tenggat'    => 'nullable|date|after_or_equal:today',
            'nilai_maks' => 'nullable|integer|between:10,100',
        ], ['tenggat.after_or_equal' => 'Tenggat tidak boleh di masa lalu.']);

        $pr = $ekstrakurikuler->prs()->create([
            'judul'       => $data['judul'],
            'deskripsi'   => $data['deskripsi'] ?? null,
            'tenggat'     => $data['tenggat'] ?? null,
            'nilai_maks'  => $data['nilai_maks'] ?? 100,
            'dibuat_oleh' => Auth::id(),
        ]);

        $penerima = PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('akses_materi', true)
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->pluck('user');

        $tenggat = $pr->tenggat ? ' (tenggat ' . $pr->tenggat->format('d-m-Y') . ')' : '';
        Pemberitahuan::kirim(
            $penerima,
            'Task baru',
            "{$pr->judul} di {$ekstrakurikuler->nama_ekstrakurikuler}{$tenggat}",
            route('ekstrakurikuler.show', ['id' => $ekstrakurikuler->id, 'tab' => 'materi'], false),
            '📝',
            Auth::id()
        );

        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekstrakurikuler->id, 'tab' => 'materi'])
            ->with('success', 'Task berhasil dibuat.');
    }

    // Halaman pemeriksaan: siapa yang sudah mengumpulkan, beri nilai
    public function show(EkskulPr $pr)
    {
        $ekskul = $pr->ekstrakurikuler;
        Gate::authorize('kelola', $ekskul);

        $pengumpulans = $pr->pengumpulans()->with('penilai')->get()->keyBy('pendaftaran_id');

        $anggota = PendaftaranEkskul::where('ekstrakurikuler_id', $ekskul->id)
            ->where('status', 'diterima')
            ->where(function ($q) use ($pengumpulans) {
                $q->where('status_anggota', 'aktif')->orWhereIn('id', $pengumpulans->keys());
            })
            ->orderBy('nama_pendaftar')
            ->get();

        return view('tugas.show', compact('pr', 'ekskul', 'anggota', 'pengumpulans'));
    }

    public function destroy(EkskulPr $pr)
    {
        $ekskulId = $pr->ekstrakurikuler_id;
        Gate::authorize('kelola', $pr->ekstrakurikuler);

        $pr->delete();   // berkas pengumpulan ikut dihapus

        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskulId, 'tab' => 'materi'])
            ->with('success', 'Task dihapus.');
    }

    public function nilai(Request $request, EkskulPrPengumpulan $pengumpulan)
    {
        $pr     = $pengumpulan->pr;
        $ekskul = $pr->ekstrakurikuler;
        Gate::authorize('kelola', $ekskul);

        $data = $request->validate([
            'nilai'    => "required|integer|between:0,{$pr->nilai_maks}",
            'komentar' => 'nullable|string|max:2000',
        ], ['nilai.between' => "Nilai harus antara 0 sampai {$pr->nilai_maks}."]);

        $pengumpulan->update([
            'nilai'        => $data['nilai'],
            'komentar'     => $data['komentar'] ?? null,
            'dinilai_oleh' => Auth::id(),
            'dinilai_pada' => now(),
        ]);

        $siswa = $pengumpulan->pendaftaran->user;
        if ($siswa) {
            Pemberitahuan::kirim(
                [$siswa],
                'Task dinilai',
                "{$pr->judul}: nilai {$data['nilai']} dari {$pr->nilai_maks}.",
                route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'materi'], false),
                '📊',
                Auth::id()
            );
        }

        return back()->with('success', 'Nilai disimpan.');
    }

    // ---------- anggota ----------

    public function kumpul(Request $request, EkskulPr $pr)
    {
        $ekskul = $pr->ekstrakurikuler;
        $user   = $request->user();

        // Hanya anggota aktif yang diizinkan mengakses materi & task
        $anggota = PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $ekskul->id)
            ->where('user_id', $user->id)
            ->where('akses_materi', true)
            ->first();

        abort_unless($anggota, 403, 'Kamu belum diizinkan mengerjakan Task ini.');

        $ada = EkskulPrPengumpulan::where('pr_id', $pr->id)->where('pendaftaran_id', $anggota->id)->first();

        if ($ada && $ada->sudah_dinilai) {
            return back()->with('error', 'Task ini sudah dinilai dan tidak bisa diubah lagi.');
        }

        $data = $request->validate([
            'jawaban' => 'nullable|string|max:5000',
            'berkas'  => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,jpg,jpeg,png,zip|max:20480',
        ], [
            'berkas.uploaded' => 'Berkas gagal diunggah. Kemungkinan ukurannya melebihi batas server.',
            'berkas.max'      => 'Ukuran berkas maksimal 20 MB.',
            'berkas.mimes'    => 'Format berkas tidak diizinkan.',
        ]);

        $jawaban     = trim($data['jawaban'] ?? '') ?: null;
        $punyaBerkas = $request->hasFile('berkas') || ($ada && $ada->berkas);

        if (!$jawaban && !$punyaBerkas) {
            return back()->withInput()->with('error', 'Isi jawaban atau unggah berkas terlebih dahulu.');
        }

        $isi = ['jawaban' => $jawaban, 'dikumpulkan_pada' => now()];

        if ($request->hasFile('berkas')) {
            BerkasPrivat::hapus($ada?->berkas);
            $isi += BerkasPrivat::simpan($request->file('berkas'), "pr/{$ekskul->id}");
        }

        $pengumpulan = EkskulPrPengumpulan::updateOrCreate(
            ['pr_id' => $pr->id, 'pendaftaran_id' => $anggota->id],
            $isi
        );

        Pemberitahuan::kirim(
            Pemberitahuan::pengelola($ekskul),
            'Task dikumpulkan',
            "{$user->name} mengumpulkan Task: {$pr->judul}.",
            route('pr.show', $pr->id, false),
            '📥',
            $user->id
        );

        return back()->with('success', $pengumpulan->terlambat
            ? 'Task terkirim, tetapi melewati tenggat.'
            : 'Task berhasil dikumpulkan.');
    }

    // Berkas pengumpulan: hanya pemiliknya dan pengelola ekskul
    public function berkas(Request $request, EkskulPrPengumpulan $pengumpulan)
    {
        $user   = $request->user();
        $ekskul = $pengumpulan->pr->ekstrakurikuler;

        abort_unless(
            $pengumpulan->pendaftaran->user_id === $user->id || $user->can('kelola', $ekskul),
            403
        );
        abort_unless($pengumpulan->berkas, 404);

        return BerkasPrivat::kirim($pengumpulan->berkas, $pengumpulan->nama_berkas, $pengumpulan->mime, false);
    }
}