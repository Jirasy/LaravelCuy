<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('halaman masuk menampilkan form dengan field, tombol mata, dan tautan daftar', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="remember"', false)
        ->assertSee('data-eye="f-password"', false)
        ->assertSee('autocomplete="current-password"', false)
        ->assertSee(route('register'), false);
});

test('halaman daftar menampilkan semua field siswa dan syarat password', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Daftar Akun')
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="nisn"', false)
        ->assertSee('name="kelas"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('data-cek="len"', false)
        ->assertSee(route('login'), false);
});

test('login gagal menampilkan pesan dan menandai kedua isian, email tetap terisi', function () {
    User::factory()->create(['email' => 'siswa@contoh.test', 'is_active' => true]);

    $this->followingRedirects()
        ->from(route('login'))
        ->post(route('login.process'), ['email' => 'siswa@contoh.test', 'password' => 'salah-banget'])
        ->assertSee('Email atau password salah.')
        ->assertSee('value="siswa@contoh.test"', false)
        ->assertSee('role="alert"', false)
        ->assertSee('aria-invalid="true"', false);
});

test('daftar dengan isian kosong menandai field yang bermasalah', function () {
    $this->followingRedirects()
        ->from(route('register'))
        ->post(route('register.process'), ['name' => 'Budi', 'email' => 'bukan-email'])
        ->assertSee('role="alert"', false)
        ->assertSee('is-invalid', false)
        ->assertSee('value="Budi"', false);
});

test('pengguna yang sudah masuk diarahkan dari halaman masuk dan daftar', function () {
    $this->actingAs(User::factory()->create(['is_active' => true]));

    $this->get(route('login'))->assertRedirect(route('ekstrakurikuler.index'));
    $this->get(route('register'))->assertRedirect(route('ekstrakurikuler.index'));
});
