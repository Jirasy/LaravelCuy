<?php

use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\KategoriEkskul;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunPk(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function ekskulPk(string $nama, array $tambahan = [], string $kodeJenis = 'ekskul'): Ekstrakurikuler
{
    return Ekstrakurikuler::create(array_merge([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', $kodeJenis)->value('id'),
    ], $tambahan));
}

// Menambah $jumlah pendaftar dengan status tertentu ke sebuah ekskul
function pendaftarPk(Ekstrakurikuler $ekskul, int $jumlah, string $status = 'diterima', string $statusAnggota = 'aktif'): void
{
    User::factory()->count($jumlah)->create(['role' => 'siswa', 'is_active' => true])->each(function (User $akun) use ($ekskul, $status, $statusAnggota) {
        PendaftaranEkskul::create([
            'user_id' => $akun->id,
            'ekstrakurikuler_id' => $ekskul->id,
            'nama_pendaftar' => $akun->name,
            'peran' => 'siswa',
            'status' => $status,
            'status_anggota' => $statusAnggota,
        ]);
    });
}

// ---------------------------------------------------------------------------
// Saringan jumlah anggota
// ---------------------------------------------------------------------------

test('ukuran ekskul menurut jumlah anggota aktif memakai batas yang benar', function (int $jumlah, string $harap) {
    expect(Ekstrakurikuler::ukuranAnggota($jumlah))->toBe($harap);
})->with([
    'kosong' => [0, 'kecil'],
    'batas atas kecil' => [14, 'kecil'],
    'batas bawah sedang' => [15, 'sedang'],
    'batas atas sedang' => [29, 'sedang'],
    'batas bawah besar' => [30, 'besar'],
    'sangat besar' => [200, 'besar'],
]);

test('menyaring katalog berdasarkan jumlah anggota; hanya anggota aktif yang diterima yang dihitung', function () {
    $kecil = ekskulPk('Ekskul Kecil Uji');
    $sedang = ekskulPk('Ekskul Sedang Uji');
    $hampirSedang = ekskulPk('Ekskul Hampir Sedang');

    pendaftarPk($kecil, 3);
    pendaftarPk($sedang, 15);
    // 14 anggota aktif + 6 yang tidak dihitung (pending, keluar): tetap kecil
    pendaftarPk($hampirSedang, 14);
    pendaftarPk($hampirSedang, 3, 'pending', 'aktif');
    pendaftarPk($hampirSedang, 3, 'diterima', 'keluar');

    $admin = akunPk('admin');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['anggota' => 'kecil']))
        ->assertOk()
        ->assertSee('Ekskul Kecil Uji')->assertSee('Ekskul Hampir Sedang')
        ->assertDontSee('Ekskul Sedang Uji');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['anggota' => 'sedang']))
        ->assertOk()
        ->assertSee('Ekskul Sedang Uji')
        ->assertDontSee('Ekskul Kecil Uji')->assertDontSee('Ekskul Hampir Sedang');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['anggota' => 'besar']))
        ->assertOk()
        ->assertSee('Tidak ada ekskul yang cocok dengan saringan ini');
});

test('pill jumlah anggota menampilkan hitungan per ukuran dan ikut terhitung bersama saringan lain', function () {
    $a = ekskulPk('Ekskul A Uji');
    $b = ekskulPk('Ekskul B Uji', ['pendaftaran_dibuka' => false]);
    $c = ekskulPk('Ekskul C Uji');
    pendaftarPk($c, 15);

    $admin = akunPk('admin');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Kecil · < 15 (2)')
        ->assertSee('Sedang · 15–29 (1)')
        ->assertSee('Besar · 30+ (0)');

    // dengan status=tutup, hanya Ekskul B (kecil) yang tersisa
    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['status' => 'tutup']))
        ->assertOk()
        ->assertSee('Kecil · < 15 (1)')
        ->assertSee('Sedang · 15–29 (0)');
});

test('nilai anggota yang tidak dikenal atau array tidak menyaring apa pun, dan tombol reset muncul saat aktif', function () {
    ekskulPk('Ekskul Biasa Uji');
    $admin = akunPk('admin');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['anggota' => 'raksasa']))
        ->assertOk()->assertSee('Ekskul Biasa Uji')->assertDontSee('Reset filter');

    $this->actingAs($admin)->get('/ekstrakurikuler?anggota[]=kecil')
        ->assertOk()->assertSee('Ekskul Biasa Uji');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['anggota' => 'kecil']))
        ->assertOk()->assertSee('Reset filter');
});

// ---------------------------------------------------------------------------
// Status pendaftaran dihitung sekali (tanpa query berulang)
// ---------------------------------------------------------------------------

test('setelah diingat, status pendaftaran dibaca tanpa query tambahan', function () {
    $paskibra = ekskulPk('Paskibra Uji', [], 'seleksi')->fresh();

    // tanpa memo: ekskul seleksi memeriksa periode rekrutmen lewat query
    DB::enableQueryLog();
    $paskibra->pendaftaran_terbuka;
    expect(count(DB::getQueryLog()))->toBeGreaterThan(0);

    $paskibra->ingatStatusPendaftaran();

    DB::flushQueryLog();
    $terbuka = $paskibra->pendaftaran_terbuka;
    $label = $paskibra->label_pendaftaran;
    $alasan = $paskibra->alasanPendaftaranTutup();

    expect(count(DB::getQueryLog()))->toBe(0)
        ->and($terbuka)->toBeFalse()
        ->and($label)->toBe('Pendaftaran Ditutup')
        ->and($alasan)->toBe('ditutup');
});

// ---------------------------------------------------------------------------
// Tombol "Tentang" di kartu rekrutmen
// ---------------------------------------------------------------------------

test('tombol Tentang di kartu rekrutmen memakai singkatan, atau nama lengkap kalau tidak ada singkatan', function () {
    ekskulPk('Paskibra Garuda', ['nama_singkatan' => 'PASKI'], 'seleksi');
    ekskulPk('Pecinta Alam Rimba', [], 'seleksi');

    $this->actingAs(akunPk('siswa'))->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Tentang PASKI')
        ->assertSee('Tentang Pecinta Alam Rimba');
});

// ---------------------------------------------------------------------------
// Form komunitas: kategori dan kontak
// ---------------------------------------------------------------------------

test('siswa membuat komunitas dengan kategori dan kontak anggota', function () {
    $siswa = akunPk('siswa');
    $bela = KategoriEkskul::where('slug', 'bela-diri')->value('id');
    $teknologi = KategoriEkskul::where('slug', 'teknologi')->value('id');

    $this->actingAs($siswa)->post(route('komunitas.store'), [
        'nama_ekstrakurikuler' => 'Komunitas Silat Muda',
        'nama_singkatan' => 'SILAT',
        'kategori_form' => '1',
        'kategori_ids' => [$bela, $teknologi],
        'wa_grup_url' => 'https://chat.whatsapp.com/KomunitasSilat',
        'telegram_url' => 'https://t.me/silatmuda',
        'instagram_url' => 'https://instagram.com/silatmuda',
        'tiktok_url' => 'https://tiktok.com/@silatmuda',
        // nomor pembina tidak berlaku untuk komunitas: harus diabaikan
        'wa_pembina' => '081234567890',
    ])->assertSessionHasNoErrors();

    $komunitas = Ekstrakurikuler::where('nama_ekstrakurikuler', 'Komunitas Silat Muda')->firstOrFail();

    expect($komunitas->kategoris()->pluck('kategori_ekskuls.id')->sort()->values()->all())->toEqualCanonicalizing([$bela, $teknologi])
        ->and($komunitas->wa_grup_url)->toBe('https://chat.whatsapp.com/KomunitasSilat')
        ->and($komunitas->telegram_url)->toBe('https://t.me/silatmuda')
        ->and($komunitas->instagram_url)->toBe('https://instagram.com/silatmuda')
        ->and($komunitas->tiktok_url)->toBe('https://tiktok.com/@silatmuda')
        ->and($komunitas->wa_pembina)->toBeNull();

    // pendiri adalah anggota aktif, jadi melihat kontaknya di halaman komunitas
    $this->actingAs($siswa)->get(route('ekstrakurikuler.show', $komunitas->id))
        ->assertOk()
        ->assertSee('https://chat.whatsapp.com/KomunitasSilat', false);
});

test('komunitas tanpa kategori dan kontak tetap bisa dibuat', function () {
    $this->actingAs(akunPk('siswa'))->post(route('komunitas.store'), [
        'nama_ekstrakurikuler' => 'Komunitas Polos',
        'kategori_form' => '1',
    ])->assertSessionHasNoErrors();

    $komunitas = Ekstrakurikuler::where('nama_ekstrakurikuler', 'Komunitas Polos')->firstOrFail();

    expect($komunitas->kategoris()->count())->toBe(0)
        ->and($komunitas->wa_grup_url)->toBeNull();
});

test('komunitas menolak kontak yang bukan milik platformnya dan kategori yang tidak dikenal', function () {
    $siswa = akunPk('siswa');

    $this->actingAs($siswa)->post(route('komunitas.store'), [
        'nama_ekstrakurikuler' => 'Komunitas Salah',
        'instagram_url' => 'https://example.com/saya',
    ])->assertSessionHasErrors('instagram_url');

    $this->actingAs($siswa)->post(route('komunitas.store'), [
        'nama_ekstrakurikuler' => 'Komunitas Salah',
        'kategori_form' => '1',
        'kategori_ids' => [99999],
    ])->assertSessionHasErrors('kategori_ids.0');

    expect(Ekstrakurikuler::where('nama_ekstrakurikuler', 'Komunitas Salah')->exists())->toBeFalse();
});

test('form komunitas punya pilihan kategori dan kontak, tanpa isian nomor pembina', function () {
    $this->actingAs(akunPk('siswa'))->get(route('komunitas.create'))
        ->assertOk()
        ->assertSee('Kategori ekskul')
        ->assertSee('Bela Diri')
        ->assertSee('Link Grup WhatsApp')
        ->assertSee('name="tiktok_url"', false)
        ->assertDontSee('Nomor WhatsApp Pembina');
});
