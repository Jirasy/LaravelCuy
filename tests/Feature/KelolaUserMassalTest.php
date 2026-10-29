<?php

use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunKm(string $role = 'siswa', array $atribut = []): User
{
    return User::factory()->create(array_merge(['role' => $role, 'is_active' => true], $atribut));
}

function massalKm($test, User $oleh, string $aksi, array $ids, array $tambahan = [])
{
    return $test->actingAs($oleh)->post(route('users.massal'), array_merge(['aksi' => $aksi, 'ids' => $ids], $tambahan));
}

test('hanya admin dan pembina yang bisa memakai aksi massal', function () {
    $korban = akunKm();

    $this->post(route('users.massal'), ['aksi' => 'hapus', 'ids' => [$korban->id]])->assertRedirect(route('login'));
    massalKm($this, akunKm('siswa'), 'hapus', [$korban->id])->assertForbidden();

    expect(User::find($korban->id))->not->toBeNull();
});

test('halaman kelola user menampilkan checkbox pilih banyak untuk admin dan pembina', function () {
    $siswa = akunKm();

    $this->actingAs(akunKm('admin'))->get(route('users.index'))
        ->assertOk()
        ->assertSee('id="form-massal"', false)
        ->assertSee('id="pilih-semua"', false)
        ->assertSee('name="ids[]" value="'.$siswa->id.'"', false)
        ->assertSee('id="role-massal"', false);

    // Pembina tidak boleh mengubah role, jadi kontrolnya tidak ditampilkan
    $this->actingAs(akunKm('pembina'))->get(route('users.index'))
        ->assertOk()
        ->assertSee('id="form-massal"', false)
        ->assertDontSee('id="role-massal"', false);
});

test('admin menonaktifkan lalu mengaktifkan banyak akun sekaligus', function () {
    $admin = akunKm('admin');
    $a = akunKm();
    $b = akunKm();
    $c = akunKm();

    massalKm($this, $admin, 'nonaktifkan', [$a->id, $b->id])
        ->assertRedirect()
        ->assertSessionHas('success', '2 akun berhasil dinonaktifkan.');

    expect($a->fresh()->is_active)->toBeFalse()
        ->and($b->fresh()->is_active)->toBeFalse()
        ->and($c->fresh()->is_active)->toBeTrue();

    massalKm($this, $admin, 'aktifkan', [$a->id, $b->id])
        ->assertSessionHas('success', '2 akun berhasil diaktifkan.');

    expect($a->fresh()->is_active)->toBeTrue()
        ->and($b->fresh()->is_active)->toBeTrue();
});

test('admin menghapus banyak akun sekaligus tetapi akunnya sendiri dilewati', function () {
    $admin = akunKm('admin');
    $a = akunKm();
    $b = akunKm();

    massalKm($this, $admin, 'hapus', [$a->id, $b->id, $admin->id])
        ->assertSessionHas('success', '2 akun berhasil dihapus.')
        ->assertSessionHas('error');

    expect(User::find($a->id))->toBeNull()
        ->and(User::find($b->id))->toBeNull()
        ->and(User::find($admin->id))->not->toBeNull();
});

test('pembina tidak bisa menyentuh akun admin lewat aksi massal tapi akun siswa tetap diproses', function () {
    $pembina = akunKm('pembina');
    $admin = akunKm('admin', ['name' => 'Admin Utama']);
    $siswa = akunKm();

    $respons = massalKm($this, $pembina, 'hapus', [$admin->id, $siswa->id]);

    $respons->assertSessionHas('success', '1 akun berhasil dihapus.');
    expect(session('error'))->toContain('Admin Utama')->toContain('tidak punya izin');

    expect(User::find($admin->id))->not->toBeNull()
        ->and(User::find($siswa->id))->toBeNull();

    $admin2 = akunKm('admin');
    massalKm($this, $pembina, 'nonaktifkan', [$admin2->id]);
    expect($admin2->fresh()->is_active)->toBeTrue();
});

test('admin mengubah role banyak akun dan pembina yang diturunkan dilepas dari ekskulnya', function () {
    $admin = akunKm('admin');
    $pembina = akunKm('pembina');
    $siswa = akunKm();

    $ekskul = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'Pak Budi', 'status' => 'aktif']);
    $penugasan = EkskulPembina::create(['ekstrakurikuler_id' => $ekskul->id, 'user_id' => $pembina->id, 'mulai' => now()->toDateString()]);

    massalKm($this, $admin, 'ubah_role', [$pembina->id, $siswa->id], ['role' => 'siswa'])
        ->assertSessionHas('success', 'Role 2 akun diubah menjadi siswa.');

    expect($pembina->fresh()->role)->toBe('siswa')
        ->and($siswa->fresh()->role)->toBe('siswa')
        ->and($penugasan->fresh()->selesai)->not->toBeNull();

    massalKm($this, $admin, 'ubah_role', [$siswa->id], ['role' => 'pembina']);
    expect($siswa->fresh()->role)->toBe('pembina');
});

test('admin tidak bisa mengubah role akunnya sendiri lewat aksi massal', function () {
    $admin = akunKm('admin');
    $siswa = akunKm();

    massalKm($this, $admin, 'ubah_role', [$admin->id, $siswa->id], ['role' => 'siswa'])
        ->assertSessionHas('error');

    expect($admin->fresh()->role)->toBe('admin')
        ->and($siswa->fresh()->role)->toBe('siswa');
});

test('pembina tidak bisa mengubah role siapa pun lewat aksi massal', function () {
    $pembina = akunKm('pembina');
    $siswa = akunKm();

    massalKm($this, $pembina, 'ubah_role', [$siswa->id], ['role' => 'admin'])
        ->assertSessionMissing('success')
        ->assertSessionHas('error');

    expect($siswa->fresh()->role)->toBe('siswa');
});

test('mengubah kelas banyak siswa sekaligus', function () {
    $pembina = akunKm('pembina');
    $a = akunKm('siswa', ['kelas' => 'X IPA 1']);
    $b = akunKm('siswa', ['kelas' => null]);

    massalKm($this, $pembina, 'ubah_kelas', [$a->id, $b->id], ['kelas' => '  XI IPA 2  '])
        ->assertSessionHas('success', 'Kelas 2 akun berhasil diperbarui.');

    expect($a->fresh()->kelas)->toBe('XI IPA 2')
        ->and($b->fresh()->kelas)->toBe('XI IPA 2');
});

test('input aksi massal yang tidak valid ditolak dan tidak mengubah data', function () {
    $admin = akunKm('admin');
    $siswa = akunKm();

    massalKm($this, $admin, 'hapus', [])->assertSessionHasErrors('ids');
    massalKm($this, $admin, 'meledakkan', [$siswa->id])->assertSessionHasErrors('aksi');
    massalKm($this, $admin, 'ubah_role', [$siswa->id])->assertSessionHasErrors('role');
    massalKm($this, $admin, 'ubah_role', [$siswa->id], ['role' => 'superadmin'])->assertSessionHasErrors('role');
    massalKm($this, $admin, 'ubah_kelas', [$siswa->id])->assertSessionHasErrors('kelas');
    massalKm($this, $admin, 'hapus', [$siswa->id, $siswa->id])->assertSessionHasErrors('ids.*');
    massalKm($this, $admin, 'hapus', range(1, 201))->assertSessionHasErrors('ids');

    expect(User::find($siswa->id))->not->toBeNull()
        ->and($siswa->fresh()->role)->toBe('siswa');
});

test('id yang tidak ada diabaikan tanpa error', function () {
    $admin = akunKm('admin');
    $siswa = akunKm();

    massalKm($this, $admin, 'nonaktifkan', [$siswa->id, 999999])
        ->assertSessionHas('success', '1 akun berhasil dinonaktifkan.');

    expect($siswa->fresh()->is_active)->toBeFalse();
});
