<?php

use App\Models\EkskulTautan;
use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunKs(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

// Ekskul dengan seluruh kontak terisi
function ekskulKs(string $nama = 'Palang Merah Remaja', array $tambahan = []): Ekstrakurikuler
{
    return Ekstrakurikuler::create(array_merge([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', 'ekskul')->value('id'),
        'wa_grup_url' => 'https://chat.whatsapp.com/GrupRahasia123',
        'wa_pembina' => '081234567890',
        'telegram_url' => 'https://t.me/pmr_rahasia',
        'instagram_url' => 'https://instagram.com/pmr_rahasia',
        'tiktok_url' => 'https://tiktok.com/@pmr_rahasia',
    ], $tambahan));
}

function daftarKs(User $akun, Ekstrakurikuler $ekskul, string $status = 'diterima', string $statusAnggota = 'aktif'): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $akun->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $akun->name,
        'peran' => 'siswa',
        'status' => $status,
        'status_anggota' => $statusAnggota,
    ]);
}

function payloadKs(array $tambahan = []): array
{
    return array_merge([
        'nama_ekstrakurikuler' => 'Palang Merah Remaja',
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'hari' => ['senin'],
        'jadwal' => ['senin' => ['jam_mulai' => '15:00', 'jam_selesai' => '17:00']],
    ], $tambahan);
}

// Penanda yang hanya boleh muncul untuk yang berhak
const KONTAK_RAHASIA_KS = ['GrupRahasia123', 'wa.me/6281234567890', '081234567890', 't.me/pmr_rahasia', 'instagram.com/pmr_rahasia', 'tiktok.com/@pmr_rahasia'];

// ---------------------------------------------------------------------------
// Yang berhak melihat
// ---------------------------------------------------------------------------

test('anggota aktif melihat semua kontak dan sosmed ekskulnya, dalam urutan yang benar', function () {
    $ekskul = ekskulKs();
    $siswa = akunKs();
    daftarKs($siswa, $ekskul);

    $this->actingAs($siswa)->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()
        ->assertSee('Kontak & Sosial Media')
        ->assertSee('https://chat.whatsapp.com/GrupRahasia123', false)
        ->assertSee('https://wa.me/6281234567890', false)
        ->assertSee('(081234567890)', false)
        ->assertSee('https://t.me/pmr_rahasia', false)
        ->assertSee('https://instagram.com/pmr_rahasia', false)
        ->assertSee('https://tiktok.com/@pmr_rahasia', false)
        ->assertSeeInOrder(['Grup WhatsApp', 'Chat Pembina', 'Telegram', 'Instagram', 'TikTok']);
});

test('admin dan pembina ekskul itu melihat kontak, pembina ekskul lain tidak', function () {
    $ekskul = ekskulKs();
    $pembina = akunKs('pembina');
    $ekskul->pembinas()->attach($pembina->id, ['mulai' => now()->toDateString()]);

    $this->actingAs(akunKs('admin'))->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()->assertSee('https://chat.whatsapp.com/GrupRahasia123', false);

    $this->actingAs($pembina)->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()->assertSee('https://chat.whatsapp.com/GrupRahasia123', false);

    $pembinaLain = akunKs('pembina');
    $response = $this->actingAs($pembinaLain)->get(route('ekstrakurikuler.show', $ekskul->id))->assertOk();
    foreach (KONTAK_RAHASIA_KS as $rahasia) {
        $response->assertDontSee($rahasia, false);
    }
});

// ---------------------------------------------------------------------------
// Yang tidak boleh melihat: tidak ada satu pun data kontak di HTML-nya
// ---------------------------------------------------------------------------

test('siswa non-anggota tidak melihat kontak maupun judul bloknya', function () {
    $ekskul = ekskulKs();

    $response = $this->actingAs(akunKs())->get(route('ekstrakurikuler.show', $ekskul->id))->assertOk();

    $response->assertDontSee('Kontak & Sosial Media');
    foreach (KONTAK_RAHASIA_KS as $rahasia) {
        $response->assertDontSee($rahasia, false);
    }
});

test('pendaftar yang masih menunggu atau ditolak tidak melihat kontak', function () {
    $ekskul = ekskulKs();
    $menunggu = akunKs();
    $ditolak = akunKs();
    daftarKs($menunggu, $ekskul, 'pending');
    daftarKs($ditolak, $ekskul, 'ditolak');

    foreach ([$menunggu, $ditolak] as $akun) {
        $response = $this->actingAs($akun)->get(route('ekstrakurikuler.show', $ekskul->id))->assertOk();
        foreach (KONTAK_RAHASIA_KS as $rahasia) {
            $response->assertDontSee($rahasia, false);
        }
    }
});

test('mantan anggota (keluar, dikeluarkan, selesai) tidak melihat kontak lagi', function () {
    $ekskul = ekskulKs();

    foreach (['keluar', 'dikeluarkan', 'selesai'] as $statusAnggota) {
        $akun = akunKs();
        daftarKs($akun, $ekskul, 'diterima', $statusAnggota);

        $response = $this->actingAs($akun)->get(route('ekstrakurikuler.show', $ekskul->id))->assertOk();
        foreach (KONTAK_RAHASIA_KS as $rahasia) {
            $response->assertDontSee($rahasia, false);
        }
    }
});

test('anggota aktif ekskul lain tidak melihat kontak ekskul ini', function () {
    $ekskul = ekskulKs();
    $lain = ekskulKs('Pramuka Penggalang', [
        'wa_grup_url' => null, 'wa_pembina' => null, 'telegram_url' => null, 'instagram_url' => null, 'tiktok_url' => null,
    ]);
    $siswa = akunKs();
    daftarKs($siswa, $lain);

    $response = $this->actingAs($siswa)->get(route('ekstrakurikuler.show', $ekskul->id))->assertOk();
    foreach (KONTAK_RAHASIA_KS as $rahasia) {
        $response->assertDontSee($rahasia, false);
    }
});

test('tautan publik ekskul tetap terlihat oleh siapa pun, tidak ikut disembunyikan', function () {
    $ekskul = ekskulKs();
    EkskulTautan::create([
        'ekstrakurikuler_id' => $ekskul->id,
        'platform' => 'youtube',
        'label' => 'Kanal Resmi PMR',
        'url' => 'https://youtube.com/@pmr',
    ]);

    $this->actingAs(akunKs())->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()
        ->assertSee('Kanal Resmi PMR')
        ->assertDontSee('GrupRahasia123', false);
});

// ---------------------------------------------------------------------------
// Blok kosong
// ---------------------------------------------------------------------------

test('kontak belum diisi: anggota tidak melihat blok, pengelola melihat petunjuk', function () {
    $ekskul = ekskulKs('Futsal', [
        'wa_grup_url' => null, 'wa_pembina' => null, 'telegram_url' => null, 'instagram_url' => null, 'tiktok_url' => null,
    ]);
    $anggota = akunKs();
    daftarKs($anggota, $ekskul);

    $this->actingAs($anggota)->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()->assertDontSee('Kontak & Sosial Media');

    $this->actingAs(akunKs('admin'))->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()->assertSee('Kontak & Sosial Media')->assertSee('Belum diisi');
});

// ---------------------------------------------------------------------------
// Form: simpan, ubah, validasi
// ---------------------------------------------------------------------------

test('admin menyimpan kontak saat membuat ekskul; nomor pembina disimpan dalam format 62', function () {
    $this->actingAs(akunKs('admin'))
        ->post(route('ekstrakurikuler.store'), payloadKs([
            'wa_grup_url' => 'https://chat.whatsapp.com/abc',
            'wa_pembina' => '0812-3456-7890',
            'telegram_url' => 'https://t.me/pmr',
            'instagram_url' => 'https://www.instagram.com/pmr',
            'tiktok_url' => 'https://www.tiktok.com/@pmr',
        ]))
        ->assertSessionHasNoErrors();

    $ekskul = Ekstrakurikuler::firstOrFail();

    expect($ekskul->wa_grup_url)->toBe('https://chat.whatsapp.com/abc')
        ->and($ekskul->wa_pembina)->toBe('6281234567890')
        ->and($ekskul->wa_pembina_tampilan)->toBe('081234567890')
        ->and($ekskul->telegram_url)->toBe('https://t.me/pmr')
        ->and($ekskul->instagram_url)->toBe('https://www.instagram.com/pmr')
        ->and($ekskul->tiktok_url)->toBe('https://www.tiktok.com/@pmr');
});

test('pembina pengelola mengisi dan mengosongkan kontak lewat edit', function () {
    $pembina = akunKs('pembina');
    $ekskul = ekskulKs();
    $ekskul->pembinas()->attach($pembina->id, ['mulai' => now()->toDateString()]);

    $this->actingAs($pembina)
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadKs([
            'wa_grup_url' => '',
            'wa_pembina' => '+62 898 7654 3210',
            'telegram_url' => '',
            'instagram_url' => 'https://instagram.com/baru',
            'tiktok_url' => '',
        ]))
        ->assertSessionHasNoErrors();

    $ekskul->refresh();

    expect($ekskul->wa_grup_url)->toBeNull()
        ->and($ekskul->wa_pembina)->toBe('6289876543210')
        ->and($ekskul->telegram_url)->toBeNull()
        ->and($ekskul->instagram_url)->toBe('https://instagram.com/baru')
        ->and($ekskul->tiktok_url)->toBeNull();
});

test('alamat yang bukan milik platformnya atau tidak https ditolak, termasuk domain tiruan', function () {
    $admin = akunKs('admin');

    $salah = [
        ['wa_grup_url', 'https://example.com/grup'],
        ['wa_grup_url', 'http://chat.whatsapp.com/abc'],
        ['telegram_url', 'https://instagram.com/pmr'],
        ['instagram_url', 'https://tiktok.com/@pmr'],
        ['instagram_url', 'https://instagram.com.evil.example/pmr'],
        ['tiktok_url', 'https://notiktok.com/@pmr'],
        ['tiktok_url', 'bukan-alamat'],
    ];

    foreach ($salah as [$kolom, $nilai]) {
        $this->actingAs($admin)
            ->post(route('ekstrakurikuler.store'), payloadKs([$kolom => $nilai]))
            ->assertSessionHasErrors($kolom);
    }

    expect(Ekstrakurikuler::count())->toBe(0);
});

test('nomor WhatsApp pembina yang tidak valid ditolak', function () {
    $admin = akunKs('admin');

    foreach (['12345', 'abc', '0812abc34567', '021234567', '+1 415 555 2671'] as $nomor) {
        $this->actingAs($admin)
            ->post(route('ekstrakurikuler.store'), payloadKs(['wa_pembina' => $nomor]))
            ->assertSessionHasErrors('wa_pembina');
    }

    expect(Ekstrakurikuler::count())->toBe(0);
});

test('form tambah dan edit punya input kontak; edit menampilkan nomor dalam format 08', function () {
    $admin = akunKs('admin');
    $ekskul = ekskulKs();

    $this->actingAs($admin)->get(route('ekstrakurikuler.create'))
        ->assertOk()
        ->assertSee('Kontak & Sosial Media')
        ->assertSee('Link Grup WhatsApp')
        ->assertSee('Nomor WhatsApp Pembina')
        ->assertSee('Link Telegram')
        ->assertSee('name="instagram_url"', false)
        ->assertSee('name="tiktok_url"', false);

    $this->actingAs($admin)->get(route('ekstrakurikuler.edit', $ekskul->id))
        ->assertOk()
        ->assertSee('value="081234567890"', false)
        ->assertSee('value="https://t.me/pmr_rahasia"', false);
});

test('katalog tidak pernah memuat kontak, bahkan untuk admin', function () {
    ekskulKs();

    $response = $this->actingAs(akunKs('admin'))->get(route('ekstrakurikuler.index'))->assertOk();
    foreach (KONTAK_RAHASIA_KS as $rahasia) {
        $response->assertDontSee($rahasia, false);
    }
});
