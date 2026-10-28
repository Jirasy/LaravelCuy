<?php

namespace App\Http\Controllers;

use App\Models\CasisPendaftaran;
use App\Models\EkskulPembina;
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

            $infoOsis = [
                'ada' => true,
                'periode' => $periode,
                'pengurus' => PendaftaranEkskul::anggotaAktif()
                    ->where('ekstrakurikuler_id', $ekskulOsis->id)
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
                    ->all(),
                'casis' => $periode
                    ? CasisPendaftaran::where('osis_periode_id', $periode->id)->pluck('status', 'user_id')->all()
                    : [],
                'boleh' => $layanan->bolehMemutuskan($request->user()),
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

        $user->update($data); // password di-hash otomatis oleh cast 'hashed'

        return redirect()->route('users.index')->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('ubahRole', $user);

        $data = $request->validate([
            'role' => 'required|in:admin,pembina,siswa',
        ]);

        $user->update(['role' => $data['role']]);

        // Bukan pembina lagi -> lepas dari semua ekskul yang ia bina
        if ($data['role'] !== 'pembina') {
            EkskulPembina::where('user_id', $user->id)
                ->whereNull('selesai')
                ->update(['selesai' => now()->toDateString()]);
        }

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
        Storage::disk('public')->delete(array_filter([$user->foto, $user->wallpaper, $user->aksen_gambar])); // bersihkan file milik user
        $user->delete();

        return back()->with('success', "Akun {$nama} berhasil dihapus.");
    }
}
