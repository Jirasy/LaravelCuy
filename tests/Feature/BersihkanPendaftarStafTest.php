<?php

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stafDaftar(User $akun, Ekstrakurikuler $ekskul, string $peran, string $status): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $akun->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $akun->name,
        'peran' => $peran,
        'status' => $status,
        'status_anggota' => 'aktif',
    ]);
}

test('pembersihan pendaftar admin/pembina: tanpa --terapkan hanya menampilkan daftar', function () {
    $ekskul = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'Pak Budi', 'status' => 'aktif']);
    $pembina = User::factory()->create(['role' => 'pembina', 'is_active' => true]);
    $staf = stafDaftar($pembina, $ekskul, 'pembina', 'diterima');

    $this->artisan('ekskul:bersihkan-pendaftar-staf')->assertExitCode(0);

    expect($staf->fresh()->status_anggota)->toBe('aktif')
        ->and($staf->fresh()->tanggal_keluar)->toBeNull();
});

test('pembersihan dengan --terapkan hanya menyentuh admin/pembina, siswa tidak berubah', function () {
    $ekskul = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'Pak Budi', 'status' => 'aktif']);
    $pembina = User::factory()->create(['role' => 'pembina', 'is_active' => true]);
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $siswa = User::factory()->create(['role' => 'siswa', 'is_active' => true]);

    $anggotaStaf = stafDaftar($pembina, $ekskul, 'pembina', 'diterima');
    $menungguStaf = stafDaftar($admin, $ekskul, 'admin', 'pending');
    $anggotaSiswa = stafDaftar($siswa, $ekskul, 'siswa', 'diterima');

    $this->artisan('ekskul:bersihkan-pendaftar-staf', ['--terapkan' => true])->assertExitCode(0);

    // Anggota admin/pembina: ditandai keluar (riwayat tetap ada)
    expect($anggotaStaf->fresh()->status_anggota)->toBe('keluar')
        ->and($anggotaStaf->fresh()->tanggal_keluar)->not->toBeNull()
        ->and($anggotaStaf->fresh()->alasan)->toContain('pembersihan data');

    // Pendaftaran admin/pembina yang masih menunggu: ditolak
    expect($menungguStaf->fresh()->status)->toBe('ditolak');

    // Siswa tidak tersentuh
    expect($anggotaSiswa->fresh()->status)->toBe('diterima')
        ->and($anggotaSiswa->fresh()->status_anggota)->toBe('aktif');
});
