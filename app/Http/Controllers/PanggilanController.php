<?php

namespace App\Http\Controllers;

use App\Models\UserPanggilan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Nama panggilan milik sendiri. Satu di antaranya ("utama") dipakai untuk sapaan di dashboard.
class PanggilanController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $request->merge([
            'nama' => trim((string) preg_replace('/\s+/u', ' ', (string) $request->input('nama'))),
        ]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'min:2', 'max:' . UserPanggilan::MAKS_KARAKTER],
        ], [
            'nama.required' => 'Nama panggilan wajib diisi.',
            'nama.min' => 'Nama panggilan minimal 2 karakter.',
            'nama.max' => 'Nama panggilan maksimal ' . UserPanggilan::MAKS_KARAKTER . ' karakter.',
        ]);

        $ada = $user->panggilan()->get(['id', 'nama']);

        if ($ada->count() >= UserPanggilan::MAKS_PER_USER) {
            return back()->withErrors(['nama' => 'Maksimal ' . UserPanggilan::MAKS_PER_USER . ' nama panggilan. Hapus salah satu dulu.'])->withInput();
        }

        if ($ada->contains(fn ($p) => mb_strtolower($p->nama) === mb_strtolower($data['nama']))) {
            return back()->withErrors(['nama' => 'Kamu sudah punya nama panggilan itu.'])->withInput();
        }

        UserPanggilan::create([
            'user_id' => $user->id,
            'nama' => $data['nama'],
            'utama' => $ada->isEmpty(), // panggilan pertama otomatis jadi sapaan
        ]);

        return redirect()->route('profil.edit')->with('success', 'Nama panggilan ditambahkan.');
    }

    public function jadikanUtama(Request $request, int $panggilan)
    {
        $user = $request->user();
        $dipilih = $user->panggilan()->findOrFail($panggilan); // milik orang lain = 404

        DB::transaction(function () use ($user, $dipilih) {
            UserPanggilan::where('user_id', $user->id)->update(['utama' => false]);
            $dipilih->update(['utama' => true]);
        });

        return redirect()->route('profil.edit')->with('success', 'Sapaan di dashboard diganti jadi "' . $dipilih->nama . '".');
    }

    public function hapus(Request $request, int $panggilan)
    {
        $dipilih = $request->user()->panggilan()->findOrFail($panggilan);
        $dipilih->delete(); // kalau yang utama dihapus, sapaan kembali memakai nama depan

        return redirect()->route('profil.edit')->with('success', 'Nama panggilan dihapus.');
    }
}
