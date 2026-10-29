<?php

use App\Models\Ekstrakurikuler;
use App\Models\KredensialSementara;
use App\Models\User;
use App\Rules\PasswordKuat;
use App\Support\PasswordAcak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunAp(string $role = 'siswa', ?string $nama = null, bool $aktif = true): User
{
    return User::factory()->create(array_filter([
        'name' => $nama,
        'role' => $role,
        'is_active' => $aktif,
    ], fn ($v) => $v !== null));
}

function payloadEkskulAp(array $tambahan = []): array
{
    return array_merge([
        'nama_ekstrakurikuler' => 'Panahan',
        'pembina' => 'Bu Ratna',
        'hari' => ['senin'],
        'jadwal' => ['senin' => ['jam_mulai' => '15:00', 'jam_selesai' => '17:00']],
    ], $tambahan);
}

// Admin membuat ekskul dengan email pembina baru, lalu mengembalikan [admin, pembina, passwordSementara]
function buatEkskulDenganPembinaBaruAp($testCase, string $email = 'bu.ratna@sekolah.test'): array
{
    $admin = akunAp('admin', 'Admin Utama');

    $testCase->actingAs($admin)
        ->post(route('ekstrakurikuler.store'), payloadEkskulAp(['email_pembina' => $email]))
        ->assertSessionHas('kredensial');

    return [$admin, User::where('email', mb_strtolower($email))->firstOrFail(), session('kredensial')['password']];
}

function judulNotifAp(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

// ---------------------------------------------------------------------------
// Poin 1: akun pembina otomatis
// ---------------------------------------------------------------------------

test('admin membuat ekskul dengan email baru: akun pembina otomatis terbuat dan terhubung', function () {
    $admin = akunAp('admin');

    $this->actingAs($admin)
        ->post(route('ekstrakurikuler.store'), payloadEkskulAp(['email_pembina' => 'Bu.Ratna@Sekolah.test']))
        ->assertRedirect(route('ekstrakurikuler.index'))
        ->assertSessionHas('kredensial');

    $pembina = User::where('email', 'bu.ratna@sekolah.test')->firstOrFail(); // email disimpan huruf kecil
    $ekskul = Ekstrakurikuler::firstOrFail();
    $kredensial = session('kredensial');

    expect($pembina->role)->toBe('pembina')
        ->and($pembina->name)->toBe('Bu Ratna')
        ->and($pembina->is_active)->toBeTrue()
        ->and($pembina->must_change_password)->toBeTrue()
        ->and($ekskul->pembinaAktif->pluck('id')->all())->toBe([$pembina->id])
        ->and(Hash::check($kredensial['password'], $pembina->password))->toBeTrue();
});

test('password acak selalu kuat, tidak memuat karakter ambigu, dan berbeda tiap kali', function () {
    $dibuat = collect(range(1, 50))->map(fn () => PasswordAcak::buat());

    foreach ($dibuat as $password) {
        $pesan = [];
        (new PasswordKuat('Bu Ratna', 'bu.ratna@sekolah.test'))->validate('password', $password, function ($m) use (&$pesan) {
            $pesan[] = $m;
        });

        expect(strlen($password))->toBe(14)
            ->and($password)->not->toMatch('/[0OIl1]/')
            ->and($pesan)->toBe([]);
    }

    expect($dibuat->unique()->count())->toBe(50);
});

test('email milik pembina aktif hanya dihubungkan, tanpa akun atau password baru', function () {
    $admin = akunAp('admin');
    $lama = akunAp('pembina', 'Pak Lama');
    $jumlahUser = User::count();

    $this->actingAs($admin)
        ->post(route('ekstrakurikuler.store'), payloadEkskulAp(['email_pembina' => $lama->email]))
        ->assertSessionMissing('kredensial');

    $ekskul = Ekstrakurikuler::firstOrFail();

    expect(User::count())->toBe($jumlahUser)
        ->and(KredensialSementara::count())->toBe(0)
        ->and($ekskul->pembinaAktif->pluck('id')->all())->toBe([$lama->id])
        ->and(judulNotifAp($lama))->toContain('Kamu ditugaskan sebagai pembina Panahan');
});

test('email milik siswa, admin, atau pembina nonaktif ditolak dan ekskul tidak jadi dibuat', function (string $role, bool $aktif) {
    $admin = akunAp('admin');
    $pemilik = akunAp($role, null, $aktif);

    $this->actingAs($admin)
        ->post(route('ekstrakurikuler.store'), payloadEkskulAp(['email_pembina' => $pemilik->email]))
        ->assertSessionHasErrors('email_pembina');

    expect(Ekstrakurikuler::count())->toBe(0)
        ->and(KredensialSementara::count())->toBe(0);
})->with([
    'siswa' => ['siswa', true],
    'admin' => ['admin', true],
    'pembina nonaktif' => ['pembina', false],
]);

test('tanpa email, ekskul dibuat seperti biasa tanpa akun baru', function () {
    $admin = akunAp('admin');

    $this->actingAs($admin)->post(route('ekstrakurikuler.store'), payloadEkskulAp())
        ->assertSessionMissing('kredensial');

    expect(Ekstrakurikuler::count())->toBe(1)
        ->and(User::count())->toBe(1)
        ->and(KredensialSementara::count())->toBe(0);
});

test('pembina yang membuat ekskul menjadi pembinanya sendiri dan email_pembina diabaikan', function () {
    $pembina = akunAp('pembina');

    $this->actingAs($pembina)
        ->post(route('ekstrakurikuler.store'), payloadEkskulAp(['email_pembina' => 'orang.lain@sekolah.test']));

    expect(User::where('email', 'orang.lain@sekolah.test')->exists())->toBeFalse()
        ->and(Ekstrakurikuler::firstOrFail()->pembinaAktif->pluck('id')->all())->toBe([$pembina->id]);
});

// ---------------------------------------------------------------------------
// Poin 2: kredensial terenkripsi + notifikasi
// ---------------------------------------------------------------------------

test('password sementara tidak tersimpan sebagai teks biasa, juga tidak ada di notifikasi', function () {
    [, , $password] = buatEkskulDenganPembinaBaruAp($this);

    $tersimpan = DB::table('kredensial_sementara')->value('password_terenkripsi');
    $semuaNotifikasi = DB::table('notifications')->pluck('data')->implode(' ');

    expect($tersimpan)->not->toContain($password)
        ->and(Crypt::decryptString($tersimpan))->toBe($password)
        ->and($semuaNotifikasi)->not->toContain($password);
});

test('admin dan pembina baru sama-sama mendapat notifikasi akun', function () {
    $adminLain = akunAp('admin', 'Admin Kedua');
    [$admin, $pembina] = buatEkskulDenganPembinaBaruAp($this);

    $notifPembina = $pembina->notifications()->get()->first(fn ($n) => $n->data['judul'] === 'Selamat datang, Pembina!');

    expect(judulNotifAp($admin))->toContain('Akun pembina dibuat')
        ->and(judulNotifAp($adminLain))->toContain('Akun pembina dibuat')   // riwayat semua admin
        ->and($notifPembina)->not->toBeNull()
        ->and($notifPembina->data['pesan'])->toContain($pembina->email)
        ->and($notifPembina->data['pesan'])->toContain('ganti password');
});

test('pembina baru boleh membaca notifikasi di login pertama, halaman lain diarahkan ke Pengaturan', function () {
    [, $pembina] = buatEkskulDenganPembinaBaruAp($this);

    $this->actingAs($pembina->fresh())->get(route('notifikasi.index'))->assertOk()->assertSee('Selamat datang, Pembina!');
    $this->actingAs($pembina->fresh())->get(route('ekstrakurikuler.index'))->assertRedirect(route('pengaturan.edit'));
});

test('halaman Kredensial hanya untuk admin', function () {
    $this->get(route('kredensial.index'))->assertRedirect(route('login'));

    $this->actingAs(akunAp('siswa'))->get(route('kredensial.index'))->assertForbidden();
    $this->actingAs(akunAp('pembina'))->get(route('kredensial.index'))->assertForbidden();
    $this->actingAs(akunAp('admin'))->get(route('kredensial.index'))->assertOk();
});

test('password tidak ada di halaman daftar, baru tampil setelah tombol Tampilkan ditekan sekali', function () {
    [$admin, $pembina, $password] = buatEkskulDenganPembinaBaruAp($this);
    $kredensial = KredensialSementara::firstOrFail();
    

    $this->actingAs($admin)->get(route('kredensial.index'))->assertOk()->assertSee('Bu Ratna')->assertDontSee($password);

    $this->actingAs($admin)->post(route('kredensial.tampilkan', $kredensial))
        ->assertSessionHas('kredensial_dibuka', fn ($v) => $v['password'] === $password);

    $this->actingAs($admin)->get(route('kredensial.index'))->assertSee($password);      // tampil sekali
    $this->actingAs($admin)->get(route('kredensial.index'))->assertDontSee($password);  // lalu hilang lagi

    expect($kredensial->fresh()->terakhir_dilihat_oleh)->toBe($admin->id)
        ->and($kredensial->fresh()->terakhir_dilihat_pada)->not->toBeNull();
});

test('kredensial yang kedaluwarsa tidak bisa dibuka, dan non-admin tidak bisa membukanya', function () {
    [$admin, $pembina] = buatEkskulDenganPembinaBaruAp($this);
    $kredensial = KredensialSementara::firstOrFail();
    $pembina->update(['must_change_password' => false]);

    $this->actingAs($pembina->fresh())->post(route('kredensial.tampilkan', $kredensial))->assertForbidden();

    $kredensial->update(['kedaluwarsa_pada' => now()->subMinute()]);

    $this->actingAs($admin)->post(route('kredensial.tampilkan', $kredensial))
        ->assertSessionHas('error')
        ->assertSessionMissing('kredensial_dibuka');
});

test('pembina mengganti password sementara: kredensial tersimpan ikut terhapus', function () {
    [, $pembina, $password] = buatEkskulDenganPembinaBaruAp($this);

    $this->actingAs($pembina->fresh())->put(route('pengaturan.password'), [
        'current_password' => $password,
        'password' => 'Panah-Emas-Lari-72!',
        'password_confirmation' => 'Panah-Emas-Lari-72!',
    ])->assertSessionHasNoErrors();

    expect(KredensialSementara::count())->toBe(0)
        ->and($pembina->fresh()->must_change_password)->toBeFalse();
});

test('admin mengatur ulang password pembina lewat Kelola User: kredensial lama dihapus', function () {
    [$admin, $pembina] = buatEkskulDenganPembinaBaruAp($this);

    $this->actingAs($admin)->put(route('users.update', $pembina), [
        'name' => $pembina->name,
        'email' => $pembina->email,
        'password' => 'Busur-Perak-Terbang-58!',
        'password_confirmation' => 'Busur-Perak-Terbang-58!',
    ])->assertSessionHasNoErrors();

    expect(KredensialSementara::count())->toBe(0)
        ->and($pembina->fresh()->must_change_password)->toBeTrue();
});

test('kredensial kedaluwarsa dibersihkan oleh model:prune, yang masih berlaku dibiarkan', function () {
    $segar = akunAp('pembina');
    $basi = akunAp('pembina');

    KredensialSementara::catat($segar, 'Rahasia-1', null, null);
    KredensialSementara::catat($basi, 'Rahasia-2', null, null)->update(['kedaluwarsa_pada' => now()->subDay()]);

    $this->artisan('model:prune', ['--model' => [KredensialSementara::class]])->assertSuccessful();

    expect(KredensialSementara::pluck('user_id')->all())->toBe([$segar->id]);
});
