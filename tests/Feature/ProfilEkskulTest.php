<?php

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

/**
 * @param  array<int, array{hari: string, jam_mulai: string, jam_selesai: string}>  $jadwal
 */
function buatEkskulDenganJadwal(string $nama, array $jadwal): Ekstrakurikuler
{
    $ekskul = Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Budi',
        'status' => 'aktif',
    ]);
    $ekskul->syncJadwal($jadwal);

    return $ekskul;
}

function daftarkanSiswaKeEkskul(User $siswa, Ekstrakurikuler $ekskul, string $status = 'diterima', string $statusAnggota = 'aktif'): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $siswa->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa',
        'status' => $status,
        'status_anggota' => $statusAnggota,
    ]);
}

test('profil siswa menampilkan semua ekskul yang diikuti beserta jadwalnya', function () {
    $siswa = User::factory()->create(['role' => 'siswa', 'is_active' => true]);

    $silat = buatEkskulDenganJadwal('Silat', [
        ['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00'],
    ]);
    $futsal = buatEkskulDenganJadwal('Futsal', [
        ['hari' => 'sabtu', 'jam_mulai' => '13:00', 'jam_selesai' => '15:00'],
        ['hari' => 'rabu', 'jam_mulai' => '15:30', 'jam_selesai' => '17:00'],
    ]);
    $pramuka = buatEkskulDenganJadwal('Pramuka', [
        ['hari' => 'jumat', 'jam_mulai' => '14:00', 'jam_selesai' => '16:00'],
    ]);

    foreach ([$silat, $futsal, $pramuka] as $ekskul) {
        daftarkanSiswaKeEkskul($siswa, $ekskul);
    }

    $response = $this->actingAs($siswa)->get(route('profil.show', ['user' => $siswa->id, 'hari' => 'sabtu']));

    $response->assertOk();
    $response->assertViewHas('badges', fn ($badges) => $badges->pluck('ekskul')->sort()->values()->all() === ['Futsal', 'Pramuka', 'Silat']);
    $response->assertViewHas('perHari', fn ($perHari) => $perHari['sabtu']->pluck('ekskul')->all() === ['Silat', 'Futsal']
        && $perHari['rabu']->count() === 1
        && $perHari['jumat']->count() === 1);
});

test('profil hanya menampilkan keanggotaan yang disetujui dan masih aktif', function () {
    $siswa = User::factory()->create(['role' => 'siswa', 'is_active' => true]);

    $aktif = buatEkskulDenganJadwal('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]);
    $menunggu = buatEkskulDenganJadwal('Futsal', [['hari' => 'sabtu', 'jam_mulai' => '13:00', 'jam_selesai' => '15:00']]);
    $ditolak = buatEkskulDenganJadwal('Basket', [['hari' => 'kamis', 'jam_mulai' => '15:00', 'jam_selesai' => '17:00']]);
    $sudahKeluar = buatEkskulDenganJadwal('Pramuka', [['hari' => 'jumat', 'jam_mulai' => '14:00', 'jam_selesai' => '16:00']]);

    daftarkanSiswaKeEkskul($siswa, $aktif);
    daftarkanSiswaKeEkskul($siswa, $menunggu, status: 'pending');
    daftarkanSiswaKeEkskul($siswa, $ditolak, status: 'ditolak');
    daftarkanSiswaKeEkskul($siswa, $sudahKeluar, statusAnggota: 'keluar');

    $response = $this->actingAs($siswa)->get(route('profil.show', ['user' => $siswa->id, 'hari' => 'sabtu']));

    $response->assertOk();
    $response->assertViewHas('badges', fn ($badges) => $badges->pluck('ekskul')->all() === ['Silat']);
});
