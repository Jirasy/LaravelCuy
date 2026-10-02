<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendaftaranEkskulController extends Controller
{
    public function index()
    {
        $pendaftarans = PendaftaranEkskul::with(['user', 'ekstrakurikuler'])->latest()->get();
        return view('pendaftaran.index', compact('pendaftarans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id',
        ]);

        // Sementara belum ada login: pakai user siswa pertama sebagai pengganti.
        $userId = Auth::id() ?? User::where('role', 'siswa')->value('id');

        if (!$userId) {
            return back()->with('error', 'Belum ada user siswa. Jalankan seeder dulu.');
        }

        // Cegah daftar dobel ke ekskul yang sama
        $sudah = PendaftaranEkskul::where('user_id', $userId)
            ->where('ekstrakurikuler_id', $request->ekstrakurikuler_id)
            ->exists();

        if ($sudah) {
            return redirect()->route('ekstrakurikuler.index')
                             ->with('error', 'Kamu sudah terdaftar di ekskul ini.');
        }

        PendaftaranEkskul::create([
            'user_id'            => $userId,
            'ekstrakurikuler_id' => $request->ekstrakurikuler_id,
            'status'             => 'pending',
        ]);

        return redirect()->route('pendaftaran.index')->with('success', 'Berhasil mendaftar!');
    }

    public function updateStatus(Request $request, PendaftaranEkskul $pendaftaran)
    {
        $data = $request->validate([
            'status' => 'required|in:diterima,ditolak',
            'alasan' => 'nullable|string',
        ]);

        $pendaftaran->update($data);

        return redirect()->route('pendaftaran.index')->with('success', 'Status pendaftaran berhasil diperbarui!');
    }
}