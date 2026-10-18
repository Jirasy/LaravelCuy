<?php

namespace App\Http\Controllers;

use App\Enums\Hari;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// Profil publik: semua user yang login bisa melihat profil user lain.
// Edit profil hanya untuk pemilik akun.
class ProfilPenggunaController extends Controller
{
    public function show(Request $request, ?User $user = null)
    {
        $viewer = $request->user();
        $user   = $user ?? $viewer;
        $isSelf = $user->id === $viewer->id;

        // Akun nonaktif hanya terlihat oleh admin/pembina dan pemiliknya
        abort_if(!$user->is_active && !$isSelf && !$viewer->isStaff(), 404);

        // Keanggotaan aktif (disetujui dan belum selesai) + ekskul yang dibina
        $keanggotaan = PendaftaranEkskul::with(['ekstrakurikuler.haris', 'jabatanAktif'])
            ->where('user_id', $user->id)
            ->where('status', 'diterima')
            ->where('status_anggota', 'aktif')
            ->get()
            ->filter(fn ($p) => $p->ekstrakurikuler);

        $dibina = $user->isPembina() ? $user->ekskulDikelola()->with('haris')->get() : collect();

        // Badge ekskul (non-interaktif) + jabatan
        $badges = collect();
        foreach ($dibina as $e) {
            $badges->push(['ekskul' => $e->nama_ekstrakurikuler, 'jabatan' => 'Pembina']);
        }
        foreach ($keanggotaan as $p) {
            $badges->push([
                'ekskul'  => $p->ekstrakurikuler->nama_ekstrakurikuler,
                'jabatan' => $p->jabatanAktif->isEmpty() ? null : $p->jabatan_label,
            ]);
        }

        // Jadwal gabungan semua ekskul, dikelompokkan per hari
        $semua = collect();
        foreach ($dibina as $e) {
            foreach ($e->haris as $j) {
                $semua->push($this->barisJadwal($e->id, $e->nama_ekstrakurikuler, $j, 'Pembina'));
            }
        }
        foreach ($keanggotaan as $p) {
            foreach ($p->ekstrakurikuler->haris as $j) {
                $semua->push($this->barisJadwal($p->ekstrakurikuler->id, $p->ekstrakurikuler->nama_ekstrakurikuler, $j, $p->jabatan_label));
            }
        }

        $perHari = collect(Hari::cases())->mapWithKeys(
            fn ($h) => [$h->value => $semua->where('hari', $h->value)->sortBy('mulai')->values()]
        );

        // Hari yang ditampilkan: ?hari=rabu, kalau kosong memakai hari ini (ikut APP_TIMEZONE)
        $hariIni     = Hari::cases()[now()->dayOfWeekIso - 1]->value;
        $hariDipilih = $perHari->has($request->query('hari')) ? $request->query('hari') : $hariIni;

        return view('profil.show', [
            'user'         => $user,
            'isSelf'       => $isSelf,
            'badges'       => $badges,
            'perHari'      => $perHari,
            'hariIni'      => $hariIni,
            'hariDipilih'  => $hariDipilih,
            'bisaWa'       => $viewer->can('lihatNoHp', $user),
            'bisaRiwayat'  => $isSelf || $viewer->isStaff(),
            'lihatDataAkun' => $isSelf || $viewer->isStaff(),
        ]);
    }

    public function edit(Request $request)
    {
        return view('profil.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        // Rapikan nomor HP sebelum divalidasi: buang spasi, strip, titik, dan tanda kurung
        $request->merge([
            'no_hp' => $request->filled('no_hp') ? preg_replace('/[\s\-\.\(\)]/', '', (string) $request->input('no_hp')) : null,
        ]);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'no_hp'            => ['nullable', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'no_hp_tampil'     => ['required', Rule::in(array_keys(User::NO_HP_TAMPIL))],
            'bio'              => 'nullable|string|max:160',
            'angkatan_sekolah' => 'nullable|string|max:20',
        ], [
            'foto.image'        => 'File foto harus berupa gambar.',
            'foto.mimes'        => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'foto.max'          => 'Ukuran foto maksimal 2 MB.',
            'no_hp.regex'       => 'Nomor HP tidak valid. Contoh: 081234567890 atau +6281234567890.',
            'bio.max'           => 'Bio maksimal 160 karakter.',
        ]);

        $user = $request->user();
        $user->name             = $data['name'];
        $user->no_hp            = $data['no_hp'] ? $this->normalisasiNoHp($data['no_hp']) : null;
        $user->no_hp_tampil     = $data['no_hp_tampil'];
        $user->bio              = isset($data['bio']) ? trim($data['bio']) : null;
        $user->angkatan_sekolah = $data['angkatan_sekolah'] ?? null;

        if ($request->hasFile('foto')) {
            $this->hapusFile($user->foto);
            $user->foto = $request->file('foto')->store('avatars', 'public');
        }

        $user->save();

        return redirect()->route('profil.show')->with('success', 'Profil berhasil diperbarui.');
    }

    public function hapusFoto(Request $request)
    {
        $user = $request->user();
        $this->hapusFile($user->foto);
        $user->foto = null;
        $user->save();

        return back()->with('success', 'Foto profil dihapus.');
    }

    // 0812... atau +62812... -> 62812... (format yang dipakai wa.me)
    private function normalisasiNoHp(string $nomor): string
    {
        $digit = ltrim($nomor, '+');

        return str_starts_with($digit, '0') ? '62' . substr($digit, 1) : $digit;
    }

    private function barisJadwal(int $ekskulId, string $nama, $jadwal, string $peran): array
    {
        return [
            'ekskul_id' => $ekskulId,
            'ekskul'    => $nama,
            'hari'      => $jadwal->hari,
            'mulai'     => substr($jadwal->jam_mulai, 0, 5),
            'selesai'   => substr($jadwal->jam_selesai, 0, 5),
            'peran'     => $peran,
        ];
    }

    private function hapusFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}