<?php

use App\Models\BentrokJadwalPilihan;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function siswaAktif(): User
{
    return User::factory()->create(['role' => 'siswa', 'is_active' => true]);
}

/**
 * @param  array<int, array{hari: string, jam_mulai: string, jam_selesai: string}>  $jadwal
 */
function ekskulUji(string $nama, array $jadwal): Ekstrakurikuler
{
    $ekskul = Ekstrakurikuler::create(['nama_ekstrakurikuler' => $nama, 'pembina' => 'Pak Budi', 'status' => 'aktif']);
    $ekskul->syncJadwal($jadwal);

    return $ekskul;
}

function jadiAnggota(User $siswa, Ekstrakurikuler $ekskul, string $status = 'diterima'): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $siswa->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa',
        'status' => $status,
    ]);
}

/**
 * Siswa yang mengikuti Silat (Sabtu 08:00–10:00) dan Futsal (Sabtu 09:30–11:00).
 *
 * @return array{0: User, 1: Ekstrakurikuler, 2: Ekstrakurikuler}
 */
function siswaDenganBentrok(): array
{
    $siswa = siswaAktif();
    $silat = ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]);
    $futsal = ekskulUji('Futsal', [['hari' => 'sabtu', 'jam_mulai' => '09:30', 'jam_selesai' => '11:00']]);
    jadiAnggota($siswa, $silat);
    jadiAnggota($siswa, $futsal);

    return [$siswa, $silat, $futsal];
}

// ---------- Profil ----------

test('profil siswa menampilkan peringatan kalau dua ekskulnya bentrok', function () {
    [$siswa] = siswaDenganBentrok();

    $response = $this->actingAs($siswa)->get(route('profil.show', ['user' => $siswa->id, 'hari' => 'sabtu']));

    $response->assertOk()
        ->assertViewHas('bentrok', fn ($bentrok) => $bentrok->count() === 1 && $bentrok->first()['pilihan'] === null)
        ->assertSee('Ada 1 jadwal ekskul yang bentrok')
        ->assertSee('Belum dipilih');
});

test('profil tanpa bentrok tidak menampilkan peringatan', function () {
    $siswa = siswaAktif();
    jadiAnggota($siswa, ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]));
    jadiAnggota($siswa, ekskulUji('Futsal', [['hari' => 'sabtu', 'jam_mulai' => '10:00', 'jam_selesai' => '12:00']]));

    $this->actingAs($siswa)->get(route('profil.show', ['user' => $siswa->id]))
        ->assertOk()
        ->assertViewHas('bentrok', fn ($bentrok) => $bentrok->isEmpty())
        ->assertDontSee('jadwal ekskul yang bentrok');
});

test('bentrok hanya dihitung untuk keanggotaan aktif yang disetujui', function () {
    $siswa = siswaAktif();
    $silat = ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]);
    $futsal = ekskulUji('Futsal', [['hari' => 'sabtu', 'jam_mulai' => '09:30', 'jam_selesai' => '11:00']]);
    jadiAnggota($siswa, $silat);
    jadiAnggota($siswa, $futsal, status: 'pending');

    $this->actingAs($siswa)->get(route('profil.show', ['user' => $siswa->id]))
        ->assertViewHas('bentrok', fn ($bentrok) => $bentrok->isEmpty());
});

test('pengguna lain tidak melihat panel bentrok di profil siswa', function () {
    [$siswa] = siswaDenganBentrok();
    $teman = siswaAktif();

    $this->actingAs($teman)->get(route('profil.show', ['user' => $siswa->id]))
        ->assertOk()
        ->assertViewHas('bentrok', fn ($bentrok) => $bentrok->isEmpty())
        ->assertDontSee('jadwal ekskul yang bentrok');
});

// ---------- Menyimpan pilihan ----------

test('siswa bisa memilih hadir di salah satu ekskul yang bentrok', function () {
    [$siswa, $silat, $futsal] = siswaDenganBentrok();

    $this->actingAs($siswa)->post(route('bentrok.simpan'), [
        'hari' => 'sabtu',
        'ekskul_a_id' => $silat->id,
        'ekskul_b_id' => $futsal->id,
        'pilihan' => 'ekskul_a',
    ])->assertRedirect(route('profil.show', ['user' => $siswa->id, 'hari' => 'sabtu']));

    expect(BentrokJadwalPilihan::where('user_id', $siswa->id)->first())
        ->pilihan->toBe('ekskul_a')
        ->catatan->toBeNull();

    $this->actingAs($siswa)->get(route('profil.show', ['user' => $siswa->id, 'hari' => 'sabtu']))
        ->assertViewHas('bentrok', fn ($bentrok) => $bentrok->first()['pilihan']?->pilihan === 'ekskul_a')
        ->assertSee('Hadir di Silat');
});

test('urutan pasangan dibalik dinormalkan dan pilihannya ikut tertukar', function () {
    [$siswa, $silat, $futsal] = siswaDenganBentrok();

    // Dikirim terbalik: a = ekskul ber-id lebih besar. "Hadir di a" harus tersimpan sebagai ekskul_b.
    $this->actingAs($siswa)->post(route('bentrok.simpan'), [
        'hari' => 'sabtu',
        'ekskul_a_id' => $futsal->id,
        'ekskul_b_id' => $silat->id,
        'pilihan' => 'ekskul_a',
    ])->assertSessionHasNoErrors();

    $tersimpan = BentrokJadwalPilihan::where('user_id', $siswa->id)->sole();

    expect($tersimpan->ekskul_a_id)->toBe($silat->id)
        ->and($tersimpan->ekskul_b_id)->toBe($futsal->id)
        ->and($tersimpan->pilihan)->toBe('ekskul_b');
});

test('memilih lagi memperbarui pilihan lama, bukan menambah baris', function () {
    [$siswa, $silat, $futsal] = siswaDenganBentrok();
    $data = ['hari' => 'sabtu', 'ekskul_a_id' => $silat->id, 'ekskul_b_id' => $futsal->id];

    $this->actingAs($siswa)->post(route('bentrok.simpan'), $data + ['pilihan' => 'ekskul_a']);
    $this->actingAs($siswa)->post(route('bentrok.simpan'), $data + ['pilihan' => 'ekskul_b']);

    expect(BentrokJadwalPilihan::where('user_id', $siswa->id)->count())->toBe(1)
        ->and(BentrokJadwalPilihan::where('user_id', $siswa->id)->first()->pilihan)->toBe('ekskul_b');
});

test('bagi waktu wajib disertai catatan', function () {
    [$siswa, $silat, $futsal] = siswaDenganBentrok();
    $data = ['hari' => 'sabtu', 'ekskul_a_id' => $silat->id, 'ekskul_b_id' => $futsal->id, 'pilihan' => 'bagi_waktu'];

    $this->actingAs($siswa)->post(route('bentrok.simpan'), $data)
        ->assertSessionHasErrors('catatan');
    expect(BentrokJadwalPilihan::count())->toBe(0);

    $this->actingAs($siswa)->post(route('bentrok.simpan'), $data + ['catatan' => 'Silat sampai 09:30, lalu Futsal'])
        ->assertSessionHasNoErrors();
    expect(BentrokJadwalPilihan::sole()->catatan)->toBe('Silat sampai 09:30, lalu Futsal');
});

test('catatan dibuang kalau pilihannya bukan bagi waktu', function () {
    [$siswa, $silat, $futsal] = siswaDenganBentrok();

    $this->actingAs($siswa)->post(route('bentrok.simpan'), [
        'hari' => 'sabtu', 'ekskul_a_id' => $silat->id, 'ekskul_b_id' => $futsal->id,
        'pilihan' => 'ekskul_a', 'catatan' => 'sisa isian lama',
    ]);

    expect(BentrokJadwalPilihan::sole()->catatan)->toBeNull();
});

test('pilihan untuk pasangan yang tidak sedang bentrok ditolak', function () {
    [$siswa, $silat] = siswaDenganBentrok();
    $pramuka = ekskulUji('Pramuka', [['hari' => 'jumat', 'jam_mulai' => '14:00', 'jam_selesai' => '16:00']]);
    jadiAnggota($siswa, $pramuka);

    $this->actingAs($siswa)->post(route('bentrok.simpan'), [
        'hari' => 'sabtu', 'ekskul_a_id' => $silat->id, 'ekskul_b_id' => $pramuka->id, 'pilihan' => 'ekskul_a',
    ])->assertStatus(422);

    expect(BentrokJadwalPilihan::count())->toBe(0);
});

test('siswa tidak bisa menyimpan pilihan untuk bentrok milik siswa lain', function () {
    [, $silat, $futsal] = siswaDenganBentrok();
    $orangLain = siswaAktif();

    $this->actingAs($orangLain)->post(route('bentrok.simpan'), [
        'hari' => 'sabtu', 'ekskul_a_id' => $silat->id, 'ekskul_b_id' => $futsal->id, 'pilihan' => 'ekskul_a',
    ])->assertStatus(422);

    expect(BentrokJadwalPilihan::count())->toBe(0);
});

test('siswa bisa menghapus pilihannya, tapi tidak pilihan orang lain', function () {
    [$siswa, $silat, $futsal] = siswaDenganBentrok();
    $pilihan = BentrokJadwalPilihan::create([
        'user_id' => $siswa->id, 'hari' => 'sabtu', 'ekskul_a_id' => $silat->id, 'ekskul_b_id' => $futsal->id, 'pilihan' => 'ekskul_a',
    ]);

    $this->actingAs(siswaAktif())->delete(route('bentrok.hapus', $pilihan))->assertForbidden();
    expect(BentrokJadwalPilihan::count())->toBe(1);

    $this->actingAs($siswa)->delete(route('bentrok.hapus', $pilihan))->assertRedirect();
    expect(BentrokJadwalPilihan::count())->toBe(0);
});

// ---------- Saat mendaftar ----------

test('mendaftar ke ekskul yang bentrok diminta konfirmasi dulu dan belum tersimpan', function () {
    $siswa = siswaAktif();
    jadiAnggota($siswa, ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]));
    $futsal = ekskulUji('Futsal', [['hari' => 'sabtu', 'jam_mulai' => '09:30', 'jam_selesai' => '11:00']]);

    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $futsal->id])
        ->assertRedirect()
        ->assertSessionHas('bentrok_baru', ['Silat (08:00–10:00) bentrok dengan Futsal (09:30–11:00) pada hari Sabtu']);

    expect(PendaftaranEkskul::where('ekstrakurikuler_id', $futsal->id)->count())->toBe(0);
});

test('mendaftar ke ekskul yang bentrok berhasil setelah siswa mengonfirmasi', function () {
    $siswa = siswaAktif();
    jadiAnggota($siswa, ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]));
    $futsal = ekskulUji('Futsal', [['hari' => 'sabtu', 'jam_mulai' => '09:30', 'jam_selesai' => '11:00']]);

    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $futsal->id, 'konfirmasi_bentrok' => '1'])
        ->assertRedirect(route('pendaftaran.index'))
        ->assertSessionMissing('bentrok_baru');

    expect(PendaftaranEkskul::where('ekstrakurikuler_id', $futsal->id)->where('user_id', $siswa->id)->count())->toBe(1);
});

test('mendaftar ke ekskul yang tidak bentrok langsung tersimpan tanpa konfirmasi', function () {
    $siswa = siswaAktif();
    jadiAnggota($siswa, ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]));
    $pramuka = ekskulUji('Pramuka', [['hari' => 'jumat', 'jam_mulai' => '14:00', 'jam_selesai' => '16:00']]);

    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $pramuka->id])
        ->assertRedirect(route('pendaftaran.index'));

    expect(PendaftaranEkskul::where('ekstrakurikuler_id', $pramuka->id)->count())->toBe(1);
});

test('pendaftaran yang ditolak tidak dihitung sebagai bentrok saat mendaftar', function () {
    $siswa = siswaAktif();
    jadiAnggota($siswa, ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]), status: 'ditolak');
    $futsal = ekskulUji('Futsal', [['hari' => 'sabtu', 'jam_mulai' => '09:30', 'jam_selesai' => '11:00']]);

    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $futsal->id])
        ->assertRedirect(route('pendaftaran.index'));

    expect(PendaftaranEkskul::where('ekstrakurikuler_id', $futsal->id)->count())->toBe(1);
});

test('form pendaftaran menampilkan peringatan dan kotak konfirmasi setelah ditolak sementara', function () {
    $siswa = siswaAktif();
    jadiAnggota($siswa, ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]));
    $futsal = ekskulUji('Futsal', [['hari' => 'sabtu', 'jam_mulai' => '09:30', 'jam_selesai' => '11:00']]);

    $this->actingAs($siswa)
        ->withSession(['bentrok_baru' => ['Silat (08:00–10:00) bentrok dengan Futsal (09:30–11:00) pada hari Sabtu']])
        ->get(route('pendaftaran.create', ['ekstrakurikuler_id' => $futsal->id]))
        ->assertOk()
        ->assertSee('Jadwal ekskul ini bentrok')
        ->assertSee('konfirmasi_bentrok', false);
});

// ---------- Notifikasi saat disetujui ----------

test('siswa diberi notifikasi saat pendaftarannya disetujui dan jadwalnya bentrok', function () {
    [$siswa] = siswaDenganBentrok();
    $basket = ekskulUji('Basket', [['hari' => 'sabtu', 'jam_mulai' => '08:30', 'jam_selesai' => '09:30']]);
    $pendaftaran = jadiAnggota($siswa, $basket, status: 'pending');
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    // Form persetujuan selalu mengirim kolom alasan (boleh kosong)
    $this->actingAs($admin)->patch(route('pendaftaran.updateStatus', $pendaftaran), ['status' => 'diterima', 'alasan' => ''])
        ->assertRedirect(route('pendaftaran.index'));

    $judul = $siswa->notifications()->get()->pluck('data.judul');
    expect($judul)->toContain('Jadwal ekskulmu bentrok')
        ->and($siswa->notifications()->get()->firstWhere('data.judul', 'Jadwal ekskulmu bentrok')->data['url'])
        ->toEndWith('#bentrok-jadwal');
});

test('tidak ada notifikasi bentrok kalau pendaftaran yang disetujui tidak bentrok', function () {
    $siswa = siswaAktif();
    jadiAnggota($siswa, ekskulUji('Silat', [['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]));
    $pramuka = ekskulUji('Pramuka', [['hari' => 'jumat', 'jam_mulai' => '14:00', 'jam_selesai' => '16:00']]);
    $pendaftaran = jadiAnggota($siswa, $pramuka, status: 'pending');
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)->patch(route('pendaftaran.updateStatus', $pendaftaran), ['status' => 'diterima', 'alasan' => ''])
        ->assertRedirect(route('pendaftaran.index'));

    // Notifikasi "disetujui" tetap terkirim, hanya yang soal bentrok yang tidak ada
    expect($siswa->notifications()->get()->pluck('data.judul'))
        ->toContain('Pendaftaran disetujui')
        ->not->toContain('Jadwal ekskulmu bentrok');
});
