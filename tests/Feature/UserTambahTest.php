<?php

use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\KredensialSementara;
use App\Models\User;
use App\Notifications\RekapImportSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunTb(string $role = 'siswa', array $isian = []): User
{
    return User::factory()->create(array_merge(['role' => $role, 'is_active' => true], $isian));
}

function ekskulTb(string $nama = 'Futsal Garuda'): Ekstrakurikuler
{
    return Ekstrakurikuler::create(['nama_ekstrakurikuler' => $nama, 'pembina' => 'Pak Uji', 'status' => 'aktif']);
}

function judulNotifTb(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

// ---------------------------------------------------------------------------
// Form & hak akses
// ---------------------------------------------------------------------------

test('admin melihat form siswa dan pembina; pembina hanya form siswa; siswa tidak bisa membuka', function () {
    $this->actingAs(akunTb('admin'))->get(route('users.create'))->assertOk()->assertSee('Simpan siswa')->assertSee('tab=pembina', false);
    $this->actingAs(akunTb('admin'))->get(route('users.create', ['tab' => 'pembina']))->assertOk()->assertSee('Simpan pembina');

    $pembina = akunTb('pembina');
    $this->actingAs($pembina)->get(route('users.create'))->assertOk()->assertSee('Simpan siswa')->assertDontSee('tab=pembina', false);
    // pembina yang mencoba membuka tab pembina tetap mendapat form siswa
    $this->actingAs($pembina)->get(route('users.create', ['tab' => 'pembina']))->assertOk()->assertSee('Simpan siswa')->assertDontSee('Simpan pembina');

    $this->actingAs(akunTb('siswa'))->get(route('users.create'))->assertForbidden();
});

test('halaman Kelola User punya tombol tambah; tombol pembina hanya untuk admin', function () {
    $this->actingAs(akunTb('admin'))->get(route('users.index'))->assertOk()->assertSee('Tambah siswa')->assertSee('Tambah pembina');
    $this->actingAs(akunTb('pembina'))->get(route('users.index'))->assertOk()->assertSee('Tambah siswa')->assertDontSee('Tambah pembina');
});

// ---------------------------------------------------------------------------
// Tambah siswa
// ---------------------------------------------------------------------------

test('menambah siswa tanpa NISN: kelas dirapikan, email otomatis, password acak tersimpan terenkripsi dan tidak ada di notifikasi', function () {
    $admin = akunTb('admin');

    $respons = $this->actingAs($admin)->post(route('users.store-siswa'), [
        'nama' => '  Budi   Santoso ',
        'kelas' => 'xi  pplg',
        'nisn' => '',
        'email' => '',
    ]);

    $siswa = User::where('role', 'siswa')->firstOrFail();

    $respons->assertRedirect()->assertSessionHas('success');

    expect($siswa->name)->toBe('Budi Santoso')
        ->and($siswa->kelas)->toBe('XI PPLG')
        ->and($siswa->nisn)->toBeNull()
        ->and($siswa->email)->toBe('budi.santoso@siswa.local')
        ->and($siswa->is_active)->toBeTrue()
        ->and($siswa->must_change_password)->toBeTrue();

    // password acak & kuat: tersimpan terenkripsi, dan cocok dengan hash di akun
    $kredensial = KredensialSementara::where('user_id', $siswa->id)->firstOrFail();
    $password = $kredensial->passwordAsli();

    expect($password)->not->toBeNull()
        ->and(strlen($password))->toBeGreaterThanOrEqual(12)
        ->and(Hash::check($password, $siswa->password))->toBeTrue()
        ->and($kredensial->dibuat_oleh)->toBe($admin->id);

    // rekap masuk Riwayat Pesan pembuat, tanpa password polos
    $rekap = $admin->notifications()->where('type', RekapImportSiswa::class)->firstOrFail();

    expect($rekap->data['id_siswa'])->toBe([$siswa->id])
        ->and($rekap->data['berkas'])->toBe('Input manual')
        ->and(json_encode($rekap->data))->not->toContain($password);

    $respons->assertRedirect(route('import-siswa.hasil', $rekap->id));
});

test('hasil penambahan siswa bisa dibuka pembuatnya untuk melihat password sementara', function () {
    $pembina = akunTb('pembina');

    $this->actingAs($pembina)->post(route('users.store-siswa'), ['nama' => 'Citra Lestari', 'kelas' => 'X']);

    $rekap = $pembina->notifications()->where('type', RekapImportSiswa::class)->firstOrFail();
    $password = KredensialSementara::firstOrFail()->passwordAsli();

    $this->actingAs($pembina)->get(route('import-siswa.hasil', $rekap->id))->assertOk()->assertSee('Citra Lestari')->assertDontSee($password);

    $this->actingAs($pembina)->followingRedirects()->post(route('import-siswa.tampilkan', $rekap->id))->assertOk()->assertSee($password);
});

test('menambah siswa dengan NISN dan email sendiri', function () {
    $this->actingAs(akunTb('admin'))->post(route('users.store-siswa'), [
        'nama' => 'Dewi Anggraini', 'kelas' => 'XII TKJ', 'nisn' => '0051234567', 'email' => 'Dewi@Sekolah.sch.id',
    ])->assertSessionHas('success');

    $siswa = User::where('role', 'siswa')->firstOrFail();

    expect($siswa->nisn)->toBe('0051234567')->and($siswa->email)->toBe('dewi@sekolah.sch.id')->and($siswa->kelas)->toBe('XII TKJ');
});

test('NISN, email, dan nama kembar ditolak dengan alasan; nama kembar boleh kalau diizinkan', function () {
    akunTb('siswa', ['name' => 'Eko Prasetyo', 'nisn' => '1234567890', 'email' => 'eko@sekolah.sch.id']);
    $admin = akunTb('admin');
    $sebelum = User::count();

    $this->actingAs($admin)->post(route('users.store-siswa'), ['nama' => 'Orang Lain', 'nisn' => '1234567890'])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'NISN 1234567890 sudah terdaftar'));
    $this->actingAs($admin)->post(route('users.store-siswa'), ['nama' => 'Orang Lain', 'email' => 'eko@sekolah.sch.id'])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah dipakai'));
    $this->actingAs($admin)->post(route('users.store-siswa'), ['nama' => 'Eko Prasetyo'])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'nama yang sama'));

    expect(User::count())->toBe($sebelum);

    $this->actingAs($admin)->post(route('users.store-siswa'), ['nama' => 'Eko Prasetyo', 'izinkan_nama_sama' => '1'])->assertSessionHas('success');

    expect(User::count())->toBe($sebelum + 1)
        ->and(User::where('name', 'Eko Prasetyo')->where('role', 'siswa')->count())->toBe(2);
});

test('data siswa yang tidak valid ditolak', function (array $isian, string $kolom) {
    $this->actingAs(akunTb('admin'))->post(route('users.store-siswa'), $isian)->assertSessionHasErrors($kolom);

    expect(User::where('role', 'siswa')->count())->toBe(0);
})->with([
    'tanpa nama' => [['nama' => '', 'kelas' => 'X'], 'nama'],
    'NISN bukan angka' => [['nama' => 'Fajar', 'nisn' => 'abc123'], 'nisn'],
    'NISN terlalu pendek' => [['nama' => 'Fajar', 'nisn' => '123'], 'nisn'],
    'email salah format' => [['nama' => 'Fajar', 'email' => 'bukan-email'], 'email'],
    'domain salah' => [['nama' => 'Fajar', 'domain_email' => 'tanpa titik'], 'domain_email'],
]);

test('siswa tidak bisa menambah akun, dan pembina tidak bisa menambah pembina', function () {
    $this->actingAs(akunTb('siswa'))->post(route('users.store-siswa'), ['nama' => 'Gita'])->assertForbidden();
    $this->actingAs(akunTb('pembina'))->post(route('users.store-pembina'), ['nama' => 'Pembina Baru', 'email_pembina' => 'baru@sekolah.sch.id'])->assertForbidden();
    $this->actingAs(akunTb('siswa'))->post(route('users.store-pembina'), ['nama' => 'Pembina Baru', 'email_pembina' => 'baru@sekolah.sch.id'])->assertForbidden();

    expect(User::where('email', 'baru@sekolah.sch.id')->exists())->toBeFalse();
});

// ---------------------------------------------------------------------------
// Tambah pembina
// ---------------------------------------------------------------------------

test('menambah pembina tanpa ekskul: password acak, kredensial terenkripsi, notifikasi admin tanpa password, tampil sekali', function () {
    $admin = akunTb('admin');

    $respons = $this->actingAs($admin)->post(route('users.store-pembina'), ['nama' => 'Bu Hartini', 'email_pembina' => 'Hartini@Sekolah.sch.id']);

    $respons->assertRedirect(route('users.index'))->assertSessionHas('success');

    $pembina = User::where('role', 'pembina')->firstOrFail();
    $kredensial = KredensialSementara::where('user_id', $pembina->id)->firstOrFail();
    $password = $kredensial->passwordAsli();

    expect($pembina->name)->toBe('Bu Hartini')
        ->and($pembina->email)->toBe('hartini@sekolah.sch.id')
        ->and($pembina->is_active)->toBeTrue()
        ->and($pembina->must_change_password)->toBeTrue()
        ->and(Hash::check($password, $pembina->password))->toBeTrue()
        ->and($kredensial->dibuat_oleh)->toBe($admin->id)
        ->and($pembina->ekskulDikelola()->count())->toBe(0)
        ->and(session('kredensial'))->toBe(['nama' => 'Bu Hartini', 'email' => 'hartini@sekolah.sch.id', 'password' => $password]);

    // Riwayat Pesan: admin tahu akun dibuat (tanpa password), pembina dapat sambutan
    $notifAdmin = $admin->notifications()->get()->firstWhere('data.judul', 'Akun pembina dibuat');

    expect($notifAdmin)->not->toBeNull()
        ->and(json_encode($notifAdmin->data))->not->toContain($password)
        ->and($notifAdmin->data['pesan'])->toContain('Bu Hartini')->not->toContain('untuk ekskul')
        ->and(judulNotifTb($pembina))->toContain('Selamat datang, Pembina!')
        ->and(json_encode($pembina->notifications()->get()->pluck('data')))->not->toContain($password);

    // halaman Kelola User menampilkan password sekali (flash), dan tidak lagi setelahnya
    $this->followingRedirects()->actingAs($admin)->post(route('users.store-pembina'), ['nama' => 'Pak Rudi', 'email_pembina' => 'rudi@sekolah.sch.id'])
        ->assertOk()->assertSee('Akun pembina dibuat: Pak Rudi');
});

test('menambah pembina dengan ekskul langsung menugaskannya', function () {
    $ekskul = ekskulTb('Paduan Suara');
    $admin = akunTb('admin');

    $this->actingAs($admin)->post(route('users.store-pembina'), [
        'nama' => 'Bu Wulan', 'email_pembina' => 'wulan@sekolah.sch.id', 'ekstrakurikuler_id' => $ekskul->id,
    ])->assertSessionHas('success');

    $pembina = User::where('email', 'wulan@sekolah.sch.id')->firstOrFail();
    $notifAdmin = $admin->notifications()->get()->firstWhere('data.judul', 'Akun pembina dibuat');

    expect($ekskul->pembinaAktif()->pluck('users.id')->all())->toBe([$pembina->id])
        ->and(KredensialSementara::where('user_id', $pembina->id)->value('ekstrakurikuler_id'))->toBe($ekskul->id)
        ->and($notifAdmin->data['pesan'])->toContain('untuk ekskul Paduan Suara');
});

test('email pembina yang sudah terdaftar ditolak tanpa ekskul; dengan ekskul, pembina aktif yang ada hanya ditugaskan', function () {
    $admin = akunTb('admin');
    $ekskul = ekskulTb('Robotik');
    $ada = akunTb('pembina', ['name' => 'Pak Lama', 'email' => 'lama@sekolah.sch.id']);
    $siswa = akunTb('siswa', ['email' => 'siswa@sekolah.sch.id']);
    $jumlah = User::count();

    $this->actingAs($admin)->post(route('users.store-pembina'), ['nama' => 'Pak Lama', 'email_pembina' => 'lama@sekolah.sch.id'])
        ->assertSessionHasErrors('email_pembina');
    $this->actingAs($admin)->post(route('users.store-pembina'), ['nama' => 'Nama Siswa', 'email_pembina' => 'siswa@sekolah.sch.id', 'ekstrakurikuler_id' => $ekskul->id])
        ->assertSessionHasErrors('email_pembina');
    expect(User::count())->toBe($jumlah)->and($ekskul->pembinaAktif()->count())->toBe(0);

    $this->actingAs($admin)->post(route('users.store-pembina'), ['nama' => 'Pak Lama', 'email_pembina' => 'lama@sekolah.sch.id', 'ekstrakurikuler_id' => $ekskul->id])
        ->assertSessionHas('success')->assertSessionMissing('kredensial');

    expect(User::count())->toBe($jumlah)
        ->and($ekskul->pembinaAktif()->pluck('users.id')->all())->toBe([$ada->id])
        ->and(KredensialSementara::where('user_id', $ada->id)->exists())->toBeFalse();
});

test('data pembina yang tidak valid ditolak', function (array $isian, string $kolom) {
    $this->actingAs(akunTb('admin'))->post(route('users.store-pembina'), $isian)->assertSessionHasErrors($kolom);

    expect(User::where('role', 'pembina')->count())->toBe(0);
})->with([
    'tanpa nama' => [['nama' => '', 'email_pembina' => 'a@sekolah.sch.id'], 'nama'],
    'tanpa email' => [['nama' => 'Pak A', 'email_pembina' => ''], 'email_pembina'],
    'email salah format' => [['nama' => 'Pak A', 'email_pembina' => 'salah'], 'email_pembina'],
    'ekskul tidak ada' => [['nama' => 'Pak A', 'email_pembina' => 'a@sekolah.sch.id', 'ekstrakurikuler_id' => 99999], 'ekstrakurikuler_id'],
]);
