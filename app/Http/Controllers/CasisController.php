<?php

namespace App\Http\Controllers;

use App\Models\CasisPendaftaran;
use App\Models\CasisReview;
use App\Models\Ekstrakurikuler;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use App\Services\OsisService;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// Seleksi masuk ekskul berseleksi (Ekskul Seleksi & Organisasi, mis. CaSis OSIS): siswa mendaftar, lalu melewati tahap
// seleksi satu per satu. Di tiap tahap dinilai tiga unsur (pembina, admin, senior); hasil tahap diputuskan admin atau pembina.
class CasisController extends Controller
{
    // ================= Siswa =================

    public function create(Request $request, Ekstrakurikuler $ekskul, OsisService $osis)
    {
        $user = $request->user();
        abort_unless($user->role === 'siswa', 403, 'Pendaftaran seleksi khusus siswa.');
        abort_unless($ekskul->memakaiCasis(), 404);

        $periode       = $osis->periodeBuka($ekskul);
        $casis         = $periode ? CasisPendaftaran::where('osis_periode_id', $periode->id)->where('user_id', $user->id)->first() : null;
        $sudahPengurus = $osis->pengurusAktif($user, $ekskul);
        $riwayat       = CasisPendaftaran::with('periode')
            ->where('user_id', $user->id)
            ->whereHas('periode', fn ($q) => $q->where('ekstrakurikuler_id', $ekskul->id))
            ->latest()
            ->get();

        // Progres tahap: dari pendaftaran terbaru yang masih berjalan atau sudah ada hasilnya
        $progres = $riwayat->first(fn ($c) => in_array($c->status, ['diajukan', 'diterima', 'ditolak'], true));
        $tahaps  = $progres ? $osis->tahapPeriode($progres->periode) : collect();
        $hasils  = $progres ? $progres->hasils()->get()->keyBy('seleksi_tahap_id') : collect();

        return view('casis.create', compact('ekskul', 'periode', 'casis', 'sudahPengurus', 'riwayat', 'progres', 'tahaps', 'hasils'));
    }

    // Simpan sebagai draft atau ajukan
    public function simpan(Request $request, Ekstrakurikuler $ekskul, OsisService $osis)
    {
        $user = $request->user();
        abort_unless($user->role === 'siswa', 403, 'Pendaftaran seleksi khusus siswa.');
        abort_unless($ekskul->memakaiCasis(), 404);

        $label   = $ekskul->label_calon;
        $periode = $osis->periodeBuka($ekskul);
        if (!$periode) {
            return back()->with('error', "Pendaftaran {$label} sedang ditutup.");
        }

        if ($osis->pengurusAktif($user, $ekskul)) {
            return back()->with('error', "Kamu sudah menjadi anggota {$ekskul->nama_ekstrakurikuler}.");
        }

        $casis = CasisPendaftaran::where('osis_periode_id', $periode->id)->where('user_id', $user->id)->first();
        if ($casis && !$casis->bisa_diubah) {
            return back()->with('error', 'Pendaftaranmu tidak bisa diubah lagi.');
        }

        $ajukan = $request->input('aksi') === 'ajukan';

        $data = $request->validate([
            'aksi'       => ['required', Rule::in(['simpan', 'ajukan'])],
            'motivasi'   => $ajukan ? ['required', 'string', 'min:50', 'max:3000'] : ['nullable', 'string', 'max:3000'],
            'pengalaman' => 'nullable|string|max:3000',
            'visi_misi'  => 'nullable|string|max:3000',
        ], [
            'motivasi.required' => 'Motivasi wajib diisi untuk mengajukan pendaftaran.',
            'motivasi.min'      => 'Motivasi minimal 50 karakter supaya penilai bisa memahami alasanmu.',
        ]);

        $sebelumnya = $casis?->status;

        $casis = CasisPendaftaran::updateOrCreate(
            ['osis_periode_id' => $periode->id, 'user_id' => $user->id],
            [
                'motivasi'   => trim($data['motivasi'] ?? '') ?: null,
                'pengalaman' => trim($data['pengalaman'] ?? '') ?: null,
                'visi_misi'  => trim($data['visi_misi'] ?? '') ?: null,
                'status'     => $ajukan ? 'diajukan' : ($sebelumnya ?? 'draft'),
            ]
        );

        if ($ajukan && $sebelumnya !== 'diajukan') {
            Pemberitahuan::kirim(
                Pemberitahuan::pengelola($ekskul),
                "Pendaftar {$label} baru",
                "{$user->name} mengajukan pendaftaran {$label} {$ekskul->nama_ekstrakurikuler} {$periode->nama}.",
                route('casis.index', [$ekskul->id, 'periode' => $periode->id], false),
                '🗳️',
                $user->id
            );
        }

        return back()->with('success', $ajukan ? "Pendaftaran {$label} diajukan. Tunggu proses seleksi." : 'Draft tersimpan.');
    }

    public function mundur(Request $request, Ekstrakurikuler $ekskul)
    {
        $user  = $request->user();
        $label = $ekskul->label_calon;

        $casis = CasisPendaftaran::with('periode')
            ->where('user_id', $user->id)
            ->whereIn('status', ['draft', 'diajukan'])
            ->whereHas('periode', fn ($q) => $q->where('ekstrakurikuler_id', $ekskul->id)->whereIn('status', ['rekrutmen', 'seleksi']))
            ->latest()
            ->first();

        if (!$casis) {
            return back()->with('error', 'Tidak ada pendaftaran yang bisa dibatalkan.');
        }

        $casis->update(['status' => 'mengundurkan_diri']);

        Pemberitahuan::kirim(
            Pemberitahuan::pengelola($ekskul),
            "{$label} mengundurkan diri",
            "{$user->name} mengundurkan diri dari pendaftaran {$label} {$ekskul->nama_ekstrakurikuler} {$casis->periode->nama}.",
            route('casis.index', [$ekskul->id, 'periode' => $casis->osis_periode_id], false),
            '↩️',
            $user->id
        );

        return back()->with('success', "Pendaftaran {$label} dibatalkan.");
    }

    // ================= Penilai & pengelola =================

    // Pusat seleksi satu ekskul: daftar periode, tahap, pendaftar, dan (untuk pengelola) pengaturan periode + senior
    public function index(Request $request, Ekstrakurikuler $ekskul, OsisService $osis)
    {
        $user = $request->user();
        abort_unless($ekskul->memakaiCasis(), 404);

        $bolehKelola = $osis->bolehMemutuskan($user, $ekskul);
        $jadiSenior  = OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)
            ->whereHas('seniors', fn ($q) => $q->where('users.id', $user->id))
            ->exists();

        abort_unless($bolehKelola || $jadiSenior || $user->isAdmin(), 403, 'Halaman ini untuk pengelola ekskul dan penilai seleksi.');

        $periodes = OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)->withCount('casis')->latest()->get();
        $periode  = $periodes->firstWhere('id', (int) $request->query('periode')) ?? $periodes->first();

        $pendaftar = collect();
        $sudahSaya = [];
        $tipeSaya  = null;
        $kandidatSenior = collect();
        $tahaps    = collect();
        $posisi    = [];

        if ($periode) {
            $periode->load('seniors');
            $tipeSaya = $osis->tipeReviewer($user, $periode);
            $tahaps   = $osis->tahapPeriode($periode);

            $urutan = ['diajukan', 'diterima', 'ditolak', 'draft', 'mengundurkan_diri'];
            $pendaftar = CasisPendaftaran::with(['user', 'reviews', 'hasils'])
                ->where('osis_periode_id', $periode->id)
                ->get()
                ->sortBy(fn ($c) => array_search($c->status, $urutan))
                ->values();

            // Posisi tiap pendaftar: tahap yang sedang dijalani (null kalau sudah diputuskan / belum diajukan)
            foreach ($pendaftar as $c) {
                $sudahTahap = $c->hasils->pluck('seleksi_tahap_id')->all();
                $posisi[$c->id] = $c->status === 'diajukan'
                    ? $tahaps->first(fn ($t) => !in_array($t->id, $sudahTahap, true))
                    : null;
            }

            // Penilaian milikku yang sudah masuk, per tahap yang sedang dijalani pendaftar
            $sudahSaya = $pendaftar
                ->filter(fn ($c) => $posisi[$c->id] && $c->reviews->contains(fn ($r) => $r->reviewer_id === $user->id && $r->seleksi_tahap_id === $posisi[$c->id]->id))
                ->pluck('id')
                ->all();

            if ($bolehKelola) {
                $kandidatSenior = PendaftaranEkskul::anggotaAktif()
                    ->where('ekstrakurikuler_id', $ekskul->id)
                    ->whereNotNull('user_id')
                    ->whereNotIn('user_id', $periode->seniors->pluck('id'))
                    ->whereNotIn('user_id', $pendaftar->pluck('user_id'))
                    ->orderBy('nama_pendaftar')
                    ->get();
            }
        }

        return view('casis.index', compact('ekskul', 'periodes', 'periode', 'pendaftar', 'sudahSaya', 'tipeSaya', 'bolehKelola', 'kandidatSenior', 'tahaps', 'posisi'));
    }

    // Detail pendaftar + form penilaian tahap yang sedang dijalani. Penilai baru bisa melihat penilaian unsur lain
    // di tahap itu setelah menilai sendiri; penilaian tahap yang sudah lewat selalu terbuka bagi penilai.
    public function show(Request $request, CasisPendaftaran $casis, OsisService $osis)
    {
        $user    = $request->user();
        $periode = $casis->periode()->with('ekstrakurikuler')->first();
        $ekskul  = $periode->ekstrakurikuler;

        abort_if($casis->user_id === $user->id, 403, 'Kamu tidak bisa membuka penilaian pendaftaranmu sendiri.');

        $tipe = $osis->tipeReviewer($user, $periode);
        abort_unless($tipe, 403, 'Kamu bukan penilai seleksi periode ini.');

        $casis->load(['user', 'reviews.reviewer', 'hasils.pemutus']);

        $tahaps        = $osis->tahapPeriode($periode);
        $tahapSekarang = $casis->tahapSekarang();

        $saya       = $tahapSekarang ? $casis->reviews->first(fn ($r) => $r->reviewer_id === $user->id && $r->seleksi_tahap_id === $tahapSekarang->id) : null;
        $lihatSemua = !$tahapSekarang || (bool) $saya;
        $syarat     = $tahapSekarang ? $osis->syarat($casis, $tahapSekarang) : [];

        $bolehMenilai = $tahapSekarang && $periode->status === 'seleksi';

        $bolehMemutuskan = $bolehMenilai
            && $osis->bolehMemutuskan($user, $ekskul)
            && collect($syarat)->every(fn ($v) => $v);

        $hasils = $casis->hasils->keyBy('seleksi_tahap_id');

        return view('casis.show', compact(
            'casis', 'periode', 'ekskul', 'tipe', 'saya', 'lihatSemua', 'syarat', 'bolehMemutuskan', 'bolehMenilai', 'tahaps', 'tahapSekarang', 'hasils'
        ));
    }

    public function review(Request $request, CasisPendaftaran $casis, OsisService $osis)
    {
        $user    = $request->user();
        $periode = $casis->periode;

        abort_if($casis->user_id === $user->id, 403, 'Kamu tidak bisa menilai pendaftaranmu sendiri.');

        $tipe = $osis->tipeReviewer($user, $periode);
        abort_unless($tipe, 403, 'Kamu bukan penilai seleksi periode ini.');

        $tahap = $casis->tahapSekarang();

        if (!$tahap || $periode->status !== 'seleksi') {
            return back()->with('error', 'Penilaian hanya bisa dilakukan saat tahap seleksi untuk pendaftar yang berstatus diajukan.');
        }

        $data = $request->validate([
            'skor'        => 'required|integer|between:0,100',
            'rekomendasi' => ['required', Rule::in(array_keys(CasisReview::REKOMENDASI))],
            'catatan'     => 'nullable|string|max:2000',
        ], ['skor.between' => 'Skor harus antara 0 sampai 100.']);

        CasisReview::updateOrCreate(
            ['casis_pendaftaran_id' => $casis->id, 'reviewer_id' => $user->id, 'seleksi_tahap_id' => $tahap->id],
            ['tipe' => $tipe, 'skor' => $data['skor'], 'rekomendasi' => $data['rekomendasi'], 'catatan' => trim($data['catatan'] ?? '') ?: null]
        );

        return back()->with('success', "Penilaianmu untuk tahap {$tahap->nama} tersimpan.");
    }

    // Hasil tahap yang sedang dijalani: admin atau pembina ekskul, setelah semua unsur yang wajib sudah menilai.
    // Tidak lolos = ditolak; lolos di tahap terakhir = diterima; lolos di tahap lain = lanjut ke tahap berikutnya.
    public function putuskan(Request $request, CasisPendaftaran $casis, OsisService $osis)
    {
        $user    = $request->user();
        $periode = $casis->periode()->with('ekstrakurikuler')->first();
        $ekskul  = $periode->ekstrakurikuler;
        $label   = $ekskul->label_calon;

        abort_unless($osis->bolehMemutuskan($user, $ekskul), 403);
        abort_if($casis->user_id === $user->id, 403);

        $tahap = $casis->tahapSekarang();

        if (!$tahap || $periode->status !== 'seleksi') {
            return back()->with('error', 'Pendaftar ini tidak bisa diputuskan pada tahap sekarang.');
        }

        $casis->load('reviews');
        $kurang = collect($osis->syarat($casis, $tahap))->reject(fn ($v) => $v)->keys()
            ->map(fn ($t) => CasisReview::TIPE[$t])->all();

        if ($kurang) {
            return back()->with('error', 'Belum semua unsur memberi penilaian. Menunggu: ' . implode(', ', $kurang) . '.');
        }

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['lolos', 'tolak'])],
            'catatan'   => ['nullable', 'string', 'max:1000', Rule::requiredIf($request->input('keputusan') === 'tolak')],
        ], ['catatan.required' => 'Beri catatan alasan penolakan.']);

        $lolos   = $data['keputusan'] === 'lolos';
        $catatan = trim($data['catatan'] ?? '') ?: null;

        $hasil = $osis->tetapkanHasil($casis, $tahap, $lolos ? 'lolos' : 'tidak_lolos', $catatan, $user);

        if ($hasil['status'] === 'diterima') {
            Pemberitahuan::kirim([$casis->user], "Selamat, kamu lolos seleksi {$ekskul->nama_ekstrakurikuler}!", "Kamu diterima di {$ekskul->nama_ekstrakurikuler} periode {$periode->nama}.", route('ekstrakurikuler.show', $ekskul->id, false), '🎉', $user->id);
        } elseif ($hasil['status'] === 'ditolak') {
            Pemberitahuan::kirim([$casis->user], "Hasil seleksi {$ekskul->nama_ekstrakurikuler}", "Pendaftaran {$label} {$periode->nama} belum diterima pada tahap {$tahap->nama}." . ($catatan ? " Catatan: {$catatan}" : ''), route('casis.create', $ekskul->id, false), '📄', $user->id);
        } else {
            $berikut = $hasil['berikut'];
            Pemberitahuan::kirim([$casis->user], "Lolos tahap {$tahap->nama}", "Kamu lanjut ke tahap berikutnya: {$berikut->ringkasan}.", route('casis.create', $ekskul->id, false), '✅', $user->id);
        }

        $pesan = match ($hasil['status']) {
            'diterima' => ' lolos semua tahap dan diterima.',
            'ditolak'  => ' tidak lolos dan ditolak.',
            default    => " lolos tahap {$tahap->nama}, lanjut ke {$hasil['berikut']->nama}.",
        };

        return redirect()->route('casis.index', [$ekskul->id, 'periode' => $periode->id])
            ->with('success', ($casis->user->name ?? 'Pendaftar') . $pesan);
    }

    // ================= Dari Kelola User =================

    // Memasukkan siswa ke jalur OSIS: dibuatkan draft CaSis, siswa diarahkan melengkapi formulirnya
    public function masukkan(Request $request, User $user, OsisService $osis)
    {
        abort_unless($osis->bolehMemutuskan($request->user()), 403);

        if ($user->role !== 'siswa' || !$user->is_active) {
            return back()->with('error', 'Hanya siswa dengan akun aktif yang bisa dimasukkan ke jalur CaSis.');
        }

        $periode = $osis->periodeBuka();
        if (!$periode) {
            return back()->with('error', 'Belum ada periode pendaftaran CaSis yang dibuka. Buat periodenya di halaman Seleksi CaSis.');
        }

        if ($osis->pengurusAktif($user)) {
            return back()->with('error', "{$user->name} sudah menjadi pengurus OSIS.");
        }

        if (CasisPendaftaran::where('osis_periode_id', $periode->id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', "{$user->name} sudah terdaftar sebagai CaSis di periode ini.");
        }

        CasisPendaftaran::create([
            'osis_periode_id'  => $periode->id,
            'user_id'          => $user->id,
            'status'           => 'draft',
            'didaftarkan_oleh' => $request->user()->id,
        ]);

        Pemberitahuan::kirim(
            [$user],
            'Kamu didaftarkan ke jalur OSIS',
            "{$request->user()->name} memasukkanmu ke jalur CaSis OSIS {$periode->nama}. Lengkapi formulir pendaftaranmu.",
            route('casis.create', [$osis->osis()->id], false),
            '🗳️',
            $request->user()->id
        );

        return back()->with('success', "{$user->name} dimasukkan ke jalur CaSis. Ia diminta melengkapi formulirnya.");
    }

    // Aturan isian kepengurusan manual (dipakai untuk catat susulan dan koreksi)
    private function aturanPengurus(): array
    {
        return [
            'angkatan'      => ['required', 'string', 'max:20'],
            'tahun_mulai'   => ['required', 'integer', 'min:1990', 'max:' . (now()->year + 1)],
            'tahun_selesai' => ['nullable', 'integer', 'gte:tahun_mulai', 'max:' . (now()->year + 10), 'required_if:status,selesai'],
            'status'        => ['required', Rule::in(array_keys(OsisService::STATUS_PENGURUS))],
            'catatan'       => ['nullable', 'string', 'max:200'],
        ];
    }

    private function pesanPengurus(): array
    {
        return [
            'angkatan.required'         => 'Isi angkatan kepengurusan, mis. 2024/2025.',
            'angkatan.max'              => 'Angkatan maksimal 20 karakter.',
            'tahun_mulai.required'      => 'Isi tahun mulai menjabat.',
            'tahun_mulai.integer'       => 'Tahun mulai harus berupa angka.',
            'tahun_mulai.min'           => 'Tahun mulai tidak masuk akal.',
            'tahun_mulai.max'           => 'Tahun mulai tidak boleh jauh di masa depan.',
            'tahun_selesai.required_if' => 'Pengurus yang sudah purna tugas wajib diisi tahun selesainya.',
            'tahun_selesai.gte'         => 'Tahun selesai tidak boleh lebih awal dari tahun mulai.',
            'tahun_selesai.integer'     => 'Tahun selesai harus berupa angka.',
            'status.required'           => 'Pilih status kepengurusan.',
            'status.in'                 => 'Status kepengurusan tidak dikenal.',
            'catatan.max'               => 'Catatan maksimal 200 karakter.',
        ];
    }

    // Menetapkan siswa langsung menjadi pengurus OSIS (tanpa seleksi CaSis). Dipakai untuk pengurus angkatan lalu yang
    // baru diinput (telat input): admin/pembina memilih angkatan, tahun, dan statusnya (masih menjabat / purna tugas).
    public function jadikanPengurus(Request $request, User $user, OsisService $osis)
    {
        abort_unless($osis->bolehMemutuskan($request->user()), 403);

        if ($user->role !== 'siswa' || !$user->is_active) {
            return back()->with('error', 'Hanya siswa dengan akun aktif yang bisa dijadikan pengurus OSIS.');
        }

        $cek = Validator::make($request->all(), $this->aturanPengurus(), $this->pesanPengurus());

        if ($cek->fails()) {
            return back()->withInput()->with('error', $cek->errors()->first());
        }

        $d = $cek->validated();

        if ($pesan = $osis->periksaSusulan($user, $d['angkatan'], $d['status'])) {
            return back()->withInput()->with('error', $pesan);
        }

        $alasan = 'Ditetapkan langsung oleh ' . $request->user()->name . (filled($d['catatan'] ?? null) ? ': ' . $d['catatan'] : '');
        $aktif  = $d['status'] === 'aktif';

        DB::transaction(function () use ($user, $request, $osis, $d, $alasan, $aktif) {
            $osis->jadikanPengurus(
                $user,
                $alasan,
                $d['angkatan'],
                (int) $d['tahun_mulai'],
                isset($d['tahun_selesai']) ? (int) $d['tahun_selesai'] : null,
                $d['status'],
                $request->user()->id,
            );

            // Kalau ia masih punya pendaftaran CaSis yang berjalan, tandai selesai agar tidak dobel.
            // Hanya untuk yang masih menjabat: mencatat riwayat purna tugas tidak boleh mengganggu seleksi yang sedang berjalan.
            if ($aktif) {
                CasisPendaftaran::where('user_id', $user->id)
                    ->whereIn('status', ['draft', 'diajukan'])
                    ->whereHas('periode', fn ($q) => $q->where('ekstrakurikuler_id', $osis->osis()->id)->whereIn('status', ['rekrutmen', 'seleksi']))
                    ->update([
                        'status'            => 'diterima',
                        'diputuskan_oleh'   => $request->user()->id,
                        'catatan_keputusan' => 'Ditetapkan langsung sebagai pengurus OSIS.',
                        'diputuskan_pada'   => now(),
                    ]);
            }
        });

        Pemberitahuan::kirim(
            [$user],
            $aktif ? 'Kamu menjadi pengurus OSIS' : 'Riwayat kepengurusan OSIS dicatat',
            $aktif
                ? "{$request->user()->name} menetapkanmu sebagai pengurus OSIS angkatan {$d['angkatan']}."
                : "{$request->user()->name} mencatat kepengurusan OSIS angkatan {$d['angkatan']} ({$d['tahun_mulai']}-{$d['tahun_selesai']}) di riwayatmu.",
            route('ekstrakurikuler.show', $osis->osis()->id, false),
            '🏛️',
            $request->user()->id
        );

        return back()->with('success', $aktif
            ? "{$user->name} sekarang pengurus OSIS angkatan {$d['angkatan']}."
            : "Kepengurusan OSIS {$user->name} angkatan {$d['angkatan']} dicatat sebagai purna tugas.");
    }

    // Mengoreksi angkatan/tahun/status kepengurusan OSIS yang sudah tercatat (mis. salah angkatan atau telat diperbarui)
    public function koreksiPengurus(Request $request, PendaftaranEkskul $pendaftaran, OsisService $osis)
    {
        abort_unless($osis->bolehMemutuskan($request->user()), 403);

        $ekskulOsis = $osis->osis();

        abort_unless(
            $ekskulOsis
            && (int) $pendaftaran->ekstrakurikuler_id === (int) $ekskulOsis->id
            && $pendaftaran->user_id
            && $pendaftaran->status === 'diterima'
            && array_key_exists($pendaftaran->status_anggota, OsisService::STATUS_PENGURUS),
            404
        );

        $cek = Validator::make($request->all(), $this->aturanPengurus(), $this->pesanPengurus());

        if ($cek->fails()) {
            return back()->withInput()->with('error', $cek->errors()->first());
        }

        $d     = $cek->validated();
        $siswa = $pendaftaran->user;

        if ($pesan = $osis->periksaSusulan($siswa, $d['angkatan'], $d['status'], $pendaftaran->id)) {
            return back()->withInput()->with('error', $pesan);
        }

        $pendaftaran->update([
            'angkatan_ekskul' => $d['angkatan'],
            'tahun_mulai'     => (int) $d['tahun_mulai'],
            'tahun_selesai'   => isset($d['tahun_selesai']) ? (int) $d['tahun_selesai'] : null,
            'status_anggota'  => $d['status'],
        ]);

        Pemberitahuan::kirim(
            [$siswa],
            'Data kepengurusan OSIS dikoreksi',
            "{$request->user()->name} memperbarui data kepengurusan OSIS-mu: angkatan {$d['angkatan']}, " . OsisService::STATUS_PENGURUS[$d['status']] . '.',
            route('ekstrakurikuler.show', $ekskulOsis->id, false),
            '✏️',
            $request->user()->id
        );

        return back()->with('success', "Data kepengurusan OSIS {$siswa->name} diperbarui.");
    }
}
