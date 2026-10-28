<?php

use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunXl(string $role = 'siswa', ?string $nama = null): User
{
    return User::factory()->create(array_filter(['name' => $nama, 'role' => $role, 'is_active' => true]));
}

function ekskulDenganAnggotaXl(string $namaAnggota = 'Andi Pratama'): Ekstrakurikuler
{
    $ekskul = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'Pak Budi', 'status' => 'aktif']);
    $siswa = akunXl('siswa', $namaAnggota);

    PendaftaranEkskul::create([
        'user_id' => $siswa->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa',
        'status' => 'diterima',
        'status_anggota' => 'aktif',
    ]);

    return $ekskul;
}

// Ketiga jenis laporan memakai partial berbeda di view Excel, jadi masing-masing diuji.
test('ekspor Excel semua jenis laporan menghasilkan unduhan, bukan error', function (string $jenis) {
    $ekskul = ekskulDenganAnggotaXl();

    $this->actingAs(akunXl('admin'))->get(route('laporan.unduh', [$ekskul, $jenis, 'excel']))
        ->assertOk()
        ->assertHeader('content-disposition');
})->with(['anggota', 'absensi', 'nilai'])
    ->skip(! class_exists(\Maatwebsite\Excel\Facades\Excel::class), 'maatwebsite/excel belum terpasang');

test('berkas Excel rekap anggota berisi judul dan nama anggota aktif', function () {
    $ekskul = ekskulDenganAnggotaXl('Andi Pratama');

    $respons = $this->actingAs(akunXl('admin'))->get(route('laporan.unduh', [$ekskul, 'anggota', 'excel']))->assertOk();

    $sel = collect(\PhpOffice\PhpSpreadsheet\IOFactory::load($respons->baseResponse->getFile()->getPathname())
        ->getActiveSheet()->toArray())->flatten()->filter()->all();

    expect($sel)->toContain('Rekap Anggota Ekskul')->toContain('Andi Pratama');
})->skip(! class_exists(\Maatwebsite\Excel\Facades\Excel::class), 'maatwebsite/excel belum terpasang');
