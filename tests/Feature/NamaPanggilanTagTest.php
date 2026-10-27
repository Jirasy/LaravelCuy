<?php

use App\Models\ProfilTag;
use App\Models\User;
use App\Models\UserPanggilan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function penggunaProfil(string $role = 'siswa', ?string $nama = null): User
{
    return User::factory()->create(array_filter([
        'name' => $nama,
        'role' => $role,
        'is_active' => true,
    ]));
}

function tagProfil(User $pemilik, ?User $pemberi, string $teks, string $status = 'privat'): ProfilTag
{
    return ProfilTag::create([
        'user_id' => $pemilik->id,
        'pemberi_id' => $pemberi?->id,
        'teks' => $teks,
        'status' => $status,
    ]);
}

// ---------------------------------------------------------------------------
// Nama panggilan
// ---------------------------------------------------------------------------

test('sapaan memakai nama depan kalau belum punya nama panggilan', function () {
    $user = penggunaProfil('siswa', 'Budi Santoso');

    expect($user->sapaan)->toBe('Budi');
});

test('panggilan pertama otomatis jadi sapaan utama dan dipakai di dashboard', function () {
    $user = penggunaProfil('siswa', 'Budi Santoso');

    $this->actingAs($user)->post(route('profil.panggilan.store'), ['nama' => 'Kiki'])
        ->assertRedirect(route('profil.edit'));
    $this->actingAs($user)->post(route('profil.panggilan.store'), ['nama' => 'Bud'])
        ->assertRedirect(route('profil.edit'));

    expect($user->panggilan()->where('utama', true)->pluck('nama')->all())->toBe(['Kiki'])
        ->and($user->fresh()->sapaan)->toBe('Kiki');

    $this->actingAs($user)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('data-nama="Kiki"', false);
});

test('hanya satu nama panggilan yang jadi sapaan utama', function () {
    $user = penggunaProfil();
    $kiki = UserPanggilan::create(['user_id' => $user->id, 'nama' => 'Kiki', 'utama' => true]);
    $bud = UserPanggilan::create(['user_id' => $user->id, 'nama' => 'Bud', 'utama' => false]);

    $this->actingAs($user)->patch(route('profil.panggilan.utama', $bud->id))
        ->assertRedirect(route('profil.edit'));

    expect($kiki->fresh()->utama)->toBeFalse()
        ->and($bud->fresh()->utama)->toBeTrue();
});

test('menghapus panggilan utama mengembalikan sapaan ke nama depan', function () {
    $user = penggunaProfil('siswa', 'Budi Santoso');
    $kiki = UserPanggilan::create(['user_id' => $user->id, 'nama' => 'Kiki', 'utama' => true]);

    $this->actingAs($user)->delete(route('profil.panggilan.destroy', $kiki->id))
        ->assertRedirect(route('profil.edit'));

    expect($user->fresh()->sapaan)->toBe('Budi');
});

test('nama panggilan dibatasi jumlahnya dan tidak boleh kembar', function () {
    $user = penggunaProfil();
    foreach (['Aa', 'Bb', 'Cc', 'Dd', 'Ee'] as $nama) {
        UserPanggilan::create(['user_id' => $user->id, 'nama' => $nama]);
    }

    $this->actingAs($user)->post(route('profil.panggilan.store'), ['nama' => 'Baru'])
        ->assertSessionHasErrors('nama');
    expect($user->panggilan()->count())->toBe(5);

    $lain = penggunaProfil();
    UserPanggilan::create(['user_id' => $lain->id, 'nama' => 'Kiki', 'utama' => true]);

    $this->actingAs($lain)->post(route('profil.panggilan.store'), ['nama' => 'kiki'])
        ->assertSessionHasErrors('nama');
    expect($lain->panggilan()->count())->toBe(1);
});

test('panggilan milik orang lain tidak bisa diubah atau dihapus', function () {
    $pemilik = penggunaProfil();
    $penyusup = penggunaProfil();
    $panggilan = UserPanggilan::create(['user_id' => $pemilik->id, 'nama' => 'Kiki', 'utama' => false]);

    $this->actingAs($penyusup)->patch(route('profil.panggilan.utama', $panggilan->id))->assertNotFound();
    $this->actingAs($penyusup)->delete(route('profil.panggilan.destroy', $panggilan->id))->assertNotFound();

    expect($panggilan->fresh())->not->toBeNull()
        ->and($panggilan->fresh()->utama)->toBeFalse();
});

// ---------------------------------------------------------------------------
// Tag profil
// ---------------------------------------------------------------------------

test('tag dari user lain privat sampai pemilik profil menampilkannya', function () {
    $pemilik = penggunaProfil('siswa', 'Pemilik Satu');
    $pemberi = penggunaProfil('siswa', 'Pemberi Rahasia');
    $orangKetiga = penggunaProfil('siswa', 'Orang Ketiga');

    $this->actingAs($pemberi)->post(route('profil.tag.store', $pemilik->id), ['teks' => 'pejuang subuh'])
        ->assertRedirect()
        ->assertSessionHas('success');

    $tag = ProfilTag::firstOrFail();
    expect($tag->status)->toBe('privat')
        ->and($tag->pemberi_id)->toEqual($pemberi->id);

    // Pemilik melihat tagnya, tapi tidak melihat siapa pemberinya
    $this->actingAs($pemilik)->get(route('profil.show', $pemilik->id))
        ->assertOk()
        ->assertSee('pejuang subuh')
        ->assertDontSee('Pemberi Rahasia');

    // Orang lain belum bisa melihatnya
    $this->actingAs($orangKetiga)->get(route('profil.show', $pemilik->id))
        ->assertOk()
        ->assertDontSee('pejuang subuh');

    // Pemilik memutuskan menampilkannya
    $this->actingAs($pemilik)->patch(route('profil.tag.update', $tag->id), ['status' => 'tampil'])
        ->assertRedirect();

    $this->actingAs($orangKetiga)->get(route('profil.show', $pemilik->id))
        ->assertOk()
        ->assertSee('pejuang subuh');
});

test('pemilik profil diberi notifikasi tanpa nama pemberi', function () {
    $pemilik = penggunaProfil();
    $pemberi = penggunaProfil('siswa', 'Pemberi Rahasia');

    $this->actingAs($pemberi)->post(route('profil.tag.store', $pemilik->id), ['teks' => 'jago futsal']);

    $notif = $pemilik->notifications()->first();
    expect($notif)->not->toBeNull()
        ->and($notif->data['judul'])->toBe('Tag baru di profilmu')
        ->and($notif->data['pesan'])->not->toContain('Pemberi Rahasia');
});

test('tidak bisa memberi tag ke profil sendiri atau akun nonaktif', function () {
    $user = penggunaProfil();
    $nonaktif = User::factory()->create(['role' => 'siswa', 'is_active' => false]);

    $this->actingAs($user)->post(route('profil.tag.store', $user->id), ['teks' => 'keren'])->assertForbidden();
    $this->actingAs($user)->post(route('profil.tag.store', $nonaktif->id), ['teks' => 'keren'])->assertForbidden();

    expect(ProfilTag::count())->toBe(0);
});

test('teks tag tidak boleh lebih dari 30 karakter', function () {
    $pemilik = penggunaProfil();
    $pemberi = penggunaProfil();

    $this->actingAs($pemberi)->post(route('profil.tag.store', $pemilik->id), ['teks' => str_repeat('a', 31)])
        ->assertSessionHasErrors('teks');

    expect(ProfilTag::count())->toBe(0);
});

test('satu user hanya bisa memberi 1 tag ke tiap user lain, tapi boleh men-tag banyak user', function () {
    $a = penggunaProfil('siswa', 'Si A');
    $b = penggunaProfil();
    $c = penggunaProfil();
    $d = penggunaProfil();

    // A men-tag B, C, dan D masing-masing satu kali
    $this->actingAs($a)->post(route('profil.tag.store', $b->id), ['teks' => 'si aktif']);
    $this->actingAs($a)->post(route('profil.tag.store', $c->id), ['teks' => 'si malas']);
    $this->actingAs($a)->post(route('profil.tag.store', $d->id), ['teks' => 'si pintar']);
    expect(ProfilTag::where('pemberi_id', $a->id)->count())->toBe(3);

    // Tag kedua ke orang yang sama ditolak, apa pun teksnya
    $this->actingAs($a)->post(route('profil.tag.store', $b->id), ['teks' => 'si rajin'])->assertSessionHasErrors('teks');
    $this->actingAs($a)->post(route('profil.tag.store', $b->id), ['teks' => 'SI AKTIF'])->assertSessionHasErrors('teks');
    expect(ProfilTag::where('user_id', $b->id)->count())->toBe(1);

    // User lain tetap boleh men-tag B dengan teks yang sama
    $this->actingAs($c)->post(route('profil.tag.store', $b->id), ['teks' => 'si aktif']);
    expect(ProfilTag::where('user_id', $b->id)->count())->toBe(2);
});

test('mengganti tag: tarik tag lama dulu, lalu beri yang baru', function () {
    $a = penggunaProfil();
    $b = penggunaProfil();

    $this->actingAs($a)->post(route('profil.tag.store', $b->id), ['teks' => 'si aktif']);
    $lama = ProfilTag::firstOrFail();

    $this->actingAs($a)->delete(route('profil.tag.destroy', $lama->id))->assertRedirect();
    $this->actingAs($a)->post(route('profil.tag.store', $b->id), ['teks' => 'si rajin']);

    expect(ProfilTag::where('user_id', $b->id)->pluck('teks')->all())->toBe(['si rajin']);
});

test('form beri tag disembunyikan setelah user memberi tag ke profil itu', function () {
    $a = penggunaProfil();
    $b = penggunaProfil('siswa', 'Si Bebek');

    $this->actingAs($a)->get(route('profil.show', $b->id))->assertOk()->assertSee('Beri tag');
    $this->actingAs($a)->post(route('profil.tag.store', $b->id), ['teks' => 'si aktif']);

    $this->actingAs($a)->get(route('profil.show', $b->id))
        ->assertOk()
        ->assertSee('Tarik tag lamamu dulu')
        ->assertDontSee('placeholder="Beri tag', false);
});

test('hanya pemilik, pemberi, dan moderator yang bisa mengelola tag', function () {
    $pemilik = penggunaProfil();
    $pemberi = penggunaProfil();
    $orangLain = penggunaProfil();
    $tag = tagProfil($pemilik, $pemberi, 'rajin');

    // Orang lain tidak boleh apa-apa
    $this->actingAs($orangLain)->patch(route('profil.tag.update', $tag->id), ['status' => 'tampil'])->assertForbidden();
    $this->actingAs($orangLain)->delete(route('profil.tag.destroy', $tag->id))->assertForbidden();

    // Pemberi hanya bisa menarik, tidak bisa menampilkan
    $this->actingAs($pemberi)->patch(route('profil.tag.update', $tag->id), ['status' => 'tampil'])->assertForbidden();

    // Pemilik tidak bisa mengubah teks (kata-kata orang lain)
    $this->actingAs($pemilik)->patch(route('profil.tag.update', $tag->id), ['teks' => 'diubah'])->assertForbidden();
    expect($tag->fresh()->teks)->toBe('rajin');

    // Pemberi menarik tagnya
    $this->actingAs($pemberi)->delete(route('profil.tag.destroy', $tag->id))->assertRedirect();
    expect(ProfilTag::count())->toBe(0);

    // Pemilik menghapus tag di profilnya
    $tag2 = tagProfil($pemilik, $pemberi, 'ramah');
    $this->actingAs($pemilik)->delete(route('profil.tag.destroy', $tag2->id))->assertRedirect();
    expect(ProfilTag::count())->toBe(0);
});

test('admin dan pembina melihat pemberi serta bisa memoderasi tag siswa', function () {
    $admin = penggunaProfil('admin');
    $pembina = penggunaProfil('pembina');
    $siswa = penggunaProfil();
    $pemberi = penggunaProfil('siswa', 'Pemberi Terlihat');
    $tag = tagProfil($siswa, $pemberi, 'tag lama');

    $this->actingAs($admin)->get(route('profil.show', $siswa->id))
        ->assertOk()
        ->assertSee('tag lama')
        ->assertSee('Pemberi Terlihat');

    $this->actingAs($pembina)->patch(route('profil.tag.update', $tag->id), ['teks' => 'tag baru'])->assertRedirect();
    expect($tag->fresh()->teks)->toBe('tag baru');

    $this->actingAs($admin)->delete(route('profil.tag.destroy', $tag->id))->assertRedirect();
    expect(ProfilTag::count())->toBe(0);
});

test('pembina tidak bisa memoderasi tag di profil admin', function () {
    $admin = penggunaProfil('admin');
    $pembina = penggunaProfil('pembina');
    $pemberi = penggunaProfil();
    $tag = tagProfil($admin, $pemberi, 'tag admin');

    $this->actingAs($pembina)->patch(route('profil.tag.update', $tag->id), ['teks' => 'diubah'])->assertForbidden();
    $this->actingAs($pembina)->delete(route('profil.tag.destroy', $tag->id))->assertForbidden();

    $this->actingAs($admin)->delete(route('profil.tag.destroy', $tag->id))->assertRedirect();
    expect(ProfilTag::count())->toBe(0);
});
