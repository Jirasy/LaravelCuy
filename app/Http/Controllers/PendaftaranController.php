<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;

class PendaftaranController extends Controller
{
    // Menampilkan Form Pendaftaran
    public function create()
    {
        $ekstrakurikuler = Ekstrakurikuler::all();
        return view('pendaftaran.create', compact('ekstrakurikuler'));
    }

    // Menyimpan Data Pendaftaran
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id',
            'nama_siswa'        => 'required|string|max:255',
            'kelas'             => 'required|string|max:50',
            'pembina'           => 'nullable|string|max:255',
            'admin'             => 'nullable|string|max:255',
            'alasan'            => 'required|string',
        ]);

        Pendaftaran::create($validated);

        return redirect()->route('pendaftaran.index')
            ->with('success', 'Pendaftaran berhasil dikirim!');
    }
}