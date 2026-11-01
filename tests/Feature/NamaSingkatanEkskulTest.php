<?php

use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunSg(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function ekskulSg(string $nama, ?string $singkatan = null): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'nama_singkatan' => $singkatan,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', 'ekskul')->value('id'),
    ]);
}

function payloadSg(array $tambahan = []): array
{
    return array_merge([
        'nama_ekstrakurikuler' => 'Palang Merah Remaja',
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'hari' => ['senin'],
        'jadwal' => ['senin' => ['jam_mulai' => '15:00', 'jam_selesai' => '17:00']],
    ], $tambahan);
}

// ---------------------------------------------------------------------------
// Model
// ---------------------------------------------------------------------------

test('nama_tampil memakai singkatan kalau ada, kalau tidak nama lengkap', function () {
    $futsal = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'nama_singkatan' => '  ', 'pembina' => 'x', 'status' => 'aktif']);

    expect(ekskulSg('Palang Merah Remaja', 'PMR')->nama_tampil)->toBe('PMR')
        ->and(ekskulSg('Basket')->nama_tampil)->toBe('Basket')
        ->and($futsal->nama_singkatan)->toBeNull()
        ->and($futsal->nama_tampil)->toBe('Futsal');
});

// ---------------------------------------------------------------------------
// Form: simpan dan ubah
// ---------------------------------------------------------------------------

test('admin membuat ekskul dengan nama lengkap dan singkatan', function () {
    $this->actingAs(akunSg('admin'))
        ->post(route('ekstrakurikuler.store'), payloadSg(['nama_singkatan' => 'PMR']))
        ->assertSessionHasNoErrors();

    $ekskul = Ekstrakurikuler::firstOrFail();

    expect($ekskul->nama_ekstrakurikuler)->toBe('Palang Merah Remaja')
        ->and($ekskul->nama_singkatan)->toBe('PMR');
});

test('singkatan boleh dikosongkan: tersimpan sebagai null', function () {
    $this->actingAs(akunSg('admin'))
        ->post(route('ekstrakurikuler.store'), payloadSg(['nama_singkatan' => '']))
        ->assertSessionHasNoErrors();

    expect(Ekstrakurikuler::firstOrFail()->nama_singkatan)->toBeNull();
});

test('singkatan kembar ditolak tanpa membedakan huruf besar/kecil, maksimal 30 karakter', function () {
    ekskulSg('Palang Merah Remaja', 'PMR');
    $admin = akunSg('admin');

    $this->actingAs($admin)
        ->post(route('ekstrakurikuler.store'), payloadSg(['nama_ekstrakurikuler' => 'Pecinta Mobil Racing', 'nama_singkatan' => 'pmr']))
        ->assertSessionHasErrors('nama_singkatan');

    $this->actingAs($admin)
        ->post(route('ekstrakurikuler.store'), payloadSg(['nama_ekstrakurikuler' => 'Panjang', 'nama_singkatan' => str_repeat('A', 31)]))
        ->assertSessionHasErrors('nama_singkatan');

    expect(Ekstrakurikuler::count())->toBe(1);
});

test('mengubah ekskul: singkatan sendiri boleh tetap, milik ekskul lain ditolak, boleh dikosongkan', function () {
    $pmr = ekskulSg('Palang Merah Remaja', 'PMR');
    ekskulSg('Pramuka Penggalang', 'PRAMUKA');
    $admin = akunSg('admin');

    // menyimpan ulang dengan singkatan yang sama (milik sendiri) tidak dianggap kembar
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $pmr->id), payloadSg(['nama_singkatan' => 'PMR']))
        ->assertSessionHasNoErrors();

    // singkatan ekskul lain ditolak dan data lama tetap
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $pmr->id), payloadSg(['nama_singkatan' => 'pramuka']))
        ->assertSessionHasErrors('nama_singkatan');
    expect($pmr->fresh()->nama_singkatan)->toBe('PMR');

    // dikosongkan
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $pmr->id), payloadSg(['nama_singkatan' => '']))
        ->assertSessionHasNoErrors();
    expect($pmr->fresh()->nama_singkatan)->toBeNull();
});

test('permintaan tanpa kolom singkatan tidak mengubah singkatan yang ada', function () {
    $pmr = ekskulSg('Palang Merah Remaja', 'PMR');

    $this->actingAs(akunSg('admin'))
        ->put(route('ekstrakurikuler.update', $pmr->id), payloadSg(['nama_ekstrakurikuler' => 'Palang Merah Remaja Sekolah']))
        ->assertSessionHasNoErrors();

    expect($pmr->fresh()->nama_singkatan)->toBe('PMR')
        ->and($pmr->fresh()->nama_ekstrakurikuler)->toBe('Palang Merah Remaja Sekolah');
});

test('pembina pengelola boleh mengatur singkatan ekskulnya', function () {
    $pembina = akunSg('pembina');
    $ekskul = ekskulSg('Karya Ilmiah Remaja');
    $ekskul->pembinas()->attach($pembina->id, ['mulai' => now()->toDateString()]);

    $this->actingAs($pembina)
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadSg(['nama_ekstrakurikuler' => 'Karya Ilmiah Remaja', 'nama_singkatan' => 'KIR']))
        ->assertSessionHasNoErrors();

    expect($ekskul->fresh()->nama_singkatan)->toBe('KIR');
});

test('form komunitas menyimpan singkatan dan menolak yang kembar', function () {
    ekskulSg('Palang Merah Remaja', 'PMR');
    $siswa = akunSg('siswa');

    $this->actingAs($siswa)
        ->post(route('komunitas.store'), ['nama_ekstrakurikuler' => 'Komunitas Fotografi', 'nama_singkatan' => 'FOTO'])
        ->assertSessionHasNoErrors();
    expect(Ekstrakurikuler::where('nama_ekstrakurikuler', 'Komunitas Fotografi')->value('nama_singkatan'))->toBe('FOTO');

    $this->actingAs($siswa)
        ->post(route('komunitas.store'), ['nama_ekstrakurikuler' => 'Komunitas Lain', 'nama_singkatan' => 'pmr'])
        ->assertSessionHasErrors('nama_singkatan');
});

// ---------------------------------------------------------------------------
// Tampilan
// ---------------------------------------------------------------------------

test('form tambah dan edit punya input Nama Lengkap dan Nama Singkatan', function () {
    $admin = akunSg('admin');
    $ekskul = ekskulSg('Palang Merah Remaja', 'PMR');

    $this->actingAs($admin)->get(route('ekstrakurikuler.create'))
        ->assertOk()
        ->assertSee('Nama Lengkap Ekskul')
        ->assertSee('Nama Singkatan / Alias');

    $this->actingAs($admin)->get(route('ekstrakurikuler.edit', $ekskul->id))
        ->assertOk()
        ->assertSee('Nama Lengkap Ekskul')
        ->assertSee('Nama Singkatan / Alias')
        ->assertSee('value="PMR"', false);
});

test('katalog menampilkan singkatan di judul kartu; ekskul tanpa singkatan tetap memakai nama lengkap', function () {
    ekskulSg('Palang Merah Remaja', 'PMR');
    ekskulSg('Basket');

    $this->actingAs(akunSg('siswa'))->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('>PMR</h2>', false)
        ->assertSee('>Basket</h2>', false)
        ->assertDontSee('>Palang Merah Remaja</h2>', false);
});

test('halaman detail tetap menampilkan nama lengkap, dengan singkatan sebagai lencana', function () {
    $ekskul = ekskulSg('Palang Merah Remaja', 'PMR');

    $this->actingAs(akunSg('siswa'))->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()
        ->assertSee('Palang Merah Remaja')
        ->assertSee('PMR');
});

test('ekskul yang berada di bawah organisasi menampilkan singkatan induknya di kartu', function () {
    $induk = Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => 'Majelis Perwakilan Kelas',
        'nama_singkatan' => 'MPK',
        'pembina' => 'x',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', 'organisasi')->value('id'),
    ]);
    Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => 'Organisasi Siswa Intra Sekolah',
        'nama_singkatan' => 'OSIS',
        'pembina' => 'x',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', 'organisasi')->value('id'),
        'induk_id' => $induk->id,
    ]);

    $this->actingAs(akunSg('admin'))->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Di bawah MPK');
});
