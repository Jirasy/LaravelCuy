<?php

namespace App\Http\Controllers;

use App\Models\CasisPendaftaran;
use App\Models\EkskulPembina;
use App\Models\KredensialSementara;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use App\Rules\PasswordKuat;
use App\Services\OsisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// Dibuka untuk admin & pembina (di route). Batasan per akun diatur UserPolicy:
// pembina tidak boleh menyentuh akun admin; hanya admin yang boleh mengubah role.
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::orderByDesc('is_active')->orderBy('role')->orderBy('name')->get();

        // Status OSIS tiap siswa (lencana dan tombol di tabel). Cuma admin & pembina OSIS yang boleh mengubahnya.
        $layanan = app(OsisService::class);
        $ekskulOsis = $layanan->osis();
        $infoOsis = ['ada' => false];

        if ($ekskulOsis) {
            $periode = OsisPeriode::where('ekstrakurikuler_id', $ekskulOsis->id)
                ->whereIn('status', ['rekrutmen', 'seleksi'])
                ->latest()
                ->first();

            $boleh = $layanan->bolehMemutuskan($request->user());
            $riwayat = collect();
            $angkatan = [];

            // Riwayat kepengurusan OSIS tiap siswa + saran angkatan, hanya dimuat untuk yang berhak mengubahnya
            if ($boleh) {
                $riwayat = PendaftaranEkskul::where('ekstrakurikuler_id', $ekskulOsis->id)
                    ->where('status', 'diterima')
                    ->whereIn('status_anggota', array_keys(OsisService::STATUS_PENGURUS))
                    ->whereNotNull('user_id')
                    ->orderByDesc('tahun_mulai')
                    ->orderByDesc('id')
                    ->get()
                    ->groupBy('user_id');

                $angkatan = $riwayat->flatten()->pluck('angkatan_ekskul')
                    ->merge(OsisPeriode::where('ekstrakurikuler_id', $ekskulOsis->id)->pluck('nama'))
                    ->filter()->unique()->sortDesc()->values()->all();
            }

            $infoOsis = [
                'ada' => true,
                'riwayat' => $riwayat,
                'angkatan' => $angkatan,
                'periode' => $periode,
                'pengurus' => PendaftaranEkskul::anggotaAktif()
                    ->where('ekstrakurikuler_id', $ekskulOsis->id)
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
                    ->all(),
                'casis' => $periode
                    ? CasisPendaftaran::where('osis_periode_id', $periode->id)->pluck('status', 'user_id')->all()
                    : [],
                'boleh' => $boleh,
            ];
        }

        return view('users.index', compact('users', 'infoOsis'));
    }

    public function edit(User $user)
    {
        Gate::authorize('kelola', $user);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('kelola', $user);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'nisn' => ['nullable', 'string', 'max:30', Rule::unique('users', 'nisn')->ignore($user->id)],
            'kelas' => 'nullable|string|max:50',
            'password' => ['nullable', 'string', 'confirmed', new PasswordKuat($request->input('name'), $request->input('email'))],
            'angkatan_sekolah' => 'nullable|string|max:20',
            'tahun_lulus' => 'nullable|integer|min:1990|max:'.(date('Y') + 10),
        ]);

        $data['is_alumni'] = $request->boolean('is_alumni');

        if (empty($data['password'])) {
            unset($data['password']);
        } elseif ($user->id !== Auth::id()) {
            // Password sementara dari admin/pembina: user wajib menggantinya saat login berikutnya
            $data['must_change_password'] = true;
        }

        if (isset($data['password'])) {
            // Password lama yang tersimpan di Kredensial sudah tidak berlaku
            KredensialSementara::where('user_id', $user->id)->delete();
        }

        $user->update($data); // password di-hash otomatis oleh cast 'hashed'

        return redirect()->route('users.index')->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('ubahRole', $user);

        $data = $request->validate([
            'role' => 'required|in:admin,pembina,siswa',
        ]);

        $this->terapkanRole($user, $data['role']);

        return back()->with('success', "Role {$user->name} diubah menjadi {$data['role']}.");
    }

    // Aktifkan <-> nonaktifkan
    public function toggleActive(User $user)
    {
        Gate::authorize('kelola', $user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Kamu tidak bisa menonaktifkan akunmu sendiri.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $teks = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$teks}.");
    }

    public function destroy(User $user)
    {
        Gate::authorize('kelola', $user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        $nama = $user->name;
        $this->hapusAkun($user);

        return back()->with('success', "Akun {$nama} berhasil dihapus.");
    }

    // Aksi massal dari checkbox di Kelola User. Tiap akun tetap dicek UserPolicy; yang tidak boleh dilewati dan dilaporkan.
    public function massal(Request $request)
    {
        $data = $request->validate([
            'aksi' => ['required', Rule::in(self::AKSI_MASSAL)],
            'ids' => ['required', 'array', 'min:1', 'max:'.self::MAKS_MASSAL],
            'ids.*' => ['integer', 'distinct'],
            'role' => ['nullable', 'required_if:aksi,ubah_role', Rule::in(['admin', 'pembina', 'siswa'])],
            'kelas' => ['nullable', 'required_if:aksi,ubah_kelas', 'string', 'max:50'],
        ], [
            'aksi.required' => 'Pilih aksi massal dulu.',
            'aksi.in' => 'Aksi massal tidak dikenali.',
            'ids.required' => 'Pilih minimal satu akun dulu.',
            'ids.min' => 'Pilih minimal satu akun dulu.',
            'ids.max' => 'Maksimal '.self::MAKS_MASSAL.' akun sekali proses.',
            'role.required_if' => 'Pilih role baru dulu.',
            'role.in' => 'Role tidak valid.',
            'kelas.required_if' => 'Isi kelas baru dulu.',
        ]);

        $aktor = $request->user();
        $berhasil = 0;
        $dilewati = [];

        foreach (User::whereIn('id', $data['ids'])->orderBy('name')->get() as $user) {
            $alasan = $this->alasanDilewati($aktor, $user, $data['aksi']);

            if ($alasan !== null) {
                $dilewati[] = "{$user->name} ({$alasan})";

                continue;
            }

            match ($data['aksi']) {
                // is_active sengaja tidak masuk $fillable (seperti toggleActive), jadi diisi lewat forceFill
                'aktifkan' => $user->forceFill(['is_active' => true])->save(),
                'nonaktifkan' => $user->forceFill(['is_active' => false])->save(),
                'ubah_kelas' => $user->update(['kelas' => trim($data['kelas'])]),
                'ubah_role' => $this->terapkanRole($user, $data['role']),
                'hapus' => $this->hapusAkun($user),
            };

            $berhasil++;
        }

        $arah = back();

        if ($berhasil > 0) {
            $arah->with('success', $this->pesanMassal($data['aksi'], $berhasil, $data['role'] ?? null));
        }

        if ($dilewati !== []) {
            $contoh = implode(', ', array_slice($dilewati, 0, 5));
            $sisa = count($dilewati) - 5;

            $arah->with('error', count($dilewati).' akun dilewati: '.$contoh.($sisa > 0 ? ", dan {$sisa} lainnya." : '.'));
        }

        return $arah;
    }

    public const AKSI_MASSAL = ['aktifkan', 'nonaktifkan', 'hapus', 'ubah_role', 'ubah_kelas'];

    public const MAKS_MASSAL = 200;

    // Null = boleh diproses; selain itu alasan singkat yang ditampilkan ke pengguna
    private function alasanDilewati(User $aktor, User $user, string $aksi): ?string
    {
        if (! $aktor->can('kelola', $user)) {
            return 'tidak punya izin untuk akun ini';
        }

        if ($user->id === $aktor->id && in_array($aksi, ['hapus', 'nonaktifkan', 'ubah_role'], true)) {
            return 'akunmu sendiri';
        }

        if ($aksi === 'ubah_role' && ! $aktor->can('ubahRole', $user)) {
            return 'hanya admin yang bisa mengubah role';
        }

        return null;
    }

    private function pesanMassal(string $aksi, int $jumlah, ?string $role): string
    {
        return match ($aksi) {
            'aktifkan' => "{$jumlah} akun berhasil diaktifkan.",
            'nonaktifkan' => "{$jumlah} akun berhasil dinonaktifkan.",
            'hapus' => "{$jumlah} akun berhasil dihapus.",
            'ubah_role' => "Role {$jumlah} akun diubah menjadi {$role}.",
            'ubah_kelas' => "Kelas {$jumlah} akun berhasil diperbarui.",
        };
    }

    private function terapkanRole(User $user, string $role): void
    {
        $user->update(['role' => $role]);

        // Bukan pembina lagi -> lepas dari semua ekskul yang ia bina
        if ($role !== 'pembina') {
            EkskulPembina::where('user_id', $user->id)
                ->whereNull('selesai')
                ->update(['selesai' => now()->toDateString()]);
        }
    }

    private function hapusAkun(User $user): void
    {
        Storage::disk('public')->delete(array_filter([$user->foto, $user->wallpaper, $user->aksen_gambar])); // bersihkan file milik user
        $user->delete();
    }
}
