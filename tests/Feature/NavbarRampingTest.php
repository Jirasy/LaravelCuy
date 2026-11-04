<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunNav(string $role): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

test('navbar admin tidak lagi punya tab Import Siswa; tombolnya ada di Kelola User', function () {
    $admin = akunNav('admin');
    $import = route('import-siswa.create');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertDontSee($import, false)
        ->assertSee(route('users.index'), false);

    $this->actingAs($admin)->get(route('users.index'))
        ->assertOk()
        ->assertSee($import, false);
});

test('pembina juga mencapai Import Siswa lewat Kelola User, bukan lewat navbar', function () {
    $pembina = akunNav('pembina');
    $import = route('import-siswa.create');

    $this->actingAs($pembina)->get(route('ekstrakurikuler.index'))->assertOk()->assertDontSee($import, false);
    $this->actingAs($pembina)->get(route('users.index'))->assertOk()->assertSee($import, false);
    $this->actingAs($pembina)->get($import)->assertOk();
});

test('navbar admin punya satu menu Tipe & Kategori, bukan dua', function () {
    $admin = akunNav('admin');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee(route('jenis-ekskul.index'), false)
        ->assertSee('Tipe &amp; Kategori', false)
        ->assertDontSee(route('kategori-ekskul.index'), false);
});

test('halaman Tipe dan Kategori saling terhubung lewat tab', function () {
    $admin = akunNav('admin');

    $this->actingAs($admin)->get(route('jenis-ekskul.index'))
        ->assertOk()
        ->assertSee(route('kategori-ekskul.index'), false)
        ->assertSee('aria-selected="true"', false);

    $this->actingAs($admin)->get(route('kategori-ekskul.index'))
        ->assertOk()
        ->assertSee(route('jenis-ekskul.index'), false)
        ->assertSee('aria-selected="true"', false);
});

test('pembina dan siswa tidak melihat menu Tipe & Kategori', function () {
    foreach (['pembina', 'siswa'] as $role) {
        $this->actingAs(akunNav($role))->get(route('ekstrakurikuler.index'))
            ->assertOk()
            ->assertDontSee(route('jenis-ekskul.index'), false);
    }
});
