<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PendaftaranEkskulController extends Controller
{
    // Admin: semua pendaftaran. Pembina: pendaftaran di ekskul yang ia bina + miliknya sendiri.
    // Siswa: hanya miliknya sendiri.
    public function index()
    {
        $user  = Auth::user();
        $query = PendaftaranEkskul::with(['user', 'ekstrakurikuler'])->latest();

        if ($user->isPembina()) {
            $query->where(function ($q) use ($user) {
                $q->whereIn('ekstrakurikuler_id', $user->idEkskulDikelola())
                  ->orWhere('user_id', $user->id);
            });
        } elseif (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $pendaftarans = $query->get();
        return view('pendaftaran.index', compact('pendaftarans'));
    }

    public function create(Request $request)
    {
        $user  = Auth::user();
        $query = Ekstrakurikuler::with('haris')
            ->withCount(['pendaftaran as anggota_count' => fn ($q) => $q->where('status', 'diterima')->where('status_anggota', 'aktif')])
            ->where('status', 'aktif')
            ->orderBy('nama_ekstrakurikuler');

        // Pembina mendaftarkan orang lain hanya ke ekskul yang ia bina
        if ($user->isPembina()) {
            $query->whereIn('id', $user->idEkskulDikelola());
        }

        $ekskuls  = $query->get();
        $terpilih = $request->query('ekstrakurikuler_id', $request->query('ekskul'));

        // Ekskul OSIS punya jalur sendiri (pendaftaran CaSis), tidak lewat form pendaftaran biasa
        $osisId = Ekstrakurikuler::where('jenis', 'osis')->value('id');
        if ($osisId && $terpilih && (int) $terpilih === (int) $osisId) {
            return $user->isStaff()
                ? redirect()->route('ekstrakurikuler.index')->with('error', 'Untuk OSIS, pakai Kelola User: "Masukkan ke jalur CaSis" atau "Jadikan pengurus OSIS".')
                : redirect()->route('casis.create');
        }
        $ekskuls = $ekskuls->reject(fn ($e) => $e->jenis === 'osis')->values();

        // Siswa tidak bisa memilih ekskul yang pendaftarannya ditutup / kuotanya penuh
        if (!$user->isStaff()) {
            $ekskuls = $ekskuls->filter(fn ($e) => $e->pendaftaran_terbuka)->values();

            if ($terpilih && !$ekskuls->contains('id', (int) $terpilih)) {
                $tutup = Ekstrakurikuler::find($terpilih);

                return redirect()->route('ekstrakurikuler.index')
                    ->with('error', ($tutup ? $tutup->label_pendaftaran . ': ' . $tutup->nama_ekstrakurikuler : 'Ekskul tidak tersedia') . '.');
            }
        }

        return view('pendaftaran.create', compact('ekskuls', 'terpilih'));
    }

    public function store(Request $request)
    {
        $user  = Auth::user();
        $pesan = ['ekstrakurikuler_id.exists' => 'Ekskul tidak ditemukan atau sedang nonaktif.'];

        if ($user->isStaff()) {
            // Admin/pembina mendaftarkan orang lain secara manual (boleh walau pendaftaran umum ditutup)
            $data = $request->validate([
                'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id,status,aktif',
                'nama_pendaftar'     => 'required|string|max:255',
                'peran'              => 'required|in:siswa,admin,pembina',
                'kelas'              => 'required_if:peran,siswa|nullable|string|max:50',
                'nisn'               => 'nullable|string|max:30',
                'alasan_mendaftar'   => 'nullable|string',
            ], $pesan);

            $ekskul = Ekstrakurikuler::findOrFail($data['ekstrakurikuler_id']);
            Gate::authorize('kelola', $ekskul);

            if ($ekskul->jenis === 'osis') {
                return back()->withInput()->with('error', 'Untuk OSIS, pakai Kelola User: "Masukkan ke jalur CaSis" atau "Jadikan pengurus OSIS".');
            }

            $data['user_id'] = null;

            $sudah = PendaftaranEkskul::where('ekstrakurikuler_id', $data['ekstrakurikuler_id'])
                ->whereRaw('LOWER(nama_pendaftar) = ?', [mb_strtolower($data['nama_pendaftar'])])
                ->where('status_anggota', 'aktif')   // yang sudah keluar boleh didaftarkan lagi
                ->exists();
        } else {
            // Siswa: identitas diambil dari akun yang login, bukan dari input form
            $data = $request->validate([
                'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id,status,aktif',
                'alasan_mendaftar'   => 'nullable|string',
            ], $pesan);

            $ekskul = Ekstrakurikuler::withCount(['pendaftaran as anggota_count' => fn ($q) => $q->where('status', 'diterima')->where('status_anggota', 'aktif')])
                ->findOrFail($data['ekstrakurikuler_id']);

            // Ekskul OSIS: pendaftaran lewat form CaSis
            if ($ekskul->jenis === 'osis') {
                return redirect()->route('casis.create');
            }

            // Aturan buka/tutup dan kuota dicek di SERVER, bukan hanya lewat teks tombol
            if (!$ekskul->pendaftaran_terbuka) {
                return redirect()->route('ekstrakurikuler.show', $ekskul->id)
                    ->with('error', $ekskul->label_pendaftaran . '. Hubungi pembina kalau kamu perlu didaftarkan.');
            }

            $data += [
                'user_id'        => $user->id,
                'nama_pendaftar' => $user->name,
                'peran'          => 'siswa',
                'kelas'          => $user->kelas,
                'nisn'           => $user->nisn,
            ];

            $sudah = PendaftaranEkskul::where('ekstrakurikuler_id', $data['ekstrakurikuler_id'])
                ->where('user_id', $user->id)
                ->where('status_anggota', 'aktif')   // yang sudah keluar boleh mendaftar lagi
                ->exists();
        }

        if ($sudah) {
            return back()->withInput()->with('error', 'Sudah terdaftar di ekskul tersebut.');
        }

        $data['status'] = 'pending';
        PendaftaranEkskul::create($data);

        // Beri tahu admin & pembina ekskul itu
        Pemberitahuan::kirim(
            Pemberitahuan::pengelola($ekskul),
            'Pendaftaran baru',
            "{$data['nama_pendaftar']} mendaftar ke {$ekskul->nama_ekstrakurikuler}.",
            route('pendaftaran.index', [], false),
            '📥',
            $user->id
        );

        return redirect()->route('pendaftaran.index')->with('success', 'Pendaftaran berhasil dikirim!');
    }

    // Hanya admin atau pembina ekskul yang bersangkutan
    public function updateStatus(Request $request, PendaftaranEkskul $pendaftaran)
    {
        Gate::authorize('kelola', $pendaftaran->ekstrakurikuler);

        $data = $request->validate([
            'status' => 'required|in:pending,diterima,ditolak',
            'alasan' => 'nullable|string',
        ]);

        $sebelumnya = $pendaftaran->status;

        $pendaftaran->update([
            'status' => $data['status'],
            'alasan' => $data['alasan'],
        ]);

        // Saat pertama kali disetujui, catat tahun mulai keanggotaan
        if ($data['status'] === 'diterima' && !$pendaftaran->tahun_mulai) {
            $pendaftaran->update(['tahun_mulai' => now()->year]);
        }

        // Beri tahu pendaftar kalau statusnya berubah jadi disetujui/ditolak
        if ($pendaftaran->user && $data['status'] !== $sebelumnya && in_array($data['status'], ['diterima', 'ditolak'], true)) {
            $setuju = $data['status'] === 'diterima';

            Pemberitahuan::kirim(
                [$pendaftaran->user],
                $setuju ? 'Pendaftaran disetujui' : 'Pendaftaran ditolak',
                'Pendaftaranmu ke ' . $pendaftaran->ekstrakurikuler->nama_ekstrakurikuler . ($setuju ? ' disetujui. Selamat bergabung!' : ' tidak disetujui.')
                    . (!$setuju && $data['alasan'] ? ' Catatan: ' . $data['alasan'] : ''),
                route('ekstrakurikuler.show', $pendaftaran->ekstrakurikuler_id, false),
                $setuju ? '🎉' : '❌',
                $request->user()->id
            );
        }

        return redirect()->route('pendaftaran.index')->with('success', 'Status pendaftaran berhasil disimpan!');
    }
}