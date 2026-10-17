<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
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
        $query = Ekstrakurikuler::with('haris')->where('status', 'aktif')->orderBy('nama_ekstrakurikuler');

        // Pembina mendaftarkan orang lain hanya ke ekskul yang ia bina
        if ($user->isPembina()) {
            $query->whereIn('id', $user->idEkskulDikelola());
        }

        $ekskuls  = $query->get();
        $terpilih = $request->query('ekstrakurikuler_id', $request->query('ekskul'));
        return view('pendaftaran.create', compact('ekskuls', 'terpilih'));
    }

    public function store(Request $request)
    {
        $user  = Auth::user();
        $pesan = ['ekstrakurikuler_id.exists' => 'Ekskul tidak ditemukan atau sedang nonaktif.'];

        if ($user->isStaff()) {
            // Admin/pembina mendaftarkan orang lain secara manual
            $data = $request->validate([
                'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id,status,aktif',
                'nama_pendaftar'     => 'required|string|max:255',
                'peran'              => 'required|in:siswa,admin,pembina',
                'kelas'              => 'required_if:peran,siswa|nullable|string|max:50',
                'nisn'               => 'nullable|string|max:30',
                'alasan_mendaftar'   => 'nullable|string',
            ], $pesan);

            Gate::authorize('kelola', Ekstrakurikuler::findOrFail($data['ekstrakurikuler_id']));

            $data['user_id'] = null;

            $sudah = PendaftaranEkskul::where('ekstrakurikuler_id', $data['ekstrakurikuler_id'])
                ->whereRaw('LOWER(nama_pendaftar) = ?', [mb_strtolower($data['nama_pendaftar'])])
                ->exists();
        } else {
            // Siswa: identitas diambil dari akun yang login, bukan dari input form
            $data = $request->validate([
                'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id,status,aktif',
                'alasan_mendaftar'   => 'nullable|string',
            ], $pesan);

            $data += [
                'user_id'        => $user->id,
                'nama_pendaftar' => $user->name,
                'peran'          => 'siswa',
                'kelas'          => $user->kelas,
                'nisn'           => $user->nisn,
            ];

            $sudah = PendaftaranEkskul::where('ekstrakurikuler_id', $data['ekstrakurikuler_id'])
                ->where('user_id', $user->id)
                ->exists();
        }

        if ($sudah) {
            return back()->withInput()->with('error', 'Sudah terdaftar di ekskul tersebut.');
        }

        $data['status'] = 'pending';
        PendaftaranEkskul::create($data);

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

        $pendaftaran->update([
            'status' => $data['status'],
            'alasan' => $data['alasan'],
        ]);

        // Saat pertama kali disetujui, catat tahun mulai keanggotaan
        if ($data['status'] === 'diterima' && !$pendaftaran->tahun_mulai) {
            $pendaftaran->update(['tahun_mulai' => now()->year]);
        }

        return redirect()->route('pendaftaran.index')->with('success', 'Status pendaftaran berhasil disimpan!');
    }
}