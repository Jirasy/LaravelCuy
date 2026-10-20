<?php

namespace App\Http\Controllers;

use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\User;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Hanya admin (dijaga di route): menugaskan / melepas pembina dari sebuah ekskul.
class EkskulPembinaController extends Controller
{
    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'pembina')->where('is_active', true)],
        ], [
            'user_id.required' => 'Pilih pembina yang akan ditugaskan.',
            'user_id.exists'   => 'Pembina tidak ditemukan atau akunnya nonaktif.',
        ]);

        $sudahAktif = EkskulPembina::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('user_id', $data['user_id'])
            ->whereNull('selesai')
            ->exists();

        if ($sudahAktif) {
            return back()->with('error', 'Pembina tersebut sudah bertugas di ekskul ini.');
        }

        EkskulPembina::create([
            'ekstrakurikuler_id' => $ekstrakurikuler->id,
            'user_id'            => $data['user_id'],
            'mulai'              => now()->toDateString(),
        ]);

        Pemberitahuan::kirim(
            [User::find($data['user_id'])],
            'Penugasan pembina',
            "Kamu ditugaskan sebagai pembina {$ekstrakurikuler->nama_ekstrakurikuler}.",
            route('ekstrakurikuler.show', $ekstrakurikuler->id, false),
            '🛡️',
            $request->user()->id
        );

        return back()->with('success', 'Pembina berhasil ditugaskan.');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler, User $user)
    {
        EkskulPembina::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('user_id', $user->id)
            ->whereNull('selesai')
            ->update(['selesai' => now()->toDateString()]);

        $sisa = $ekstrakurikuler->pembinaAktif()->count();

        return back()->with('success', $sisa === 0
            ? 'Pembina dilepas. Ekskul ini sekarang belum punya pembina.'
            : 'Pembina dilepas dari ekskul ini.');
    }
}