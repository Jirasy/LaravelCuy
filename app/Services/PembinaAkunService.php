<?php

namespace App\Services;

use App\Models\Ekstrakurikuler;
use App\Models\KredensialSementara;
use App\Models\User;
use App\Support\PasswordAcak;
use App\Support\Pemberitahuan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembinaAkunService
{
    /**
     * Menugaskan pembina ke ekskul berdasarkan email:
     * - email belum terdaftar  -> akun pembina baru (password acak, wajib diganti saat login pertama)
     * - email milik pembina aktif -> akun yang ada hanya dihubungkan ke ekskul
     * - email milik akun lain / nonaktif -> ValidationException (semua perubahan di transaksi pemanggil dibatalkan)
     *
     * Notifikasi disimpan di transaksi yang sama, jadi tidak pernah ada notifikasi untuk akun yang gagal dibuat.
     *
     * @return array{user: User, password: ?string, baru: bool}
     */
    public function tugaskan(Ekstrakurikuler $ekskul, string $nama, string $email, User $admin): array
    {
        $email = mb_strtolower(trim($email));

        return DB::transaction(function () use ($ekskul, $nama, $email, $admin) {
            $user = User::where('email', $email)->lockForUpdate()->first();
            $password = null;
            $baru = false;

            if ($user) {
                if ($user->role !== 'pembina' || ! $user->is_active) {
                    throw ValidationException::withMessages([
                        'email_pembina' => "Email {$email} sudah dipakai akun lain yang bukan pembina aktif. Gunakan email lain.",
                    ]);
                }
            } else {
                $password = PasswordAcak::buat();

                $user = new User([
                    'name'                 => $nama,
                    'email'                => $email,
                    'password'             => $password,     // di-hash otomatis oleh cast
                    'role'                 => 'pembina',
                    'must_change_password' => true,
                ]);
                $user->is_active = true;
                $user->save();
                $baru = true;

                KredensialSementara::catat($user, $password, $ekskul, $admin);
            }

            $ekskul->pembinas()->syncWithoutDetaching([$user->id => ['mulai' => now()->toDateString()]]);

            $this->beritahu($ekskul, $user, $admin, $baru);

            return ['user' => $user, 'password' => $password, 'baru' => $baru];
        });
    }

    private function beritahu(Ekstrakurikuler $ekskul, User $pembina, User $admin, bool $baru): void
    {
        $namaEkskul = $ekskul->nama_ekstrakurikuler;

        if ($baru) {
            // Admin: TIDAK memuat password. Password dibuka lewat halaman Kredensial (terenkripsi, berbatas waktu).
            $semuaAdmin = User::where('role', 'admin')->where('is_active', true)->get();
            Pemberitahuan::kirim(
                $semuaAdmin,
                'Akun pembina dibuat',
                "Akun pembina {$pembina->name} ({$pembina->email}) untuk ekskul {$namaEkskul} dibuat oleh {$admin->name}.\n"
                . 'Password sementara bisa dibuka di halaman Kredensial selama ' . KredensialSementara::MASA_BERLAKU_HARI
                . ' hari, dan terhapus otomatis setelah pembina mengganti passwordnya.',
                route('kredensial.index', [], false),
                '🔑'
            );

            Pemberitahuan::kirim(
                [$pembina],
                'Selamat datang, Pembina!',
                "Akun pembinamu untuk ekskul {$namaEkskul} sudah dibuat oleh {$admin->name}.\n"
                . "Email login: {$pembina->email}\n"
                . 'Demi keamanan, segera ganti password sementara dari admin lewat halaman Pengaturan.',
                route('pengaturan.edit', [], false),
                '👋'
            );

            return;
        }

        Pemberitahuan::kirim(
            [$pembina],
            "Kamu ditugaskan sebagai pembina {$namaEkskul}",
            "{$admin->name} menugaskanmu sebagai pembina ekskul {$namaEkskul}.",
            route('ekstrakurikuler.show', $ekskul->id, false),
            '📌'
        );
    }
}
