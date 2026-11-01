<?php

use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\KategoriEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunKe(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function ekskulKe(string $nama): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', 'ekskul')->value('id'),
    ]);
}

function kategoriKe(string $nama, array $tambahan = []): KategoriEkskul
{
    return KategoriEkskul::create(array_merge([
        'nama' => $nama,
        'slug' => Str::slug($nama),
        'urutan' => 20,
        'aktif' => true,
    ], $tambahan));
}

function idKategoriKe(string $slug): int
{
    return (int) KategoriEkskul::where('slug', $slug)->value('id');
}

function payloadKe(array $tambahan = []): array
{
    return array_merge([
        'nama_ekstrakurikuler' => 'Ekskul Uji',
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'hari' => ['senin'],
        'jadwal' => ['senin' => ['jam_mulai' => '15:00', 'jam_selesai' => '17:00']],
    ], $tambahan);
}

function idKategoriEkskulKe(Ekstrakurikuler $ekskul): array
{
    return $ekskul->kategoris()->pluck('kategori_ekskuls.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
}

// ---------------------------------------------------------------------------
// Data dan relasi
// ---------------------------------------------------------------------------

test('kategori awal tersedia dengan slug yang unik', function () {
    expect(KategoriEkskul::urut()->pluck('nama')->all())->toContain('Bela Diri', 'Seni & Olahraga')
        ->and(KategoriEkskul::pluck('slug')->unique()->count())->toBe(KategoriEkskul::count());
});

test('satu ekskul punya banyak kategori dan satu kategori dipakai banyak ekskul', function () {
    $silat = ekskulKe('Silat');
    $taekwondo = ekskulKe('Taekwondo');
    $bela = idKategoriKe('bela-diri');
    $seni = idKategoriKe('seni-olahraga');

    $silat->kategoris()->attach([$bela, $seni]);
    $taekwondo->kategoris()->attach($bela);

    expect(idKategoriEkskulKe($silat))->toEqualCanonicalizing([$bela, $seni])
        ->and(KategoriEkskul::find($bela)->ekskuls()->count())->toBe(2);
});

test('menghapus ekskul melepas kategorinya, kategorinya sendiri tetap ada', function () {
    $ekskul = ekskulKe('Silat');
    $ekskul->kategoris()->attach(idKategoriKe('bela-diri'));

    $ekskul->delete();

    expect(DB::table('ekstrakurikuler_kategori')->count())->toBe(0)
        ->and(KategoriEkskul::where('slug', 'bela-diri')->exists())->toBeTrue();
});

// ---------------------------------------------------------------------------
// Form ekskul: simpan dan ubah kategori
// ---------------------------------------------------------------------------

test('admin membuat ekskul dengan lebih dari satu kategori', function () {
    $bela = idKategoriKe('bela-diri');
    $seni = idKategoriKe('seni-olahraga');

    $this->actingAs(akunKe('admin'))
        ->post(route('ekstrakurikuler.store'), payloadKe([
            'nama_ekstrakurikuler' => 'Silat',
            'kategori_form' => '1',
            'kategori_ids' => [$bela, $seni],
        ]))
        ->assertSessionHasNoErrors();

    $silat = Ekstrakurikuler::where('nama_ekstrakurikuler', 'Silat')->firstOrFail();

    expect($silat->kategoris()->pluck('nama')->all())->toEqualCanonicalizing(['Bela Diri', 'Seni & Olahraga']);
});

test('ekskul boleh dibuat tanpa kategori', function () {
    $this->actingAs(akunKe('admin'))
        ->post(route('ekstrakurikuler.store'), payloadKe(['kategori_form' => '1']))
        ->assertSessionHasNoErrors();

    expect(Ekstrakurikuler::firstOrFail()->kategoris()->count())->toBe(0);
});

test('mengubah ekskul mengganti kategorinya; tanpa penanda form kategori dibiarkan', function () {
    $admin = akunKe('admin');
    $ekskul = ekskulKe('Taekwondo');
    $ekskul->kategoris()->attach([idKategoriKe('bela-diri'), idKategoriKe('seni-olahraga')]);
    $teknologi = idKategoriKe('teknologi');

    // ganti ke satu kategori lain
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadKe(['kategori_form' => '1', 'kategori_ids' => [$teknologi]]))
        ->assertSessionHasNoErrors();
    expect(idKategoriEkskulKe($ekskul))->toBe([$teknologi]);

    // permintaan tanpa penanda form: kategori tidak berubah
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadKe())
        ->assertSessionHasNoErrors();
    expect(idKategoriEkskulKe($ekskul))->toBe([$teknologi]);

    // penanda ada tapi tidak ada yang dipilih: semua kategori dilepas
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadKe(['kategori_form' => '1']))
        ->assertSessionHasNoErrors();
    expect(idKategoriEkskulKe($ekskul))->toBe([]);
});

test('kategori nonaktif tidak bisa dipilih baru, tetapi yang sudah melekat boleh dipertahankan', function () {
    $admin = akunKe('admin');
    $lama = kategoriKe('Kategori Lama', ['aktif' => false]);
    $baru = kategoriKe('Kategori Baru');

    $ekskulA = ekskulKe('Ekskul A');
    $ekskulA->kategoris()->attach($lama->id);
    $ekskulB = ekskulKe('Ekskul B');

    // Ekskul B tidak bisa mengambil kategori nonaktif
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $ekskulB->id), payloadKe(['kategori_form' => '1', 'kategori_ids' => [$lama->id]]))
        ->assertSessionHasNoErrors();
    expect(idKategoriEkskulKe($ekskulB))->toBe([]);

    // Ekskul A mempertahankan yang lama dan boleh menambah yang baru
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $ekskulA->id), payloadKe(['kategori_form' => '1', 'kategori_ids' => [$lama->id, $baru->id]]))
        ->assertSessionHasNoErrors();
    expect(idKategoriEkskulKe($ekskulA))->toEqualCanonicalizing([$lama->id, $baru->id]);

    // Setelah dilepas, kategori nonaktif tidak bisa dipasang kembali
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $ekskulA->id), payloadKe(['kategori_form' => '1', 'kategori_ids' => [$baru->id]]))
        ->assertSessionHasNoErrors();
    $this->actingAs($admin)
        ->put(route('ekstrakurikuler.update', $ekskulA->id), payloadKe(['kategori_form' => '1', 'kategori_ids' => [$lama->id, $baru->id]]))
        ->assertSessionHasNoErrors();
    expect(idKategoriEkskulKe($ekskulA))->toBe([$baru->id]);
});

test('id kategori yang tidak dikenal ditolak dan ekskul tidak jadi dibuat', function () {
    $this->actingAs(akunKe('admin'))
        ->post(route('ekstrakurikuler.store'), payloadKe(['kategori_form' => '1', 'kategori_ids' => [99999]]))
        ->assertSessionHasErrors('kategori_ids.0');

    expect(Ekstrakurikuler::count())->toBe(0);
});

test('pembina pengelola boleh mengatur kategori ekskulnya, pembina lain tidak', function () {
    $pembina = akunKe('pembina');
    $ekskul = ekskulKe('Robotik');
    $ekskul->pembinas()->attach($pembina->id, ['mulai' => now()->toDateString()]);
    $teknologi = idKategoriKe('teknologi');

    $this->actingAs($pembina)
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadKe(['kategori_form' => '1', 'kategori_ids' => [$teknologi]]))
        ->assertSessionHasNoErrors();
    expect(idKategoriEkskulKe($ekskul))->toBe([$teknologi]);

    $this->actingAs(akunKe('pembina'))
        ->put(route('ekstrakurikuler.update', $ekskul->id), payloadKe(['kategori_form' => '1', 'kategori_ids' => []]))
        ->assertForbidden();
    expect(idKategoriEkskulKe($ekskul))->toBe([$teknologi]);
});

// ---------------------------------------------------------------------------
// Tampilan
// ---------------------------------------------------------------------------

test('form tambah menawarkan kategori aktif; form edit menambah kategori nonaktif yang melekat', function () {
    $admin = akunKe('admin');
    $nonaktif = kategoriKe('Tidak Dipakai', ['aktif' => false]);
    $ekskul = ekskulKe('Ekskul Lama');
    $ekskul->kategoris()->attach($nonaktif->id);

    $this->actingAs($admin)->get(route('ekstrakurikuler.create'))
        ->assertOk()
        ->assertSee('Bela Diri')
        ->assertDontSee('Tidak Dipakai');

    $this->actingAs($admin)->get(route('ekstrakurikuler.edit', $ekskul->id))
        ->assertOk()
        ->assertSee('Tidak Dipakai (nonaktif)');

    // pembina yang membuat ekskul/komunitas juga memilih kategori
    $this->actingAs(akunKe('pembina'))->get(route('ekstrakurikuler.create'))
        ->assertOk()
        ->assertSee('Bela Diri');
});

test('katalog dan halaman detail menampilkan semua kategori ekskul', function () {
    $ekskul = ekskulKe('Silat Sekolah');
    $ekskul->kategoris()->attach([idKategoriKe('bela-diri'), idKategoriKe('seni-olahraga')]);
    $siswa = akunKe('siswa');

    $this->actingAs($siswa)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Bela Diri')
        ->assertSee('Seni & Olahraga');

    $this->actingAs($siswa)->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()
        ->assertSee('Bela Diri')
        ->assertSee('Seni & Olahraga');
});

// ---------------------------------------------------------------------------
// Halaman Kategori Ekskul (khusus admin)
// ---------------------------------------------------------------------------

test('admin menambah kategori; slug dibuat otomatis dan tetap unik', function () {
    $admin = akunKe('admin');

    $this->actingAs($admin)->post(route('kategori-ekskul.store'), ['nama' => 'Seni Pertunjukan', 'urutan' => 7])
        ->assertSessionHasNoErrors();
    $this->actingAs($admin)->post(route('kategori-ekskul.store'), ['nama' => 'Seni Pertunjukan!'])
        ->assertSessionHasNoErrors();

    expect(KategoriEkskul::where('nama', 'Seni Pertunjukan')->value('slug'))->toBe('seni-pertunjukan')
        ->and(KategoriEkskul::where('nama', 'Seni Pertunjukan!')->value('slug'))->toBe('seni-pertunjukan-2');
});

test('nama kategori kembar atau kosong ditolak', function () {
    $admin = akunKe('admin');

    $this->actingAs($admin)->post(route('kategori-ekskul.store'), ['nama' => 'Bela Diri'])->assertSessionHasErrors('nama');
    $this->actingAs($admin)->post(route('kategori-ekskul.store'), ['nama' => ''])->assertSessionHasErrors('nama');
});

test('mengubah kategori tidak mengganti slug; centang aktif dilepas = nonaktif', function () {
    $kategori = kategoriKe('Seni Lama');

    $this->actingAs(akunKe('admin'))
        ->patch(route('kategori-ekskul.update', $kategori), ['nama' => 'Seni Panggung', 'urutan' => 3])
        ->assertSessionHasNoErrors();

    $kategori->refresh();
    expect($kategori->nama)->toBe('Seni Panggung')
        ->and($kategori->slug)->toBe('seni-lama')
        ->and($kategori->urutan)->toBe(3)
        ->and($kategori->aktif)->toBeFalse();
});

test('kategori yang masih dipakai tidak bisa dihapus, yang tidak dipakai bisa', function () {
    $admin = akunKe('admin');
    $dipakai = kategoriKe('Dipakai');
    $kosong = kategoriKe('Kosong');
    ekskulKe('Ekskul X')->kategoris()->attach($dipakai->id);

    $this->actingAs($admin)->delete(route('kategori-ekskul.destroy', $dipakai))->assertSessionHas('error');
    expect(KategoriEkskul::whereKey($dipakai->id)->exists())->toBeTrue();

    $this->actingAs($admin)->delete(route('kategori-ekskul.destroy', $kosong))->assertSessionHas('success');
    expect(KategoriEkskul::whereKey($kosong->id)->exists())->toBeFalse();
});

test('halaman Kategori Ekskul hanya untuk admin', function () {
    $this->actingAs(akunKe('admin'))->get(route('kategori-ekskul.index'))->assertOk()->assertSee('Bela Diri');
    $this->actingAs(akunKe('pembina'))->get(route('kategori-ekskul.index'))->assertForbidden();
    $this->actingAs(akunKe('siswa'))->get(route('kategori-ekskul.index'))->assertForbidden();
    $this->actingAs(akunKe('pembina'))->post(route('kategori-ekskul.store'), ['nama' => 'Coba'])->assertForbidden();
});
