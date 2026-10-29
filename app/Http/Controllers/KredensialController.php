<?php

namespace App\Http\Controllers;

use App\Models\KredensialSementara;
use Illuminate\Http\Request;

// Riwayat password sementara akun yang dibuatkan admin. Hanya admin (dijaga di routes/web.php).
class KredensialController extends Controller
{
    public function index()
    {
        $daftar = KredensialSementara::with(['user', 'ekskul', 'pembuat', 'pelihat'])->latest()->get();

        return view('kredensial.index', compact('daftar'));
    }

    // Password tidak pernah ada di HTML halaman daftar; baru dibuka ketika admin menekan tombolnya.
    public function tampilkan(Request $request, KredensialSementara $kredensial)
    {
        $password = $kredensial->passwordAsli();

        if ($kredensial->sudahKedaluwarsa() || $password === null) {
            return back()->with('error', 'Kredensial ini sudah kedaluwarsa atau tidak bisa dibaca. Atur ulang passwordnya lewat Kelola User.');
        }

        $kredensial->update([
            'terakhir_dilihat_pada'  => now(),
            'terakhir_dilihat_oleh'  => $request->user()->id,
        ]);

        return back()->with('kredensial_dibuka', ['id' => $kredensial->id, 'password' => $password]);
    }

    public function hapus(KredensialSementara $kredensial)
    {
        $nama = $kredensial->user?->name ?? 'akun ini';
        $kredensial->delete();

        return back()->with('success', "Kredensial {$nama} dihapus.");
    }
}
