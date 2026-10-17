<?php

namespace App\Http\Controllers;

use App\Enums\Hari;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $ekskuls = Ekstrakurikuler::with(['haris', 'pembinaAktif'])->latest()->get();
        return view('ekstrakurikuler.index', compact('ekskuls'));
    }

    // Halaman detail satu ekskul: tab Informasi / Prestasi / Event / Pengumuman / Anggota
    public function show(Request $request, $id)
    {
        $user = Auth::user();

        $ekskul = Ekstrakurikuler::with(['haris', 'pembinaAktif'])
            ->withCount(['pendaftaran as anggota_count' => fn ($q) => $q->where('status', 'diterima')])
            ->findOrFail($id);

        // Hak kelola per ekskul: admin, atau pembina yang ditugaskan di ekskul ini.
        // (variabel $staff dipakai view untuk menampilkan form/tombol kelola)
        $staff = $user->can('kelola', $ekskul);

        // Pendaftaran milik user yang sedang login (kalau ada)
        $pendaftaranSaya = PendaftaranEkskul::where('ekstrakurikuler_id', $ekskul->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        // Pengumuman & daftar anggota: hanya admin/pembina dan anggota yang sudah disetujui.
        // Prestasi & event terbuka untuk semua user yang login.
        $bolehLihat = $staff || ($pendaftaranSaya && $pendaftaranSaya->status === 'diterima');

        $tab = in_array($request->query('tab'), ['info', 'struktur', 'prestasi', 'event', 'pengumuman', 'anggota'], true)
            ? $request->query('tab')
            : 'info';

        $pengumumans = collect();
        $anggota = collect();
        $prestasis = collect();
        $events = collect();
        $anggotaOpsi = collect();

        if ($bolehLihat && $tab === 'pengumuman') {
            $pengumumans = $ekskul->pengumuman()->with('penulis')->latest()->get();
        }

        if ($bolehLihat && $tab === 'anggota') {
            $anggota = PendaftaranEkskul::with('jabatanAktif')->where('ekstrakurikuler_id', $ekskul->id)
                ->where('status', 'diterima')
                ->orderBy('nama_pendaftar')
                ->get();
        }

        if ($tab === 'prestasi') {
            $prestasis = $ekskul->prestasi()->with('anggota')->orderByDesc('tahun')->latest()->get();

            if ($staff) {
                $anggotaOpsi = PendaftaranEkskul::where('ekstrakurikuler_id', $ekskul->id)
                    ->where('status', 'diterima')
                    ->orderBy('nama_pendaftar')
                    ->get();
            }
        }

        if ($tab === 'event') {
            $events = $ekskul->events()->orderBy('tanggal_mulai')->get();
        }

        // Daftar jabatan: untuk tab Struktur, dan untuk form "beri jabatan" di tab Anggota (khusus pengelola)
        $jabatans = collect();
        if ($tab === 'struktur' || ($tab === 'anggota' && $staff)) {
            $jabatans = $ekskul->jabatans()->with('pemegangAktif')->get();
        }

        return view('ekstrakurikuler.show', compact(
            'ekskul', 'tab', 'staff', 'bolehLihat', 'pendaftaranSaya',
            'pengumumans', 'anggota', 'prestasis', 'events', 'anggotaOpsi', 'jabatans'
        ));
    }

    public function create()
    {
        return view('ekstrakurikuler.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request, false), $this->messages());
        $data['status'] = $data['status'] ?? 'aktif';

        DB::transaction(function () use ($data) {
            $ekskul = Ekstrakurikuler::create(
                Arr::only($data, ['nama_ekstrakurikuler', 'pembina', 'deskripsi', 'status'])
            );

            // Pembina yang membuat ekskul otomatis ditugaskan sebagai pembinanya
            if (Auth::user()->isPembina()) {
                $ekskul->pembinas()->attach(Auth::id(), ['mulai' => now()->toDateString()]);
            }
            $ekskul->syncJadwal($this->susunJadwal($data));
        });

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Ekstrakurikuler berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $ekstrakurikuler = Ekstrakurikuler::with(['haris', 'pembinaAktif'])->findOrFail($id);
        Gate::authorize('kelola', $ekstrakurikuler);

        // Hanya admin yang menugaskan pembina: daftar calon = user pembina aktif yang belum bertugas di ekskul ini
        $calonPembina = Auth::user()->isAdmin()
            ? User::where('role', 'pembina')
                ->where('is_active', true)
                ->whereNotIn('id', $ekstrakurikuler->pembinaAktif->pluck('id'))
                ->orderBy('name')
                ->get()
            : collect();

        return view('ekstrakurikuler.edit', compact('ekstrakurikuler', 'calonPembina'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate($this->rules($request, true), $this->messages());
        $ekskul = Ekstrakurikuler::findOrFail($id);
        Gate::authorize('kelola', $ekskul);

        DB::transaction(function () use ($ekskul, $data) {
            $ekskul->update(
                Arr::only($data, ['nama_ekstrakurikuler', 'pembina', 'deskripsi', 'status'])
            );
            $ekskul->syncJadwal($this->susunJadwal($data));
        });

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Data ekstrakurikuler berhasil diperbarui!');
    }

    public function destroy($id)
    {
        // Menghapus ekskul = menghapus semua data turunannya (anggota, prestasi, event, ...). Hanya admin.
        abort_unless(Auth::user()->isAdmin(), 403, 'Hanya admin yang boleh menghapus ekskul.');

        Ekstrakurikuler::findOrFail($id)->delete(); // semua data turunan ikut terhapus (cascade)

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Ekstrakurikuler berhasil dihapus!');
    }

    // Ubah input form (hari[] + jadwal[hari][jam_*]) menjadi daftar jadwal
    private function susunJadwal(array $data): array
    {
        return collect($data['hari'])
            ->unique()
            ->map(fn ($h) => [
                'hari'        => $h,
                'jam_mulai'   => $data['jadwal'][$h]['jam_mulai'],
                'jam_selesai' => $data['jadwal'][$h]['jam_selesai'],
            ])
            ->values()
            ->all();
    }

    private function rules(Request $request, bool $statusWajib): array
    {
        $valid = array_map(fn ($c) => $c->value, Hari::cases());

        $rules = [
            'nama_ekstrakurikuler' => 'required|string|max:255',
            'pembina'   => 'required|string|max:255',
            'hari'      => 'required|array|min:1',
            'hari.*'    => ['required', Rule::in($valid)],
            'deskripsi' => 'nullable|string',
            'status'    => ($statusWajib ? 'required' : 'nullable') . '|in:aktif,nonaktif',
        ];

        // Jam wajib diisi untuk SETIAP hari yang dipilih
        $dipilih = array_intersect(array_filter((array) $request->input('hari', []), 'is_string'), $valid);

        foreach ($dipilih as $h) {
            $rules["jadwal.$h.jam_mulai"]   = 'required|date_format:H:i,H:i:s';
            $rules["jadwal.$h.jam_selesai"] = "required|date_format:H:i,H:i:s|after:jadwal.$h.jam_mulai";
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'hari.required' => 'Pilih minimal satu hari pelaksanaan.',
            'hari.min'      => 'Pilih minimal satu hari pelaksanaan.',
            'jadwal.*.jam_mulai.required'   => 'Jam mulai wajib diisi untuk setiap hari yang dipilih.',
            'jadwal.*.jam_selesai.required' => 'Jam selesai wajib diisi untuk setiap hari yang dipilih.',
            'jadwal.*.jam_mulai.date_format'   => 'Format jam mulai tidak valid.',
            'jadwal.*.jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jadwal.*.jam_selesai.after'       => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}