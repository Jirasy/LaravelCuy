<?php

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunDt(string $role = 'siswa', array $isian = []): User
{
    return User::factory()->create(array_merge(['role' => $role, 'is_active' => true], $isian));
}

function ekskulDt(): Ekstrakurikuler
{
    return Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal Garuda', 'pembina' => 'Pak Uji', 'status' => 'aktif']);
}

// Anggota aktif dengan salinan data akun saat mendaftar (persis seperti pendaftaran sungguhan)
function anggotaDt(Ekstrakurikuler $ekskul, User $siswa): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $siswa->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa',
        'kelas' => $siswa->kelas,
        'nisn' => $siswa->nisn,
        'alasan_mendaftar' => 'Ingin ikut.',
        'status' => 'diterima',
        'status_anggota' => 'aktif',
        'tahun_mulai' => 2026,
    ]);
}

function tabAnggotaDt(Ekstrakurikuler $ekskul): string
{
    return route('ekstrakurikuler.show', [$ekskul->id, 'tab' => 'anggota']);
}

test('halaman Anggota menampilkan nama, kelas, dan NISN terbaru dari akun, bukan salinan saat mendaftar', function () {
    $ekskul = ekskulDt();
    $siswa = akunDt('siswa', ['name' => 'Nama Lama Siswa', 'kelas' => 'X', 'nisn' => '1111111111']);
    anggotaDt($ekskul, $siswa);

    // Ubah akun lewat query langsung (tanpa event model) supaya salinan di tabel pendaftaran sengaja tertinggal
    DB::table('users')->where('id', $siswa->id)->update(['name' => 'Nama Baru Siswa', 'kelas' => 'XI PPLG', 'nisn' => '2222222222']);

    expect(PendaftaranEkskul::first()->nama_pendaftar)->toBe('Nama Lama Siswa');

    $this->actingAs(akunDt('admin'))->get(tabAnggotaDt($ekskul))
        ->assertOk()
        ->assertSee('Nama Baru Siswa')
        ->assertSee('XI PPLG')
        ->assertSee('2222222222')
        ->assertDontSee('Nama Lama Siswa')
        ->assertDontSee('1111111111');
});

test('mengubah akun menyalin nama, kelas, dan NISN ke data pendaftarannya', function () {
    $ekskul = ekskulDt();
    $siswa = akunDt('siswa', ['name' => 'Nama Lama', 'kelas' => 'X', 'nisn' => null]);
    $baris = anggotaDt($ekskul, $siswa);

    $siswa->update(['name' => 'Nama Baru', 'kelas' => 'XI', 'nisn' => '3333333333']);

    $baris->refresh();

    expect($baris->nama_pendaftar)->toBe('Nama Baru')
        ->and($baris->kelas)->toBe('XI')
        ->and($baris->nisn)->toBe('3333333333');
});

test('hanya perubahan yang disalin, dan pendaftaran siswa lain tidak ikut berubah', function () {
    $ekskul = ekskulDt();
    $siswa = akunDt('siswa', ['name' => 'Siswa Satu', 'kelas' => 'X']);
    $lain = akunDt('siswa', ['name' => 'Siswa Dua', 'kelas' => 'XI']);
    $barisSatu = anggotaDt($ekskul, $siswa);
    $barisLain = anggotaDt($ekskul, $lain);

    $siswa->update(['name' => 'Siswa Satu Baru']);

    expect($barisSatu->fresh()->nama_pendaftar)->toBe('Siswa Satu Baru')
        ->and($barisSatu->fresh()->kelas)->toBe('X')
        ->and($barisLain->fresh()->nama_pendaftar)->toBe('Siswa Dua')
        ->and($barisLain->fresh()->kelas)->toBe('XI');
});

test('kelas atau NISN yang dikosongkan di akun tidak menimpa salinan yang terisi, sedangkan nama selalu mengikuti', function () {
    $ekskul = ekskulDt();
    $siswa = akunDt('siswa', ['name' => 'Siswa Uji', 'kelas' => 'X', 'nisn' => null]);
    $baris = anggotaDt($ekskul, $siswa);

    // NISN diketik siswa saat mendaftar, padahal akunnya belum punya
    $baris->update(['nisn' => '4444444444']);

    $siswa->update(['kelas' => null, 'name' => 'Siswa Uji Baru']);

    $baris->refresh();

    expect($baris->nama_pendaftar)->toBe('Siswa Uji Baru')
        ->and($baris->kelas)->toBe('X')
        ->and($baris->nisn)->toBe('4444444444');

    // di halaman, akun yang kosong memakai salinan sebagai cadangan
    $this->actingAs(akunDt('admin'))->get(tabAnggotaDt($ekskul))
        ->assertOk()->assertSee('Siswa Uji Baru')->assertSee('4444444444');
});

test('pendaftaran tanpa akun (data manual lama) tetap tampil dari salinannya', function () {
    $ekskul = ekskulDt();

    PendaftaranEkskul::create([
        'user_id' => null,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => 'Anggota Data Manual',
        'peran' => 'siswa',
        'kelas' => 'XII',
        'status' => 'diterima',
        'status_anggota' => 'aktif',
        'tahun_mulai' => 2025,
    ]);

    $this->actingAs(akunDt('admin'))->get(tabAnggotaDt($ekskul))
        ->assertOk()->assertSee('Anggota Data Manual')->assertSee('XII');
});

test('daftar anggota diurutkan berdasarkan nama terbaru', function () {
    $ekskul = ekskulDt();
    $a = akunDt('siswa', ['name' => 'Andi Awal']);
    $b = akunDt('siswa', ['name' => 'Budi Tengah']);
    anggotaDt($ekskul, $a);
    anggotaDt($ekskul, $b);

    // Andi diganti jadi "Zaki Akhir": urutannya harus pindah ke belakang Budi
    DB::table('users')->where('id', $a->id)->update(['name' => 'Zaki Akhir']);

    $this->actingAs(akunDt('admin'))->get(tabAnggotaDt($ekskul))
        ->assertOk()->assertSeeInOrder(['Budi Tengah', 'Zaki Akhir']);
});

test('nama_tampil memakai akun kalau relasinya dimuat, dan salinan kalau tidak', function () {
    $siswa = akunDt('siswa', ['name' => 'Nama Akun', 'kelas' => 'XI', 'nisn' => '5555555555']);
    $baris = new PendaftaranEkskul(['nama_pendaftar' => 'Nama Salinan', 'kelas' => 'X', 'nisn' => '9999999999']);

    // belum dimuat: salinan dipakai (tanpa query tambahan)
    expect($baris->nama_tampil)->toBe('Nama Salinan')
        ->and($baris->kelas_tampil)->toBe('X')
        ->and($baris->nisn_tampil)->toBe('9999999999');

    $baris->setRelation('user', $siswa);

    expect($baris->nama_tampil)->toBe('Nama Akun')
        ->and($baris->kelas_tampil)->toBe('XI')
        ->and($baris->nisn_tampil)->toBe('5555555555');
});
