<?php

namespace App\Http\Controllers;

use App\Models\EkskulSaran;
use App\Models\Ekstrakurikuler;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EkskulSaranController extends Controller
{
    // Semua pengguna: form kirim + riwayat kirimannya sendiri
    public function index(Request $request)
    {
        $ekskulOpsi = Ekstrakurikuler::where('status', 'aktif')->orderBy('nama_ekstrakurikuler')->get(['id', 'nama_ekstrakurikuler']);
        $terpilih   = $request->query('ekskul');

        $saya = EkskulSaran::with('ekstrakurikuler')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('saran.index', compact('ekskulOpsi', 'terpilih', 'saya'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ekstrakurikuler_id' => 'nullable|integer|exists:ekstrakurikulers,id',
            'jenis'              => ['required', Rule::in(['kritik', 'saran'])],
            'isi'                => 'required|string|min:10|max:2000',
            'anonim'             => 'nullable|boolean',
        ], [
            'isi.required' => 'Isi kritik atau saran wajib diisi.',
            'isi.min'      => 'Tulis minimal 10 karakter supaya jelas.',
        ]);

        $user   = $request->user();
        $anonim = $request->boolean('anonim');
        $ekskul = !empty($data['ekstrakurikuler_id']) ? Ekstrakurikuler::find($data['ekstrakurikuler_id']) : null;

        EkskulSaran::create([
            'user_id'            => $user->id,
            'ekstrakurikuler_id' => $ekskul?->id,
            'jenis'              => $data['jenis'],
            'isi'                => trim($data['isi']),
            'anonim'             => $anonim,
        ]);

        // Beri tahu yang berwenang: pengelola ekskul itu, atau admin untuk saran umum
        $penerima = $ekskul ? Pemberitahuan::pengelola($ekskul) : Pemberitahuan::admin();
        $label    = $data['jenis'] === 'kritik' ? 'Kritik' : 'Saran';
        $dari     = $anonim ? 'seorang pengirim anonim' : $user->name;

        Pemberitahuan::kirim(
            $penerima,
            "{$label} baru",
            "{$label} dari {$dari}" . ($ekskul ? " untuk {$ekskul->nama_ekstrakurikuler}." : ' untuk sekolah.'),
            route('saran.masuk', [], false),
            '💬',
            $user->id
        );

        return redirect()->route('saran.index')->with('success', 'Terima kasih, ' . strtolower($label) . 'mu sudah terkirim.');
    }

    // Kotak masuk: admin (semua) dan pembina (ekskul yang ia bina)
    public function masuk(Request $request)
    {
        $user   = $request->user();
        $status = $request->query('status');

        $daftar = EkskulSaran::with(['ekstrakurikuler', 'pengirim'])
            ->untukPengelola($user)
            ->when(array_key_exists((string) $status, EkskulSaran::STATUS), fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $jumlah = EkskulSaran::untukPengelola($user)->where('status', 'baru')->count();

        return view('saran.masuk', compact('daftar', 'status', 'jumlah'));
    }

    public function tanggapi(Request $request, EkskulSaran $saran)
    {
        Gate::authorize('tanggapi', $saran);

        $data = $request->validate([
            'status'  => ['required', Rule::in(array_keys(EkskulSaran::STATUS))],
            'balasan' => 'nullable|string|max:2000',
        ]);

        $balasan  = trim($data['balasan'] ?? '') ?: null;
        $baruBalas = $balasan && $balasan !== $saran->balasan;

        $saran->update([
            'status'       => $data['status'],
            'balasan'      => $balasan,
            'dibalas_oleh' => $baruBalas ? $request->user()->id : $saran->dibalas_oleh,
            'dibalas_pada' => $baruBalas ? now() : $saran->dibalas_pada,
        ]);

        // Pengirim diberi tahu walau kirimannya anonim (identitasnya tidak ditampilkan ke pengelola)
        if ($baruBalas && $saran->pengirim) {
            Pemberitahuan::kirim(
                [$saran->pengirim],
                'Kritik/saranmu dibalas',
                'Ada balasan untuk ' . ($saran->jenis === 'kritik' ? 'kritik' : 'saran') . 'mu.',
                route('saran.index', [], false),
                '💬',
                $request->user()->id
            );
        }

        return back()->with('success', 'Tanggapan disimpan.');
    }
}