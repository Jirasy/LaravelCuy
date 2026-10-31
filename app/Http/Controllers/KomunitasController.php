<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\PendaftaranEkskul;
use App\Services\OsisService;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Komunitas = ekskul yang baru lahir dan belum diresmikan. Dibuat pembina atau siswa; admin meresmikannya
// (memilih tipe akhirnya). Kalau admin yang membuat ekskul, langsung resmi lewat form Tambah Ekskul biasa.
class KomunitasController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('ekstrakurikuler.create')
                ->with('error', 'Admin membuat ekskul resmi langsung lewat Tambah Ekskul. Komunitas dibuat pembina atau siswa.');
        }

        return view('komunitas.create');
    }

    public function store(Request $request, OsisService $osis)
    {
        $user = $request->user();

        abort_if($user->isAdmin(), 403, 'Admin membuat ekskul resmi langsung lewat Tambah Ekskul.');

        $data = $request->validate([
            'nama_ekstrakurikuler' => 'required|string|max:255',
            'deskripsi'            => 'nullable|string|max:2000',
        ], ['nama_ekstrakurikuler.required' => 'Isi nama komunitas.']);

        $tipe = JenisEkskul::where('kategori', 'komunitas')->first();

        if (! $tipe) {
            return back()->withInput()->with('error', 'Tipe Komunitas belum tersedia. Hubungi admin.');
        }

        $ekskul = DB::transaction(function () use ($data, $tipe, $user, $osis) {
            $ekskul = Ekstrakurikuler::create([
                'nama_ekstrakurikuler' => $data['nama_ekstrakurikuler'],
                'pembina'              => $user->isPembina() ? $user->name : '-',
                'deskripsi'            => $data['deskripsi'] ?? null,
                'status'               => 'aktif',
                'jenis_ekskul_id'      => $tipe->id,
                'dibuat_oleh'          => $user->id,
            ]);

            if ($user->isPembina()) {
                // Pembina pembuat otomatis menjadi pembina komunitasnya
                $ekskul->pembinas()->attach($user->id, ['mulai' => now()->toDateString()]);
            } else {
                // Siswa pembuat langsung menjadi anggota aktif komunitasnya
                $osis->jadikanAnggota($ekskul, $user, 'Pendiri komunitas');
            }

            return $ekskul;
        });

        Pemberitahuan::kirim(
            Pemberitahuan::admin(),
            'Komunitas baru menunggu peresmian',
            "{$user->name} membuat komunitas {$ekskul->nama_ekstrakurikuler}. Buka halamannya untuk meresmikannya jadi ekskul.",
            route('ekstrakurikuler.show', $ekskul->id, false),
            '🌱',
            $user->id
        );

        return redirect()->route('ekstrakurikuler.show', $ekskul->id)
            ->with('success', "Komunitas {$ekskul->nama_ekstrakurikuler} dibuat. Admin akan meresmikannya jadi ekskul.");
    }

    // Admin meresmikan komunitas: memilih tipe akhir (Ekskul Biasa, Ekskul Seleksi, Organisasi, dst)
    public function resmikan(Request $request, Ekstrakurikuler $ekskul)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Hanya admin yang boleh meresmikan komunitas.');

        if (! $ekskul->isKomunitas()) {
            return back()->with('error', 'Ekskul ini sudah resmi.');
        }

        $data = $request->validate(['jenis_ekskul_id' => 'required|integer'], ['jenis_ekskul_id.required' => 'Pilih tipe ekskul setelah diresmikan.']);

        $tipe = JenisEkskul::find($data['jenis_ekskul_id']);

        if (! $tipe || ! $tipe->aktif || $tipe->isKomunitas()) {
            return back()->with('error', 'Pilih tipe ekskul resmi yang aktif.');
        }

        if ($tipe->hanya_satu && Ekstrakurikuler::where('jenis_ekskul_id', $tipe->id)->where('id', '!=', $ekskul->id)->exists()) {
            return back()->with('error', "Sudah ada ekskul bertipe {$tipe->nama}. Tipe ini hanya boleh dimiliki satu ekskul.");
        }

        $ekskul->update([
            'jenis_ekskul_id' => $tipe->id,
            'diresmikan_pada' => now(),
            'diresmikan_oleh' => $request->user()->id,
        ]);

        $anggota = PendaftaranEkskul::anggotaAktif()->where('ekstrakurikuler_id', $ekskul->id)->whereNotNull('user_id')->with('user')->get()->pluck('user');

        Pemberitahuan::kirim(
            $anggota->merge($ekskul->pembinaAktif()->get())->push($ekskul->pembuat),
            'Komunitas diresmikan',
            "{$ekskul->nama_ekstrakurikuler} sekarang resmi menjadi ekskul ({$tipe->nama}).",
            route('ekstrakurikuler.show', $ekskul->id, false),
            '🎉',
            $request->user()->id
        );

        return back()->with('success', "{$ekskul->nama_ekstrakurikuler} diresmikan sebagai {$tipe->nama}.");
    }
}
