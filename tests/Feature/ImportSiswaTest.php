<?php

use App\Models\KredensialSementara;
use App\Models\User;
use App\Notifications\RekapImportSiswa;
use App\Services\ImportSiswaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunImp(string $role = 'admin'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function unggahImp($test, User $oleh, string $nama, string $isi)
{
    return $test->actingAs($oleh)->post(route('import-siswa.proses'), [
        'berkas' => UploadedFile::fake()->createWithContent($nama, $isi),
    ]);
}

function berkasDocxImp(array $baris): UploadedFile
{
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
        .'<w:p><w:r><w:t>Daftar Peserta</w:t></w:r></w:p><w:tbl>';

    foreach ($baris as $b) {
        $xml .= '<w:tr>';
        foreach ($b as $sel) {
            $xml .= '<w:tc><w:p><w:r><w:t>'.htmlspecialchars($sel).'</w:t></w:r></w:p></w:tc>';
        }
        $xml .= '</w:tr>';
    }

    $xml .= '</w:tbl></w:body></w:document>';

    $jalur = tempnam(sys_get_temp_dir(), 'docx');
    $zip = new ZipArchive;
    $zip->open($jalur, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('word/document.xml', $xml);
    $zip->close();

    return new UploadedFile($jalur, 'peserta.docx', null, null, true);
}

test('hanya admin dan pembina yang bisa membuka fitur import siswa', function () {
    $this->get(route('import-siswa.create'))->assertRedirect(route('login'));

    $this->actingAs(akunImp('siswa'))->get(route('import-siswa.create'))->assertForbidden();
    $this->actingAs(akunImp('siswa'))->post(route('import-siswa.proses'))->assertForbidden();

    $this->actingAs(akunImp('pembina'))->get(route('import-siswa.create'))->assertOk();
    $this->actingAs(akunImp('admin'))->get(route('import-siswa.create'))->assertOk();
});

test('csv dengan pemisah titik koma: kolom kelompok CAMP diabaikan, akun siswa dibuat dengan password acak', function () {
    $admin = akunImp();

    $isi = "No;Nama Siswa;Kelompok CAMP\n1;BUDI SANTOSO;Kelompok 1\n2;siti aminah;Kelompok 2\n3;Andi Wijaya;Kelompok 1\n";

    $respons = unggahImp($this, $admin, 'siswa.csv', $isi);

    $notif = $admin->notifications()->first();
    $respons->assertRedirect(route('import-siswa.hasil', $notif->id))
        ->assertSessionHas('success', 'Berhasil mengimpor 3 siswa baru.');

    expect(User::where('role', 'siswa')->orderBy('name')->pluck('name')->all())
        ->toBe(['Andi Wijaya', 'Budi Santoso', 'Siti Aminah']);

    $budi = User::where('name', 'Budi Santoso')->first();
    expect($budi->nisn)->toBeNull()
        ->and($budi->must_change_password)->toBeTrue()
        ->and($budi->email)->toBe('budi.santoso@siswa.local');

    $password = KredensialSementara::where('user_id', $budi->id)->first()->passwordAsli();
    expect(strlen($password))->toBeGreaterThanOrEqual(12)
        ->and(Hash::check($password, $budi->password))->toBeTrue();

    // Rekap masuk ke notifikasi pengunggah, tanpa memuat password
    expect($notif->type)->toBe(RekapImportSiswa::class)
        ->and($notif->data['pesan'])->toContain('Budi Santoso')
        ->and($notif->data['pesan'])->not->toContain($password);
});

test('beberapa kelompok berdampingan dibaca semua, judul kelompok (nama pembina) tidak dianggap siswa', function () {
    $isi = "\n"
        ."No;Kelompok 1 = Pak Andi, S.Pd.;Kelas;;No;Kelompok 2 = Bu Sari, S.Kom.;Kelas\n"
        ."1;Adilla Ufairah;X;;1;Adrian Shadra;X\n"
        ."2;Febry Intania;X;;2;Firliano Azzami;XI\n"
        ."3;Kiagus Ahmad;XI;;3;Batrisya Rahmalia ( X DKV );XI\n";

    unggahImp($this, akunImp(), 'kelompok.csv', $isi)
        ->assertSessionHas('success', 'Berhasil mengimpor 6 siswa baru.');

    expect(User::where('role', 'siswa')->pluck('name')->all())
        ->not->toContain('Pak Andi')
        ->toContain('Adrian Shadra', 'Batrisya Rahmalia')
        ->and(User::where('name', 'Firliano Azzami')->value('kelas'))->toBe('XI')
        ->and(User::where('name', 'Adilla Ufairah')->value('kelas'))->toBe('X');
});

test('txt satu nama per baris dengan penomoran dibaca benar', function () {
    $admin = akunImp('pembina');

    unggahImp($this, $admin, 'siswa.txt', "1. Rina Putri\n2) Dewi Lestari\n3 Agus Salim\n")
        ->assertSessionHas('success', 'Berhasil mengimpor 3 siswa baru.');

    expect(User::where('role', 'siswa')->orderBy('name')->pluck('name')->all())
        ->toBe(['Agus Salim', 'Dewi Lestari', 'Rina Putri']);
});

test('NISN dibaca bila ada, dan NISN yang sudah terdaftar dilewati', function () {
    $admin = akunImp();

    unggahImp($this, $admin, 'a.csv', "Nama,NISN,Kelas\nBudi Hartono,0012345678,X-1\nCitra Dewi,,X-2\n");

    expect(User::where('name', 'Budi Hartono')->value('nisn'))->toBe('0012345678')
        ->and(User::where('name', 'Citra Dewi')->value('nisn'))->toBeNull()
        ->and(User::where('name', 'Citra Dewi')->value('kelas'))->toBe('X-2');

    unggahImp($this, $admin, 'b.csv', "Nama,NISN\nOrang Lain,0012345678\n")
        ->assertSessionHas('dilewati', fn ($d) => $d[0]['alasan'] === 'NISN 0012345678 sudah terdaftar');

    expect(User::where('role', 'siswa')->count())->toBe(2);
});

test('mengunggah berkas yang sama dua kali tidak membuat akun ganda, kecuali diizinkan', function () {
    $admin = akunImp();
    $isi = "Nama\nBambang Pamungkas\nSusi Susanti\n";

    unggahImp($this, $admin, 'siswa.csv', $isi);
    expect(User::where('role', 'siswa')->count())->toBe(2);

    unggahImp($this, $admin, 'siswa.csv', $isi)
        ->assertSessionHas('error')
        ->assertSessionHas('dilewati', fn ($d) => count($d) === 2);
    expect(User::where('role', 'siswa')->count())->toBe(2);

    $this->actingAs($admin)->post(route('import-siswa.proses'), [
        'berkas' => UploadedFile::fake()->createWithContent('siswa.csv', $isi),
        'izinkan_nama_sama' => '1',
    ])->assertSessionHas('success');

    expect(User::where('role', 'siswa')->count())->toBe(4)
        ->and(User::where('email', 'bambang.pamungkas2@siswa.local')->exists())->toBeTrue();
});

test('xlsx dibaca lewat Laravel Excel dan kolom tak relevan diabaikan', function () {
    $sheet = new Spreadsheet;
    $sheet->getActiveSheet()->fromArray([
        ['No', 'Nama Lengkap', 'NISN', 'Kelompok CAMP'],
        [1, 'Dewi Sartika', 3001234567, 'A'],
        [2, 'Joko Susilo', null, 'B'],
    ], null, 'A1');

    $jalur = tempnam(sys_get_temp_dir(), 'xlsx');
    (new Xlsx($sheet))->save($jalur);

    $this->actingAs(akunImp())->post(route('import-siswa.proses'), [
        'berkas' => new UploadedFile($jalur, 'daftar.xlsx', null, null, true),
    ])->assertSessionHas('success', 'Berhasil mengimpor 2 siswa baru.');

    expect(User::where('name', 'Dewi Sartika')->value('nisn'))->toBe('3001234567')
        ->and(User::where('name', 'Joko Susilo')->value('nisn'))->toBeNull();
});

test('docx berisi tabel dibaca, paragraf judul diabaikan', function () {
    $berkas = berkasDocxImp([['No', 'Nama', 'Kelompok'], ['1', 'Hendra Gunawan', '3'], ['2', 'Maya Sari', '1']]);

    $this->actingAs(akunImp())->post(route('import-siswa.proses'), ['berkas' => $berkas])
        ->assertSessionHas('success', 'Berhasil mengimpor 2 siswa baru.');

    expect(User::where('role', 'siswa')->orderBy('name')->pluck('name')->all())->toBe(['Hendra Gunawan', 'Maya Sari']);
});

test('berkas tanpa nama atau berformat salah ditolak dan tidak membuat akun', function () {
    $admin = akunImp();

    unggahImp($this, $admin, 'kosong.csv', "123\n456\n")->assertSessionHasErrors('berkas');
    unggahImp($this, $admin, 'virus.exe', 'abc')->assertSessionHasErrors('berkas');

    expect(User::where('role', 'siswa')->count())->toBe(0)
        ->and($admin->notifications()->count())->toBe(0);
});

test('rekap kredensial hanya bisa dibuka pengunggah, password tampil sekali setelah tombol ditekan', function () {
    $admin = akunImp();
    $lain = akunImp('pembina');

    unggahImp($this, $admin, 'siswa.csv', "Nama\nTono Sugiarto\n");
    $notif = $admin->notifications()->first();
    $password = KredensialSementara::first()->passwordAsli();

    $this->actingAs($lain)->get(route('import-siswa.hasil', $notif->id))->assertNotFound();

    $this->actingAs($admin)->get(route('import-siswa.hasil', $notif->id))
        ->assertOk()->assertSee('Tono Sugiarto')->assertDontSee($password);

    $this->actingAs($admin)->post(route('import-siswa.tampilkan', $notif->id))
        ->assertRedirect(route('import-siswa.hasil', $notif->id));

    $this->actingAs($admin)->get(route('import-siswa.hasil', $notif->id))->assertSee($password);
    $this->actingAs($admin)->get(route('import-siswa.hasil', $notif->id))->assertDontSee($password);

    $csv = $this->actingAs($admin)->post(route('import-siswa.unduh', $notif->id));
    $csv->assertOk();
    expect($csv->streamedContent())->toContain('tono.sugiarto@siswa.local')->toContain($password);
});

test('siswa hasil import bisa login dan wajib mengganti password sementara', function () {
    $admin = akunImp();

    app(ImportSiswaService::class)->impor(
        [['nama' => 'Wati Kusuma', 'nisn' => null, 'kelas' => null, 'email' => null]],
        $admin,
        'siswa.csv',
    );

    $siswa = User::where('name', 'Wati Kusuma')->firstOrFail();
    $password = KredensialSementara::where('user_id', $siswa->id)->firstOrFail()->passwordAsli();

    $this->post(route('login.process'), ['email' => $siswa->email, 'password' => $password])->assertRedirect();
    $this->assertAuthenticatedAs($siswa);

    $this->get(route('ekstrakurikuler.index'))->assertRedirect(route('pengaturan.edit'));
});
