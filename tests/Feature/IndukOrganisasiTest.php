<?php

use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function adminIo(): User
{
    return User::factory()->create(['role' => 'admin', 'is_active' => true]);
}

function ekskulIo(string $nama, string $tipe = 'organisasi', array $tambahan = []): Ekstrakurikuler
{
    return Ekstrakurikuler::create(array_merge([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => JenisEkskul::where('kode', $tipe)->value('id'),
    ], $tambahan));
}

function formIo(Ekstrakurikuler $ekskul, array $tambahan = []): array
{
    return array_merge([
        'nama_ekstrakurikuler' => $ekskul->nama_ekstrakurikuler,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => $ekskul->jenis_ekskul_id,
        'hari' => ['senin'],
        'jadwal' => ['senin' => ['jam_mulai' => '15:00', 'jam_selesai' => '17:00']],
    ], $tambahan);
}

test('admin menempatkan OSIS di bawah MPK, dan MPK tercatat membawahi OSIS', function () {
    $mpk = ekskulIo('MPK');
    $osis = ekskulIo('OSIS');

    $this->actingAs(adminIo())->put(route('ekstrakurikuler.update', $osis->id), formIo($osis, ['induk_id' => $mpk->id]))
        ->assertSessionHasNoErrors();

    expect($osis->fresh()->induk_id)->toBe($mpk->id)
        ->and($osis->fresh()->induk->nama_ekstrakurikuler)->toBe('MPK')
        ->and($mpk->bawahan()->pluck('nama_ekstrakurikuler')->all())->toBe(['OSIS']);
});

test('induk bisa dilepas dengan mengosongkan pilihan', function () {
    $mpk = ekskulIo('MPK');
    $osis = ekskulIo('OSIS', 'organisasi', ['induk_id' => $mpk->id]);

    $this->actingAs(adminIo())->put(route('ekstrakurikuler.update', $osis->id), formIo($osis, ['induk_id' => '']))
        ->assertSessionHasNoErrors();

    expect($osis->fresh()->induk_id)->toBeNull();
});

test('induk harus organisasi, bukan dirinya sendiri, dan tidak boleh membentuk lingkaran', function () {
    $mpk = ekskulIo('MPK');
    $osis = ekskulIo('OSIS', 'organisasi', ['induk_id' => $mpk->id]);
    $futsal = ekskulIo('Futsal', 'ekskul');
    $admin = adminIo();

    // ekskul biasa tidak bisa jadi induk
    $this->actingAs($admin)->put(route('ekstrakurikuler.update', $osis->id), formIo($osis, ['induk_id' => $futsal->id]))
        ->assertSessionHasErrors('induk_id');

    // organisasi tidak bisa jadi induk dirinya sendiri
    $this->actingAs($admin)->put(route('ekstrakurikuler.update', $mpk->id), formIo($mpk, ['induk_id' => $mpk->id]))
        ->assertSessionHasErrors('induk_id');

    // MPK di bawah OSIS padahal OSIS sudah di bawah MPK = lingkaran
    $this->actingAs($admin)->put(route('ekstrakurikuler.update', $mpk->id), formIo($mpk, ['induk_id' => $osis->id]))
        ->assertSessionHasErrors('induk_id');

    expect($mpk->fresh()->induk_id)->toBeNull()->and($osis->fresh()->induk_id)->toBe($mpk->id);
});

test('induk yang tidak ada ditolak', function () {
    $osis = ekskulIo('OSIS');

    $this->actingAs(adminIo())->put(route('ekstrakurikuler.update', $osis->id), formIo($osis, ['induk_id' => 99999]))
        ->assertSessionHasErrors('induk_id');
});

test('mengganti tipe organisasi menjadi bukan organisasi otomatis melepas induknya', function () {
    $mpk = ekskulIo('MPK');
    $osis = ekskulIo('OSIS', 'organisasi', ['induk_id' => $mpk->id]);
    $biasa = JenisEkskul::where('kode', 'ekskul')->value('id');

    $this->actingAs(adminIo())->put(route('ekstrakurikuler.update', $osis->id), formIo($osis, ['jenis_ekskul_id' => $biasa, 'induk_id' => $mpk->id]))
        ->assertSessionHasNoErrors();

    expect($osis->fresh()->induk_id)->toBeNull();
});

test('induk hanya bisa diatur lewat model untuk organisasi: ekskul biasa selalu tanpa induk', function () {
    $mpk = ekskulIo('MPK');
    $futsal = ekskulIo('Futsal', 'ekskul', ['induk_id' => $mpk->id]);

    expect($futsal->fresh()->induk_id)->toBeNull();
});

test('menghapus organisasi induk melepas induk bawahannya, bukan menghapus bawahannya', function () {
    $mpk = ekskulIo('MPK');
    $osis = ekskulIo('OSIS', 'organisasi', ['induk_id' => $mpk->id]);

    $mpk->delete();

    expect($osis->fresh())->not->toBeNull()->and($osis->fresh()->induk_id)->toBeNull();
});

test('halaman ekskul dan katalog menampilkan hierarkinya', function () {
    $mpk = ekskulIo('MPK');
    $osis = ekskulIo('OSIS', 'organisasi', ['induk_id' => $mpk->id]);
    $admin = adminIo();

    $this->actingAs($admin)->get(route('ekstrakurikuler.show', $osis->id))->assertOk()->assertSee('Di bawah MPK');
    $this->actingAs($admin)->get(route('ekstrakurikuler.show', $mpk->id))->assertOk()->assertSee('Membawahi OSIS');
    $this->actingAs($admin)->get(route('ekstrakurikuler.index'))->assertOk()->assertSee('Di bawah MPK');
});

test('form edit menawarkan organisasi lain sebagai induk (bukan dirinya sendiri dan bukan ekskul biasa)', function () {
    $mpk = ekskulIo('MPK');
    $osis = ekskulIo('OSIS');
    ekskulIo('Futsal Garuda', 'ekskul');

    $html = $this->actingAs(adminIo())->get(route('ekstrakurikuler.edit', $osis->id))->assertOk()->getContent();

    expect($html)->toContain('name="induk_id"')
        ->and($html)->toMatch('/<option value="'.$mpk->id.'"[^>]*>MPK<\/option>/')
        ->and($html)->not->toMatch('/<option value="'.$osis->id.'"[^>]*>OSIS<\/option>/')
        ->and($html)->not->toMatch('/<option value="\d+"[^>]*>Futsal Garuda<\/option>/');

    // form tambah ekskul juga punya pilihannya
    $this->actingAs(adminIo())->get(route('ekstrakurikuler.create'))->assertOk()->assertSee('name="induk_id"', false);
});
