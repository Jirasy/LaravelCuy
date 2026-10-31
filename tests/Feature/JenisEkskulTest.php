<?php

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

// Tipe tambahan buatan admin (bukan bawaan)
function tipeJe(string $kode, array $tambahan = []): JenisEkskul
{
    return JenisEkskul::create(array_merge([
        'kode' => $kode,
        'nama' => ucfirst($kode).' Khusus',
        'alur_pendaftaran' => 'langsung',
        'kategori' => 'ekskul',
        'hanya_satu' => false,
        'aktif' => true,
        'bawaan' => false,
        'urutan' => 20,
    ], $tambahan));
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

test('tipe bawaan: Ekskul Biasa, Ekskul Seleksi, Organisasi, Komunitas, dengan cara masuk dan kategorinya', function () {
    $tipe = JenisEkskul::all()->keyBy('kode');

    expect($tipe->keys()->all())->toEqualCanonicalizing(['ekskul', 'seleksi', 'organisasi', 'komunitas'])
        ->and($tipe['ekskul']->alur_pendaftaran)->toBe('langsung')
        ->and($tipe['seleksi']->alur_pendaftaran)->toBe('seleksi')
        ->and($tipe['organisasi']->alur_pendaftaran)->toBe('seleksi')
        ->and($tipe['komunitas']->alur_pendaftaran)->toBe('langsung')
        ->and($tipe['organisasi']->kategori)->toBe('organisasi')
        ->and($tipe['komunitas']->kategori)->toBe('komunitas')
        ->and($tipe['ekskul']->kategori)->toBe('ekskul')
        ->and($tipe['seleksi']->kategori)->toBe('ekskul')
        ->and(JenisEkskul::where('hanya_satu', true)->count())->toBe(0)
        ->and(JenisEkskul::where('bawaan', true)->count())->toBe(4);
});

test('kolom lama jenis dan jenis_ekskul_id selalu sinkron (osis = organisasi)', function () {
    // cara lama: kolom "jenis" masih bisa diisi dengan kode lama
    $lamaOsis = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'OSIS', 'pembina' => 'x', 'status' => 'aktif', 'jenis' => 'osis']);
    $lamaBiasa = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'x', 'status' => 'aktif']);

    expect($lamaOsis->jenis_ekskul_id)->toBe(idJenisJe('organisasi'))
        ->and($lamaOsis->memakaiCasis())->toBeTrue()
        ->and($lamaBiasa->jenis_ekskul_id)->toBe(idJenisJe('ekskul'))
        ->and($lamaBiasa->jenis)->toBe('ekskul');

    // cara baru: Ekskul Seleksi memakai seleksi tapi bukan organisasi
    $seleksi = ekskulJe('Paskibra Sekolah', 'seleksi');
    expect($seleksi->jenis)->toBe('ekskul')->and($seleksi->memakaiCasis())->toBeTrue()->and($seleksi->isOrganisasi())->toBeFalse();

    // mengganti tipe memperbarui kolom lama, dan sebaliknya
    $seleksi->update(['jenis_ekskul_id' => idJenisJe('organisasi')]);
    expect($seleksi->fresh()->jenis)->toBe('osis')->and($seleksi->fresh()->isOrganisasi())->toBeTrue();

    $seleksi->update(['jenis_ekskul_id' => idJenisJe('ekskul')]);
    expect($seleksi->fresh()->jenis)->toBe('ekskul')->and($seleksi->fresh()->memakaiCasis())->toBeFalse();

    $seleksi->update(['jenis' => 'osis']);
    expect($seleksi->fresh()->jenis_ekskul_id)->toBe(idJenisJe('organisasi'));
});

test('sebutan calon: CaSis untuk OSIS, Calon Pengurus untuk organisasi lain, Calon Anggota untuk ekskul seleksi', function () {
    expect(ekskulJe('OSIS SMK', 'organisasi')->label_calon)->toBe('CaSis')
        ->and(ekskulJe('MPK', 'organisasi')->label_calon)->toBe('Calon Pengurus')
        ->and(ekskulJe('Paskibra', 'seleksi')->label_calon)->toBe('Calon Anggota');
});

// ---------------------------------------------------------------------------
// Memilih tipe di form ekskul
// ---------------------------------------------------------------------------

test('admin membuat ekskul bertipe Ekskul Seleksi, dan kartunya menampilkan tipenya', function () {
    $this->actingAs(akunJe('admin'))
        ->post(route('ekstrakurikuler.store'), payloadJe(['nama_ekstrakurikuler' => 'Paskibra Garuda', 'jenis_ekskul_id' => idJenisJe('seleksi')]))
        ->assertSessionHasNoErrors();

    $ekskul = Ekstrakurikuler::firstOrFail();

    expect($ekskul->jenis_ekskul_id)->toBe(idJenisJe('seleksi'))
        ->and($ekskul->memakaiCasis())->toBeTrue()
        ->and($ekskul->diresmikan_pada)->toBeNull()
        ->and($ekskul->isKomunitas())->toBeFalse();

    $this->actingAs(akunJe('admin'))->get(route('ekstrakurikuler.index'))
        ->assertOk()->assertSee('Paskibra Garuda')->assertSee('Ekskul Seleksi');
});

test('tanpa memilih tipe, ekskul buatan admin berjenis Ekskul Biasa', function () {
    $this->actingAs(akunJe('admin'))->post(route('ekstrakurikuler.store'), payloadJe())->assertSessionHasNoErrors();

    expect(Ekstrakurikuler::firstOrFail()->jenis_ekskul_id)->toBe(idJenisJe('ekskul'));
});

test('ekskul yang dibuat pembina berstatus komunitas, dan pembina tidak bisa menentukan tipenya', function () {
    $pembina = akunJe('pembina');

    $this->actingAs($pembina)
        ->post(route('ekstrakurikuler.store'), payloadJe(['jenis_ekskul_id' => idJenisJe('organisasi')]))
        ->assertSessionHasNoErrors();

    $ekskul = Ekstrakurikuler::firstOrFail();

    expect($ekskul->jenis_ekskul_id)->toBe(idJenisJe('komunitas'))
        ->and($ekskul->isKomunitas())->toBeTrue()
        ->and($ekskul->dibuat_oleh)->toBe($pembina->id);
});

test('tipe yang tidak dikenal ditolak', function () {
    $this->actingAs(akunJe('admin'))->post(route('ekstrakurikuler.store'), payloadJe(['jenis_ekskul_id' => 99999]))
        ->assertSessionHasErrors('jenis_ekskul_id');

    expect(Ekstrakurikuler::count())->toBe(0);
});

test('admin tidak bisa membuat ekskul bertipe Komunitas: ekskul buatan admin langsung resmi', function () {
    $this->actingAs(akunJe('admin'))->post(route('ekstrakurikuler.store'), payloadJe(['jenis_ekskul_id' => idJenisJe('komunitas')]))
        ->assertSessionHasErrors('jenis_ekskul_id');

    expect(Ekstrakurikuler::count())->toBe(0);
});

test('tipe "hanya satu" tidak boleh dimiliki dua ekskul, sedangkan tipe lain boleh banyak', function (string $kode, bool $ditolak) {
    tipeJe('tunggal', ['hanya_satu' => true]);
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
    'tipe tambahan hanya satu' => ['tunggal', true],
    'Organisasi' => ['organisasi', false],
    'Ekskul Seleksi' => ['seleksi', false],
    'Ekskul Biasa' => ['ekskul', false],
]);

test('mengedit ekskul yang sudah bertipe hanya-satu tidak dianggap duplikat dirinya sendiri', function () {
    tipeJe('tunggal', ['hanya_satu' => true]);
    $ekskul = ekskulJe('Wadah Tunggal', 'tunggal');

    $this->actingAs(akunJe('admin'))
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadJe(['nama_ekstrakurikuler' => 'Wadah Tunggal Baru', 'jenis_ekskul_id' => idJenisJe('tunggal')]))
        ->assertSessionHasNoErrors();

    expect($ekskul->fresh()->nama_ekstrakurikuler)->toBe('Wadah Tunggal Baru');
});

test('tipe nonaktif tidak bisa dipilih ekskul baru, tapi ekskul yang sudah memakainya tetap bisa diedit', function () {
    tipeJe('khusus');
    $ekskul = ekskulJe('Ekskul Khusus', 'khusus');
    JenisEkskul::where('kode', 'khusus')->update(['aktif' => false]);
    $admin = akunJe('admin');

    $this->actingAs($admin)->post(route('ekstrakurikuler.store'), payloadJe(['jenis_ekskul_id' => idJenisJe('khusus')]))
        ->assertSessionHasErrors('jenis_ekskul_id');
    expect(Ekstrakurikuler::count())->toBe(1);

    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadJe(['nama_ekstrakurikuler' => 'Ekskul Khusus Juara', 'jenis_ekskul_id' => idJenisJe('khusus')]))
        ->assertSessionHasNoErrors();
    expect($ekskul->fresh()->nama_ekstrakurikuler)->toBe('Ekskul Khusus Juara');

    // form edit tetap menampilkan tipenya (diberi tanda nonaktif), form tambah tidak menawarkannya
    $this->actingAs($admin)->get(route('ekstrakurikuler.edit', $ekskul->id))->assertSee('Khusus Khusus (nonaktif)');
    $this->actingAs($admin)->get(route('ekstrakurikuler.create'))->assertDontSee('Khusus Khusus');
});

test('form ekskul menampilkan pilihan tipe hanya untuk admin', function () {
    $this->actingAs(akunJe('admin'))->get(route('ekstrakurikuler.create'))
        ->assertOk()->assertSee('name="jenis_ekskul_id"', false)->assertSee('Ekskul Seleksi')->assertSee('Organisasi');

    $this->actingAs(akunJe('pembina'))->get(route('ekstrakurikuler.create'))
        ->assertOk()->assertDontSee('name="jenis_ekskul_id"', false);
});

// ---------------------------------------------------------------------------
// Dampak ke alur pendaftaran & katalog
// ---------------------------------------------------------------------------

test('ekskul bertipe Ekskul Seleksi dan Organisasi dialihkan ke jalur seleksi, tipe lain memakai pendaftaran biasa', function () {
    $organisasi = ekskulJe('OSIS', 'organisasi');
    $seleksi = ekskulJe('Paskibra', 'seleksi');
    $biasa = ekskulJe('Futsal Garuda', 'ekskul');
    $siswa = akunJe('siswa');

    expect(app(OsisService::class)->osis()->id)->toBe($organisasi->id);

    $this->actingAs($siswa)->get(route('pendaftaran.create', ['ekstrakurikuler_id' => $organisasi->id]))
        ->assertRedirect(route('casis.create', $organisasi->id));
    $this->actingAs($siswa)->get(route('pendaftaran.create', ['ekstrakurikuler_id' => $seleksi->id]))
        ->assertRedirect(route('casis.create', $seleksi->id));

    $this->actingAs($siswa)->get(route('pendaftaran.create', ['ekstrakurikuler_id' => $biasa->id]))
        ->assertOk()->assertSee('Futsal Garuda');
});

test('mengganti tipe sebuah ekskul menjadi Organisasi menjadikannya organisasi utama', function () {
    $ekskul = ekskulJe('Wadah Siswa', 'ekskul');
    expect(app(OsisService::class)->osis())->toBeNull();

    $this->actingAs(akunJe('admin'))
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadJe(['nama_ekstrakurikuler' => 'Wadah Siswa', 'jenis_ekskul_id' => idJenisJe('organisasi')]))
        ->assertSessionHasNoErrors();

    expect(app(OsisService::class)->osis()->id)->toBe($ekskul->id);
});

test('katalog menampilkan tipe di kartu dan bisa disaring per tipe', function () {
    ekskulJe('Paskibra Garuda', 'seleksi');
    ekskulJe('OSIS Pandega', 'organisasi');
    ekskulJe('Futsal Putra', 'ekskul');
    $admin = akunJe('admin');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index'))
        ->assertOk()->assertSee('Paskibra Garuda')->assertSee('OSIS Pandega')->assertSee('Futsal Putra')
        ->assertSee('Ekskul Seleksi (1)')->assertSee('Organisasi (1)')->assertSee('Ekskul Biasa (1)');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['jenis' => 'seleksi']))
        ->assertOk()->assertSee('Paskibra Garuda')->assertDontSee('Futsal Putra');

    // kode tipe yang tidak dikenal = tidak menyaring apa pun
    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['jenis' => 'asal']))
        ->assertOk()->assertSee('Paskibra Garuda')->assertSee('Futsal Putra');
});

// ---------------------------------------------------------------------------
// Kelola tipe (admin)
// ---------------------------------------------------------------------------

test('halaman Tipe Ekskul hanya untuk admin', function () {
    $this->get(route('jenis-ekskul.index'))->assertRedirect(route('login'));
    $this->actingAs(akunJe('siswa'))->get(route('jenis-ekskul.index'))->assertForbidden();
    $this->actingAs(akunJe('pembina'))->get(route('jenis-ekskul.index'))->assertForbidden();
    $this->actingAs(akunJe('pembina'))->post(route('jenis-ekskul.store'), ['nama' => 'Baru'])->assertForbidden();

    $this->actingAs(akunJe('admin'))->get(route('jenis-ekskul.index'))
        ->assertOk()->assertSee('Ekskul Seleksi')->assertSee('Tambah tipe baru');

    expect(JenisEkskul::count())->toBe(4);
});

test('admin menambah tipe baru: kodenya otomatis unik, cara masuk dan kategori bisa dipilih', function () {
    $admin = akunJe('admin');

    $this->actingAs($admin)->post(route('jenis-ekskul.store'), ['nama' => 'Paduan Suara', 'hanya_satu' => 1, 'urutan' => 7])
        ->assertSessionHas('success');
    $this->actingAs($admin)->post(route('jenis-ekskul.store'), ['nama' => 'Paduan-Suara', 'alur_pendaftaran' => 'seleksi', 'kategori' => 'organisasi'])
        ->assertSessionHas('success');   // nama beda, tapi kode dasarnya sama

    $pertama = JenisEkskul::where('nama', 'Paduan Suara')->firstOrFail();
    $kedua = JenisEkskul::where('nama', 'Paduan-Suara')->firstOrFail();

    expect($pertama->kode)->toBe('paduan_suara')
        ->and($kedua->kode)->toBe('paduan_suara_2')
        ->and($pertama->alur_pendaftaran)->toBe('langsung')
        ->and($pertama->kategori)->toBe('ekskul')
        ->and($pertama->hanya_satu)->toBeTrue()
        ->and($pertama->bawaan)->toBeFalse()
        ->and($pertama->urutan)->toBe(7)
        ->and($kedua->alur_pendaftaran)->toBe('seleksi')
        ->and($kedua->kategori)->toBe('organisasi')
        ->and($kedua->memakaiCasis())->toBeTrue()
        ->and($kedua->hanya_satu)->toBeFalse();
});

test('kategori komunitas tidak bisa dibuat admin sebagai tipe baru', function () {
    $this->actingAs(akunJe('admin'))->post(route('jenis-ekskul.store'), ['nama' => 'Mirip Komunitas', 'kategori' => 'komunitas'])
        ->assertSessionHasErrors('kategori');

    expect(JenisEkskul::count())->toBe(4);
});

test('nama tipe wajib, unik, dan tidak boleh terlalu panjang', function (array $isian) {
    $this->actingAs(akunJe('admin'))->post(route('jenis-ekskul.store'), $isian)->assertSessionHasErrors('nama');

    expect(JenisEkskul::count())->toBe(4);
})->with([
    'kosong' => [['nama' => '']],
    'sudah ada' => [['nama' => 'Organisasi']],
    'terlalu panjang' => [['nama' => str_repeat('x', 61)]],
]);

test('tipe tambahan bisa diubah nama, cara masuk, dan dinonaktifkan', function () {
    $tipe = tipeJe('khusus');

    $this->actingAs(akunJe('admin'))->patch(route('jenis-ekskul.update', $tipe), ['nama' => 'Seleksi Khusus', 'urutan' => 9, 'hanya_satu' => 1, 'alur_pendaftaran' => 'seleksi', 'kategori' => 'organisasi'])
        ->assertSessionHas('success');

    $tipe->refresh();

    expect($tipe->nama)->toBe('Seleksi Khusus')
        ->and($tipe->kode)->toBe('khusus')                 // kode tetap
        ->and($tipe->urutan)->toBe(9)
        ->and($tipe->hanya_satu)->toBeTrue()
        ->and($tipe->alur_pendaftaran)->toBe('seleksi')
        ->and($tipe->kategori)->toBe('organisasi')
        ->and($tipe->aktif)->toBeFalse();                  // checkbox "aktif" tidak dikirim = dinonaktifkan
});

test('tipe bawaan tidak bisa dinonaktifkan dan cara masuk serta kategorinya tidak bisa diubah', function () {
    $admin = akunJe('admin');

    foreach (['organisasi', 'ekskul', 'seleksi', 'komunitas'] as $kode) {
        $tipe = JenisEkskul::where('kode', $kode)->firstOrFail();
        $this->actingAs($admin)->patch(route('jenis-ekskul.update', $tipe), ['nama' => $tipe->nama, 'alur_pendaftaran' => 'langsung', 'kategori' => 'ekskul'])
            ->assertSessionHas('success');
    }

    $organisasi = JenisEkskul::where('kode', 'organisasi')->firstOrFail();

    expect(JenisEkskul::where('aktif', true)->count())->toBe(4)
        ->and($organisasi->alur_pendaftaran)->toBe('seleksi')
        ->and($organisasi->kategori)->toBe('organisasi')
        ->and(JenisEkskul::where('kode', 'komunitas')->value('kategori'))->toBe('komunitas');
});

test('menandai tipe "hanya satu" ditolak kalau sudah ada lebih dari satu ekskul bertipe itu', function () {
    $tipe = tipeJe('khusus');
    ekskulJe('Khusus A', 'khusus');
    ekskulJe('Khusus B', 'khusus');

    $this->actingAs(akunJe('admin'))->patch(route('jenis-ekskul.update', $tipe), ['nama' => 'Khusus', 'hanya_satu' => 1, 'aktif' => 1])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'Sudah ada 2 ekskul'));

    expect($tipe->fresh()->hanya_satu)->toBeFalse();

    Ekstrakurikuler::where('nama_ekstrakurikuler', 'Khusus B')->delete();

    $this->actingAs(akunJe('admin'))->patch(route('jenis-ekskul.update', $tipe), ['nama' => 'Khusus', 'hanya_satu' => 1, 'aktif' => 1])
        ->assertSessionHas('success');
    expect($tipe->fresh()->hanya_satu)->toBeTrue();
});

test('tipe hanya bisa dihapus kalau bukan bawaan dan tidak dipakai ekskul mana pun', function () {
    $admin = akunJe('admin');
    tipeJe('dipakai');
    tipeJe('kosong');
    ekskulJe('Ekskul Terpakai', 'dipakai');

    $this->actingAs($admin)->delete(route('jenis-ekskul.destroy', JenisEkskul::where('kode', 'organisasi')->first()))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'bawaan'));
    $this->actingAs($admin)->delete(route('jenis-ekskul.destroy', JenisEkskul::where('kode', 'dipakai')->first()))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'masih dipakai 1 ekskul'));
    expect(JenisEkskul::count())->toBe(6);

    $this->actingAs($admin)->delete(route('jenis-ekskul.destroy', JenisEkskul::where('kode', 'kosong')->first()))
        ->assertSessionHas('success');
    expect(JenisEkskul::where('kode', 'kosong')->exists())->toBeFalse();
});

test('menghapus tipe tidak merusak ekskul: tipe yang terhapus mengembalikan ke Ekskul Biasa saat disimpan lagi', function () {
    tipeJe('sementara');
    $ekskul = ekskulJe('Ekskul Yatim', 'sementara');

    // dihapus lewat DB langsung (di luar jalur aman) -> FK nullOnDelete
    JenisEkskul::where('kode', 'sementara')->delete();
    expect($ekskul->fresh()->jenis_ekskul_id)->toBeNull();

    $ekskul->fresh()->update(['nama_ekstrakurikuler' => 'Ekskul Yatim Baru']);

    expect($ekskul->fresh()->jenis_ekskul_id)->toBe(idJenisJe('ekskul'));
});
