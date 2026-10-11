<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendaftaranEkskulController extends Controller
{
    // Admin/pembina: semua pendaftaran. Siswa: hanya miliknya sendiri.
    public function index()
    {
        $user  = Auth::user();
        $query = PendaftaranEkskul::with(['user', 'ekstrakurikuler'])->latest();

        if (!$user->isStaff()) {
            $query->where('user_id', $user->id);
        }

        $pendaftarans = $query->get();
        return view('pendaftaran.index', compact('pendaftarans'));
    }

    public function create(Request $request)
    {
        $ekskuls  = Ekstrakurikuler::with('haris')->where('status', 'aktif')->orderBy('nama_ekstrakurikuler')->get();
        $terpilih = $request->query('ekstrakurikuler_id', $request->query('ekskul'));
        return view('pendaftaran.create', compact('ekskuls', 'terpilih'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $pesan = ['ekstrakurikuler_id.exists' => 'Ekskul tidak ditemukan atau sedang nonaktif.'];

        if ($user->isStaff()) {
            // Admin/pembina boleh mendaftarkan orang lain secara manual
            $data = $request->validate([
                'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id,status,aktif',
                'nama_pendaftar'     => 'required|string|max:255',
                'peran'              => 'required|in:siswa,admin,pembina',
                'kelas'              => 'required_if:peran,siswa|nullable|string|max:50',
                'nisn'               => 'nullable|string|max:30',
                'alasan_mendaftar'   => 'nullable|string',
            ], $pesan);

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

    // Hanya admin/pembina (dijaga di route)
    public function updateStatus(Request $request, PendaftaranEkskul $pendaftaran)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,diterima,ditolak',
            'alasan' => 'nullable|string',
        ]);

        $pendaftaran->update([
            'status' => $data['status'],
            'alasan' => $data['alasan'],
        ]);

        return redirect()->route('pendaftaran.index')->with('success', 'Status pendaftaran berhasil disimpan!');
    }
}