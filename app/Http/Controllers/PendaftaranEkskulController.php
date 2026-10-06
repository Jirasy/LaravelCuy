<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendaftaranEkskulController extends Controller
{
    public function index()
    {
        $pendaftarans = PendaftaranEkskul::with(['user', 'ekstrakurikuler'])->latest()->get();
        return view('pendaftaran.index', compact('pendaftarans'));
    }

    public function create(Request $request)
    {
        $ekskuls  = Ekstrakurikuler::where('status', 'aktif')->orderBy('nama_ekstrakurikuler')->get();
        $terpilih = $request->query('ekstrakurikuler_id', $request->query('ekskul'));
        return view('pendaftaran.create', compact('ekskuls', 'terpilih'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id',
            'nama_pendaftar'     => 'required|string|max:255',
            'peran'              => 'required|in:siswa,admin,pembina',
            'kelas'              => 'required_if:peran,siswa|nullable|string|max:50',
            'nisn'               => 'nullable|string|max:30',
            'alasan_mendaftar'   => 'nullable|string',
        ]);

        // Cegah pendaftar yang sama mendaftar dua kali ke ekskul yang sama
        $sudah = PendaftaranEkskul::where('ekstrakurikuler_id', $data['ekstrakurikuler_id'])
            ->whereRaw('LOWER(nama_pendaftar) = ?', [mb_strtolower($data['nama_pendaftar'])])
            ->exists();

        if ($sudah) {
            return back()->withInput()->with('error', 'Nama ini sudah terdaftar di ekskul tersebut.');
        }

        $data['user_id'] = Auth::id(); // null jika belum ada login
        $data['status']  = 'pending';

        PendaftaranEkskul::create($data);

        return redirect()->route('pendaftaran.index')->with('success', 'Pendaftaran berhasil dikirim!');
    }

    public function updateStatus(Request $request, PendaftaranEkskul $pendaftaran)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,diterima,ditolak',
            'alasan' => 'nullable|string',
        ]);

        $pendaftaran->update([
            'status' => $data['status'],
            // Kosongkan hanya jika memang dikosongkan di form (form sudah berisi alasan lama)
            'alasan' => $data['alasan'],
        ]);

        return redirect()->route('pendaftaran.index')->with('success', 'Keputusan pendaftaran berhasil disimpan!');
    }
}