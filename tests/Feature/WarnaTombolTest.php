<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('komponen x-tombol memakai warna sesuai fungsinya', function (string $jenis, string $warna) {
    $this->blade("<x-tombol jenis=\"{$jenis}\" href=\"/uji\">Aksi</x-tombol>")
        ->assertSee("bg-{$warna}-", false)
        ->assertSee('<a href="/uji"', false);
})->with([
    'edit' => ['edit', 'indigo'],
    'hapus' => ['hapus', 'red'],
    'detail' => ['detail', 'sky'],
    'sukses' => ['sukses', 'emerald'],
    'peringatan' => ['peringatan', 'orange'],
    'unduh' => ['unduh', 'teal'],
    'netral' => ['netral', 'gray'],
]);

test('x-tombol tanpa href menjadi tombol, dan jenis yang tidak dikenal memakai warna netral', function () {
    $this->blade('<x-tombol jenis="hapus" type="submit">Hapus</x-tombol>')->assertSee('<button type="submit"', false);
    $this->blade('<x-tombol jenis="asal">Aksi</x-tombol>')->assertSee('bg-gray-800', false);
});

test('tabel Kelola User memakai warna berbeda untuk Edit, Hapus, Riwayat, dan status', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Aktif Uji', 'is_active' => true]);
    User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Nonaktif Uji', 'is_active' => false]);

    $html = $this->actingAs($admin)->get(route('users.index'))->assertOk()->getContent();

    // Edit bernuansa indigo, Hapus merah, Riwayat sky, Nonaktifkan oranye, Aktifkan hijau
    expect($html)->toMatch('/bg-indigo-600[^"]*"[^>]*>\s*Edit/')
        ->and($html)->toMatch('/bg-red-600[^"]*"[^>]*>\s*Hapus/')
        ->and($html)->toMatch('/bg-sky-600[^"]*"[^>]*>\s*Riwayat/')
        ->and($html)->toMatch('/bg-orange-500[^"]*"[^>]*>\s*Nonaktifkan/')
        ->and($html)->toMatch('/bg-emerald-600[^"]*"[^>]*>\s*Aktifkan/');
});

test('tombol Edit/Perbarui tidak lagi memakai kuning atau amber di seluruh tampilan', function () {
    $pelanggar = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'))) as $berkas) {
        if (! str_ends_with($berkas->getFilename(), '.blade.php')) {
            continue;
        }

        $isi = file_get_contents($berkas->getPathname());

        // tombol solid (bg-yellow-4xx..7xx / bg-amber-4xx..7xx) berteks putih di atribut class yang sama
        if (preg_match('/class="[^"]*(bg-(yellow|amber)-[4-7]00)[^"]*text-white|class="[^"]*text-white[^"]*(bg-(yellow|amber)-[4-7]00)/', $isi)) {
            $pelanggar[] = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $berkas->getPathname());
        }
    }

    expect($pelanggar)->toBe([]);
});
