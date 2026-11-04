<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

// Buka/tutup pendaftaran dan kuota ekskul: admin, pembina ekskul itu, atau penanggung jawab anggota.
class PendaftaranKontrolController extends Controller
{
    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('kelolaAnggota', $ekstrakurikuler);

        $data = $request->validate([
            'pendaftaran_dibuka'     => 'required|boolean',
            'kuota'                  => 'nullable|integer|min:1|max:1000',
            'pendaftaran_tutup_pada' => 'nullable|date|after:now',
        ], [
            'pendaftaran_tutup_pada.after' => 'Waktu tutup otomatis harus di masa depan.',
        ]);

        $ekstrakurikuler->update([
            'pendaftaran_dibuka'     => $request->boolean('pendaftaran_dibuka'),
            'kuota'                  => $data['kuota'] ?? null,
            'pendaftaran_tutup_pada' => $data['pendaftaran_tutup_pada'] ?? null,
        ]);

        return back()->with('success', $request->boolean('pendaftaran_dibuka')
            ? 'Pengaturan pendaftaran disimpan. Pendaftaran dibuka.'
            : 'Pengaturan pendaftaran disimpan. Pendaftaran ditutup.');
    }
}