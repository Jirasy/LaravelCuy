<?php

use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\User;
use App\Services\OsisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunJe(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function idJenisJe(string $kode): int
{
    return (int) JenisEkskul::where('kode', $kode)->value('id');
}

function ekskulJe(string $nama, string $kodeJenis = 'ekskul'): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => idJenisJe($kodeJenis),
    ]);
}

function payloadJe(array $tambahan = []): array
{
    return array_merge([
        'nama_ekstrakurikuler' => 'Ekskul Uji',
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'hari' => ['senin'],
        'jadwal' => ['senin' => ['jam_mulai' => '15:00', 'jam_selesai' => '17:00']],
    ], $tambahan);
}

// ---------------------------------------------------------------------------
// Data bawaan & sinkronisasi dengan kolom lama
// ---------------------------------------------------------------------------

test('jenis bawaan tersedia: hanya OSIS yang memakai seleksi CaSis, OSIS dan MPK hanya satu', function () {
    $jenis = JenisEkskul::pluck('alur_pendaftaran', 'kode')->all();

    expect(array_keys($jenis))->toEqualCanonicalizing(['ekskul', 'osis', 'mpk', 'pramuka', 'pmr', 'paskibra'])
        ->and($jenis['osis'])->toBe('seleksi_casis')
        ->and(collect($jenis)->except('osis')->unique()->values()->all())->toBe(['langsung'])
        ->and(JenisEkskul::where('hanya_satu', true)->pluck('kode')->sort()->values()->all())->toBe(['mpk', 'osis'])
        ->and(JenisEkskul::where('bawaan', true)->pluck('kode')->sort()->values()->all())->toBe(['ekskul', 'osis']);
});

test('kolom lama jenis dan jenis_ekskul_id selalu sinkron', function () {
    // cara lama (kode lama yang masih mengisi "jenis")
    $lamaOsis = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'OSIS', 'pembina' => 'x', 'status' => 'aktif', 'jenis' => 'osis']);
    $lamaBiasa = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'x', 'status' => 'aktif']);

    expect($lamaOsis->jenis_ekskul_id)->toBe(idJenisJe('osis'))
        ->and($lamaOsis->memakaiCasis())->toBeTrue()
        ->and($lamaBiasa->jenis_ekskul_id)->toBe(idJenisJe('ekskul'))
        ->and($lamaBiasa->jenis)->toBe('ekskul');

    // cara baru: jenis non-OSIS tetap berperilaku "langsung"
    $pmr = ekskulJe('PMR Sekolah', 'pmr');
    expect($pmr->jenis)->toBe('ekskul')->and($pmr->memakaiCasis())->toBeFalse();

    // mengganti jenis memperbarui kolom lama, dan sebaliknya
    $pmr->update(['jenis_ekskul_id' => idJenisJe('osis')]);
    expect($pmr->fresh()->jenis)->toBe('osis')->and($pmr->fresh()->memakaiCasis())->toBeTrue();

    $pmr->update(['jenis_ekskul_id' => idJenisJe('pramuka')]);
    expect($pmr->fresh()->jenis)->toBe('ekskul');

    $pmr->update(['jenis' => 'osis']);
    expect($pmr->fresh()->jenis_ekskul_id)->toBe(idJenisJe('osis'));
});

// ---------------------------------------------------------------------------
// Memilih jenis di form ekskul
// ---------------------------------------------------------------------------

test('admin membuat ekskul dengan jenis PMR, dan kartunya menampilkan jenisnya', function () {
    $this->actingAs(akunJe('admin'))
        ->post(route('ekstrakurikuler.store'), payloadJe(['nama_ekstrakurikuler' => 'PMR Garuda', 'jenis_ekskul_id' => idJenisJe('pmr')]))
        ->assertSessionHasNoErrors();

    $ekskul = Ekstrakurikuler::firstOrFail();

    expect($ekskul->jenis_ekskul_id)->toBe(idJenisJe('pmr'))
        ->and($ekskul->memakaiCasis())->toBeFalse();

    $this->actingAs(akunJe('admin'))->get(route('ekstrakurikuler.index'))
        ->assertOk()->assertSee('PMR Garuda')->assertSee('PMR');
});

test('tanpa memilih jenis, ekskul baru berjenis Ekskul Biasa', function () {
    $this->actingAs(akunJe('admin'))->post(route('ekstrakurikuler.store'), payloadJe())->assertSessionHasNoErrors();

    expect(Ekstrakurikuler::firstOrFail()->jenis_ekskul_id)->toBe(idJenisJe('ekskul'));
});

test('pembina tidak bisa menentukan jenis ekskul, hanya admin', function () {
    $pembina = akunJe('pembina');

    $this->actingAs($pembina)
        ->post(route('ekstrakurikuler.store'), payloadJe(['jenis_ekskul_id' => idJenisJe('paskibra')]))
        ->assertSessionHasNoErrors();

    expect(Ekstrakurikuler::firstOrFail()->jenis_ekskul_id)->toBe(idJenisJe('ekskul'));
});

test('jenis yang tidak dikenal ditolak', function () {
    $this->actingAs(akunJe('admin'))->post(route('ekstrakurikuler.store'), payloadJe(['jenis_ekskul_id' => 99999]))
        ->assertSessionHasErrors('jenis_ekskul_id');

    expect(Ekstrakurikuler::count())->toBe(0);
});

test('jenis "hanya satu" tidak boleh dimiliki dua ekskul, sedangkan jenis lain boleh banyak', function (string $kode, bool $ditolak) {
    ekskulJe('Pertama', $kode);

    $respons = $this->actingAs(akunJe('admin'))
        ->post(route('ekstrakurikuler.store'), payloadJe(['nama_ekstrakurikuler' => 'Kedua', 'jenis_ekskul_id' => idJenisJe($kode)]));

    if ($ditolak) {
        $respons->assertSessionHasErrors('jenis_ekskul_id');
        expect(Ekstrakurikuler::count())->toBe(1);
    } else {
        $respons->assertSessionHasNoErrors();
        expect(Ekstrakurikuler::count())->toBe(2);
    }
})->with([
    'OSIS' => ['osis', true],
    'MPK' => ['mpk', true],
    'Pramuka' => ['pramuka', false],
    'PMR' => ['pmr', false],
    'Ekskul Biasa' => ['ekskul', false],
]);

test('mengedit ekskul yang sudah berjenis hanya-satu tidak dianggap duplikat dirinya sendiri', function () {
    $mpk = ekskulJe('MPK Sekolah', 'mpk');

    $this->actingAs(akunJe('admin'))
        ->put(route('ekstrakurikuler.update', $mpk->id), payloadJe(['nama_ekstrakurikuler' => 'MPK Sekolah Baru', 'jenis_ekskul_id' => idJenisJe('mpk')]))
        ->assertSessionHasNoErrors();

    expect($mpk->fresh()->nama_ekstrakurikuler)->toBe('MPK Sekolah Baru');
});

test('jenis nonaktif tidak bisa dipilih ekskul baru, tapi ekskul yang sudah memakainya tetap bisa diedit', function () {
    $paskibra = ekskulJe('Paskibra Sekolah', 'paskibra');
    JenisEkskul::where('kode', 'paskibra')->update(['aktif' => false]);
    $admin = akunJe('admin');

    $this->actingAs($admin)->post(route('ekstrakurikuler.store'), payloadJe(['jenis_ekskul_id' => idJenisJe('paskibra')]))
        ->assertSessionHasErrors('jenis_ekskul_id');
    expect(Ekstrakurikuler::count())->toBe(1);

    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $paskibra->id), payloadJe(['nama_ekstrakurikuler' => 'Paskibra Juara', 'jenis_ekskul_id' => idJenisJe('paskibra')]))
        ->assertSessionHasNoErrors();
    expect($paskibra->fresh()->nama_ekstrakurikuler)->toBe('Paskibra Juara');

    // form edit tetap menampilkan jenisnya (diberi tanda nonaktif), form tambah tidak menawarkannya
    $this->actingAs($admin)->get(route('ekstrakurikuler.edit', $paskibra->id))->assertSee('Paskibra (nonaktif)');
    $this->actingAs($admin)->get(route('ekstrakurikuler.create'))->assertDontSee('Paskibra');
});

test('form ekskul menampilkan pilihan jenis hanya untuk admin', function () {
    $this->actingAs(akunJe('admin'))->get(route('ekstrakurikuler.create'))
        ->assertOk()->assertSee('name="jenis_ekskul_id"', false)->assertSee('Pramuka')->assertSee('Paskibra');

    $this->actingAs(akunJe('pembina'))->get(route('ekstrakurikuler.create'))
        ->assertOk()->assertDontSee('name="jenis_ekskul_id"', false);
});

// ---------------------------------------------------------------------------
// Dampak ke alur pendaftaran & katalog
// ---------------------------------------------------------------------------

test('hanya ekskul berjenis OSIS yang dialihkan ke jalur CaSis, jenis lain memakai pendaftaran biasa', function () {
    $osis = ekskulJe('OSIS', 'osis');
    $pmr = ekskulJe('PMR Garuda', 'pmr');
    $siswa = akunJe('siswa');

    expect(app(OsisService::class)->osis()->id)->toBe($osis->id);

    $this->actingAs($siswa)->get(route('pendaftaran.create', ['ekstrakurikuler_id' => $osis->id]))
        ->assertRedirect(route('casis.create'));

    $this->actingAs($siswa)->get(route('pendaftaran.create', ['ekstrakurikuler_id' => $pmr->id]))
        ->assertOk()->assertSee('PMR Garuda');
});

test('mengganti jenis sebuah ekskul menjadi OSIS memindahkan jalur CaSis ke ekskul itu', function () {
    $ekskul = ekskulJe('Wadah Siswa', 'ekskul');
    expect(app(OsisService::class)->osis())->toBeNull();

    $this->actingAs(akunJe('admin'))
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadJe(['nama_ekstrakurikuler' => 'Wadah Siswa', 'jenis_ekskul_id' => idJenisJe('osis')]))
        ->assertSessionHasNoErrors();

    expect(app(OsisService::class)->osis()->id)->toBe($ekskul->id);
});

test('katalog menampilkan jenis di kartu dan bisa disaring per jenis', function () {
    ekskulJe('PMR Garuda', 'pmr');
    ekskulJe('Pramuka Pandega', 'pramuka');
    ekskulJe('Futsal Putra', 'ekskul');
    $admin = akunJe('admin');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index'))
        ->assertOk()->assertSee('PMR Garuda')->assertSee('Pramuka Pandega')->assertSee('Futsal Putra')
        ->assertSee('PMR (1)')->assertSee('Pramuka (1)')->assertSee('Ekskul Biasa (1)');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['jenis' => 'pmr']))
        ->assertOk()->assertSee('PMR Garuda')->assertDontSee('Pramuka Pandega')->assertDontSee('Futsal Putra');

    // kode jenis yang tidak dikenal = tidak menyaring apa pun
    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['jenis' => 'asal']))
        ->assertOk()->assertSee('PMR Garuda')->assertSee('Futsal Putra');
});

// ---------------------------------------------------------------------------
// Kelola jenis (admin)
// ---------------------------------------------------------------------------

test('halaman Jenis Ekskul hanya untuk admin', function () {
    $this->get(route('jenis-ekskul.index'))->assertRedirect(route('login'));
    $this->actingAs(akunJe('siswa'))->get(route('jenis-ekskul.index'))->assertForbidden();
    $this->actingAs(akunJe('pembina'))->get(route('jenis-ekskul.index'))->assertForbidden();
    $this->actingAs(akunJe('pembina'))->post(route('jenis-ekskul.store'), ['nama' => 'Baru'])->assertForbidden();

    $this->actingAs(akunJe('admin'))->get(route('jenis-ekskul.index'))
        ->assertOk()->assertSee('Paskibra')->assertSee('Tambah jenis baru');

    expect(JenisEkskul::count())->toBe(6);
});

test('admin menambah jenis baru: kodenya otomatis unik, alurnya selalu pendaftaran langsung', function () {
    $admin = akunJe('admin');

    $this->actingAs($admin)->post(route('jenis-ekskul.store'), ['nama' => 'Paduan Suara', 'hanya_satu' => 1, 'urutan' => 7])
        ->assertSessionHas('success');
    $this->actingAs($admin)->post(route('jenis-ekskul.store'), ['nama' => 'Paduan-Suara'])
        ->assertSessionHas('success');   // nama beda, tapi kode dasarnya sama

    $pertama = JenisEkskul::where('nama', 'Paduan Suara')->firstOrFail();
    $kedua = JenisEkskul::where('nama', 'Paduan-Suara')->firstOrFail();

    expect($pertama->kode)->toBe('paduan_suara')
        ->and($kedua->kode)->toBe('paduan_suara_2')
        ->and($pertama->alur_pendaftaran)->toBe('langsung')
        ->and($pertama->hanya_satu)->toBeTrue()
        ->and($pertama->bawaan)->toBeFalse()
        ->and($pertama->urutan)->toBe(7)
        ->and($kedua->hanya_satu)->toBeFalse();
});

test('nama jenis wajib, unik, dan tidak boleh terlalu panjang', function (array $isian) {
    $this->actingAs(akunJe('admin'))->post(route('jenis-ekskul.store'), $isian)->assertSessionHasErrors('nama');

    expect(JenisEkskul::count())->toBe(6);
})->with([
    'kosong' => [['nama' => '']],
    'sudah ada' => [['nama' => 'Pramuka']],
    'terlalu panjang' => [['nama' => str_repeat('x', 61)]],
]);

test('jenis non-bawaan bisa diubah namanya dan dinonaktifkan', function () {
    $pmr = JenisEkskul::where('kode', 'pmr')->firstOrFail();

    $this->actingAs(akunJe('admin'))->patch(route('jenis-ekskul.update', $pmr), ['nama' => 'Palang Merah Remaja', 'urutan' => 9, 'hanya_satu' => 1])
        ->assertSessionHas('success');

    $pmr->refresh();

    expect($pmr->nama)->toBe('Palang Merah Remaja')
        ->and($pmr->kode)->toBe('pmr')            // kode tetap
        ->and($pmr->urutan)->toBe(9)
        ->and($pmr->hanya_satu)->toBeTrue()
        ->and($pmr->aktif)->toBeFalse();          // checkbox "aktif" tidak dikirim = dinonaktifkan
});

test('jenis bawaan tidak bisa dinonaktifkan, dan OSIS selalu hanya-satu dengan alur CaSis', function () {
    $admin = akunJe('admin');

    foreach (['osis', 'ekskul'] as $kode) {
        $jenis = JenisEkskul::where('kode', $kode)->firstOrFail();
        $this->actingAs($admin)->patch(route('jenis-ekskul.update', $jenis), ['nama' => $jenis->nama])->assertSessionHas('success');
    }

    $osis = JenisEkskul::where('kode', 'osis')->firstOrFail();
    $biasa = JenisEkskul::where('kode', 'ekskul')->firstOrFail();

    expect($osis->aktif)->toBeTrue()
        ->and($osis->hanya_satu)->toBeTrue()
        ->and($osis->alur_pendaftaran)->toBe('seleksi_casis')
        ->and($biasa->aktif)->toBeTrue();
});

test('menandai jenis "hanya satu" ditolak kalau sudah ada lebih dari satu ekskul berjenis itu', function () {
    ekskulJe('PMR A', 'pmr');
    ekskulJe('PMR B', 'pmr');
    $pmr = JenisEkskul::where('kode', 'pmr')->firstOrFail();

    $this->actingAs(akunJe('admin'))->patch(route('jenis-ekskul.update', $pmr), ['nama' => 'PMR', 'hanya_satu' => 1, 'aktif' => 1])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'Sudah ada 2 ekskul'));

    expect($pmr->fresh()->hanya_satu)->toBeFalse();

    Ekstrakurikuler::where('nama_ekstrakurikuler', 'PMR B')->delete();

    $this->actingAs(akunJe('admin'))->patch(route('jenis-ekskul.update', $pmr), ['nama' => 'PMR', 'hanya_satu' => 1, 'aktif' => 1])
        ->assertSessionHas('success');
    expect($pmr->fresh()->hanya_satu)->toBeTrue();
});

test('jenis hanya bisa dihapus kalau bukan bawaan dan tidak dipakai ekskul mana pun', function () {
    $admin = akunJe('admin');
    ekskulJe('PMR Garuda', 'pmr');

    $this->actingAs($admin)->delete(route('jenis-ekskul.destroy', JenisEkskul::where('kode', 'osis')->first()))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'bawaan'));
    $this->actingAs($admin)->delete(route('jenis-ekskul.destroy', JenisEkskul::where('kode', 'pmr')->first()))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'masih dipakai 1 ekskul'));
    expect(JenisEkskul::count())->toBe(6);

    $this->actingAs($admin)->delete(route('jenis-ekskul.destroy', JenisEkskul::where('kode', 'paskibra')->first()))
        ->assertSessionHas('success');
    expect(JenisEkskul::where('kode', 'paskibra')->exists())->toBeFalse();
});

test('menghapus akun pembina atau jenis tidak merusak ekskul: jenis yang terhapus mengembalikan ke Ekskul Biasa saat disimpan lagi', function () {
    $ekskul = ekskulJe('Ekskul Yatim', 'paskibra');

    // dihapus lewat DB langsung (di luar jalur aman) -> FK nullOnDelete
    JenisEkskul::where('kode', 'paskibra')->delete();
    expect($ekskul->fresh()->jenis_ekskul_id)->toBeNull();

    $ekskul->fresh()->update(['nama_ekstrakurikuler' => 'Ekskul Yatim Baru']);

    expect($ekskul->fresh()->jenis_ekskul_id)->toBe(idJenisJe('ekskul'));
});
