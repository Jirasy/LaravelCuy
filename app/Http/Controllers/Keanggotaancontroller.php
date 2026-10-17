<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Atur jabatan, angkatan ekskul, dan periode keanggotaan. Hanya admin & pembina.
class KeanggotaanController extends Controller
{
    public function update(Request $request, PendaftaranEkskul $pendaftaran)
    {
        Gate::authorize('kelola', $pendaftaran->ekstrakurikuler);

        $rules = [
            'jabatan'         => ['required', Rule::in(array_keys(PendaftaranEkskul::JABATAN))],
            'angkatan_ekskul' => 'nullable|string|max:20',
            'tahun_mulai'     => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'tahun_selesai'   => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 10)],
        ];

        // gte hanya dipakai kalau tahun mulai diisi (kalau tidak, aturan ini error)
        if ($request->filled('tahun_mulai')) {
            $rules['tahun_selesai'][] = 'gte:tahun_mulai';
        }

        $pendaftaran->update($request->validate($rules));

        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $pendaftaran->ekstrakurikuler_id, 'tab' => 'anggota'])
            ->with('success', 'Data keanggotaan ' . $pendaftaran->nama_pendaftar . ' diperbarui.');
    }
}