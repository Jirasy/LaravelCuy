<?php

use App\Models\CasisPendaftaran;
use App\Models\CasisReview;
use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\SeleksiTahap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunSb(string $role = 'siswa', ?string $nama = null): User
{
    return User::factory()->create(array_filter(['name' => $nama, 'role' => $role, 'is_active' => true], fn ($v) => $v !== null));
}

function ekskulSb(string $nama = 'Paskibra Garuda', string $tipe = 'seleksi'): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => JenisEkskul::where('kode', $tipe)->value('id'),
    ]);
}

function pembinaSb(Ekstrakurikuler $ekskul): User
{
    $pembina = akunSb('pembina');
    EkskulPembina::create(['ekstrakurikuler_id' => $ekskul->id, 'user_id' => $pembina->id, 'mulai' => now()->toDateString()]);

    return $pembina;
}

function periodeSb(Ekstrakurikuler $ekskul, string $status = 'seleksi', string $nama = '2026/2027'): OsisPeriode
{
    return OsisPeriode::create(['ekstrakurikuler_id' => $ekskul->id, 'nama' => $nama, 'status' => $status]);
}

function tahapSb(OsisPeriode $periode, int $urutan, string $nama): SeleksiTahap
{
    return SeleksiTahap::create(['osis_periode_id' => $periode->id, 'urutan' => $urutan, 'nama' => $nama]);
}

function pendaftarSb(OsisPeriode $periode, ?User $siswa = null, string $status = 'diajukan'): CasisPendaftaran
{
    return CasisPendaftaran::create([
        'osis_periode_id' => $periode->id,
        'user_id' => ($siswa ?? akunSb('siswa'))->id,
        'motivasi' => str_repeat('Saya ingin ikut berkontribusi. ', 3),
        'status' => $status,
    ]);
}

function nilaiSb($uji, User $penilai, CasisPendaftaran $casis, int $skor = 80)
{
    return $uji->actingAs($penilai)->post(route('casis.review', $casis->id), ['skor' => $skor, 'rekomendasi' => 'lolos']);
}

function judulNotifSb(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

// ---------------------------------------------------------------------------
// Periode & jumlah tahap bebas
// ---------------------------------------------------------------------------

test('periode dibuat untuk ekskul berseleksi mana pun dan langsung punya tahap 1; ekskul biasa tidak bisa', function () {
    $ekskul = ekskulSb();
    $biasa = ekskulSb('Futsal', 'ekskul');
    $admin = akunSb('admin');

    $this->actingAs($admin)->post(route('periode.store', $ekskul->id), ['nama' => '2026/2027'])->assertSessionHas('success');

    $periode = OsisPeriode::firstOrFail();

    expect($periode->ekstrakurikuler_id)->toBe($ekskul->id)
        ->and($periode->status)->toBe('rekrutmen')
        ->and($periode->tahaps()->count())->toBe(1)
        ->and($periode->tahaps()->first()->nama)->toBe('Seleksi')
        ->and($periode->tahaps()->first()->urutan)->toBe(1);

    $this->actingAs($admin)->post(route('periode.store', $biasa->id), ['nama' => '2026/2027'])->assertNotFound();
});

test('siswa mendaftar lewat jalur seleksi ekskulnya sendiri, tidak lewat ekskul biasa', function () {
    $ekskul = ekskulSb();
    $biasa = ekskulSb('Futsal', 'ekskul');
    $periode = periodeSb($ekskul, 'rekrutmen');
    $siswa = akunSb('siswa');

    $this->actingAs($siswa)->get(route('casis.create', $ekskul->id))->assertOk()->assertSee('Calon Anggota');
    $this->actingAs($siswa)->get(route('casis.create', $biasa->id))->assertNotFound();

    $this->actingAs($siswa)->post(route('casis.simpan', $ekskul->id), [
        'aksi' => 'ajukan',
        'motivasi' => str_repeat('Saya ingin belajar dan berlatih bersama. ', 2),
    ])->assertSessionHas('success');

    $casis = CasisPendaftaran::firstOrFail();

    expect($casis->osis_periode_id)->toBe($periode->id)->and($casis->status)->toBe('diajukan');
});

test('admin menambah tahap sebanyak yang dibutuhkan, dan pendaftar yang masih diproses diberi tahu', function () {
    $ekskul = ekskulSb();
    $periode = periodeSb($ekskul);
    $pendaftar = pendaftarSb($periode);
    $admin = akunSb('admin');

    $this->actingAs($admin)->post(route('tahap.store', $periode->id), [
        'nama' => 'Wawancara',
        'mulai_pada' => '2026-11-10 08:00',
        'selesai_pada' => '2026-11-10 10:00',
        'tempat' => 'Aula',
        'keterangan' => 'Bawa seragam lengkap.',
    ])->assertSessionHas('success');

    $this->actingAs($admin)->post(route('tahap.store', $periode->id), ['nama' => 'Praktik'])->assertSessionHas('success');
    $this->actingAs($admin)->post(route('tahap.store', $periode->id), ['nama' => 'Tes kesehatan'])->assertSessionHas('success');

    $tahaps = $periode->tahaps()->get();
    $wawancara = $tahaps->firstWhere('nama', 'Wawancara');

    // tahap 1 otomatis + 3 tahap baru = 4, urutan berurutan
    expect($tahaps->pluck('urutan')->all())->toBe([1, 2, 3, 4])
        ->and($wawancara->tempat)->toBe('Aula')
        ->and($wawancara->mulai_pada->format('d-m-Y H:i'))->toBe('10-11-2026 08:00')
        ->and($wawancara->waktu_teks)->toBe('10-11-2026 08:00 - 10:00')
        ->and(judulNotifSb($pendaftar->user))->toContain('Ada tahap seleksi baru');
});

test('admin, pembina, dan senior boleh mengatur tahap; siswa biasa dan pembina ekskul lain tidak', function () {
    $ekskul = ekskulSb();
    $periode = periodeSb($ekskul);
    $pembina = pembinaSb($ekskul);
    $senior = akunSb('siswa', 'Kakak Senior');
    $periode->seniors()->attach($senior->id);

    foreach ([$pembina, $senior] as $pengatur) {
        $this->actingAs($pengatur)->post(route('tahap.store', $periode->id), ['nama' => 'Tahap oleh '.$pengatur->id])->assertSessionHas('success');
    }

    $this->actingAs(akunSb('siswa'))->post(route('tahap.store', $periode->id), ['nama' => 'Iseng'])->assertForbidden();
    $this->actingAs(pembinaSb(ekskulSb('Ekskul Lain')))->post(route('tahap.store', $periode->id), ['nama' => 'Iseng'])->assertForbidden();

    expect($periode->tahaps()->count())->toBe(3);
});

test('jadwal tahap divalidasi: waktu selesai wajib bila mulai diisi dan tidak boleh lebih awal', function () {
    $periode = periodeSb(ekskulSb());
    $admin = akunSb('admin');

    $this->actingAs($admin)->post(route('tahap.store', $periode->id), ['nama' => 'Tes', 'mulai_pada' => '2026-11-10 08:00'])
        ->assertSessionHasErrors('selesai_pada');
    $this->actingAs($admin)->post(route('tahap.store', $periode->id), ['nama' => 'Tes', 'mulai_pada' => '2026-11-10 08:00', 'selesai_pada' => '2026-11-10 07:00'])
        ->assertSessionHasErrors('selesai_pada');
    $this->actingAs($admin)->post(route('tahap.store', $periode->id), ['nama' => ''])->assertSessionHasErrors('nama');

    expect($periode->tahaps()->count())->toBe(0);
});

test('mengubah jadwal tahap memberi tahu pendaftar; mengubah nama saja tidak', function () {
    $periode = periodeSb(ekskulSb());
    $tahap = tahapSb($periode, 1, 'Wawancara');
    $pendaftar = pendaftarSb($periode);
    $admin = akunSb('admin');

    $this->actingAs($admin)->patch(route('tahap.update', $tahap->id), ['nama' => 'Wawancara Akhir'])->assertSessionHas('success');
    expect(judulNotifSb($pendaftar->user))->not->toContain('Jadwal seleksi diperbarui');

    $this->actingAs($admin)->patch(route('tahap.update', $tahap->id), [
        'nama' => 'Wawancara Akhir', 'mulai_pada' => '2026-11-12 09:00', 'selesai_pada' => '2026-11-12 11:00', 'tempat' => 'Ruang Guru',
    ])->assertSessionHas('success');

    expect($tahap->fresh()->tempat)->toBe('Ruang Guru')
        ->and(judulNotifSb($pendaftar->user))->toContain('Jadwal seleksi diperbarui');
});

test('tahap yang sudah punya penilaian tidak bisa dihapus, minimal satu tahap tersisa, dan nomor urut dirapatkan', function () {
    $periode = periodeSb(ekskulSb());
    $satu = tahapSb($periode, 1, 'Tes tulis');
    $dua = tahapSb($periode, 2, 'Wawancara');
    $tiga = tahapSb($periode, 3, 'Praktik');
    $admin = akunSb('admin');

    $casis = pendaftarSb($periode);
    CasisReview::create(['casis_pendaftaran_id' => $casis->id, 'seleksi_tahap_id' => $satu->id, 'reviewer_id' => $admin->id, 'tipe' => 'admin', 'skor' => 70, 'rekomendasi' => 'lolos']);

    $this->actingAs($admin)->delete(route('tahap.destroy', $satu->id))->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah punya penilaian'));

    $this->actingAs($admin)->delete(route('tahap.destroy', $dua->id))->assertSessionHas('success');
    expect($periode->tahaps()->pluck('nama', 'urutan')->all())->toBe([1 => 'Tes tulis', 2 => 'Praktik']);

    $sendiri = periodeSb(ekskulSb('Ekskul Sendiri'), 'rekrutmen', '2027/2028');
    $hanya = tahapSb($sendiri, 1, 'Seleksi');
    $this->actingAs($admin)->delete(route('tahap.destroy', $hanya->id))->assertSessionHas('error', fn ($m) => str_contains($m, 'minimal satu tahap'));
});

test('tahap baru tidak bisa ditambah setelah ada pendaftar yang diterima, dan tahap tidak bisa diubah setelah kepengurusan berjalan', function () {
    $periode = periodeSb(ekskulSb());
    tahapSb($periode, 1, 'Seleksi');
    pendaftarSb($periode, null, 'diterima');
    $admin = akunSb('admin');

    $this->actingAs($admin)->post(route('tahap.store', $periode->id), ['nama' => 'Terlambat'])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'ada pendaftar yang diterima'));

    $periode->update(['status' => 'berjalan']);
    $this->actingAs($admin)->post(route('tahap.store', $periode->id), ['nama' => 'Terlambat'])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'sebelum kepengurusan berjalan'));

    expect($periode->tahaps()->count())->toBe(1);
});

// ---------------------------------------------------------------------------
// Melewati tahap demi tahap
// ---------------------------------------------------------------------------

test('pendaftar lolos tahap demi tahap; lolos di tahap terakhir = diterima dan tercatat sebagai anggota', function () {
    $ekskul = ekskulSb();
    $periode = periodeSb($ekskul);
    $satu = tahapSb($periode, 1, 'Tes tulis');
    $dua = tahapSb($periode, 2, 'Wawancara');
    $pembina = pembinaSb($ekskul);
    $admin = akunSb('admin');
    $siswa = akunSb('siswa', 'Calon Hebat');
    $casis = pendaftarSb($periode, $siswa);

    // tahap 1: admin dan pembina menilai, lalu admin meloloskan
    nilaiSb($this, $admin, $casis)->assertSessionHas('success');
    nilaiSb($this, $pembina, $casis)->assertSessionHas('success');
    $this->actingAs($admin)->patch(route('casis.putuskan', $casis->id), ['keputusan' => 'lolos'])->assertSessionHas('success');

    $casis->refresh();
    expect($casis->status)->toBe('diajukan')
        ->and($casis->tahapSekarang()->id)->toBe($dua->id)
        ->and($casis->hasils()->where('seleksi_tahap_id', $satu->id)->value('hasil'))->toBe('lolos')
        ->and(PendaftaranEkskul::where('user_id', $siswa->id)->count())->toBe(0)
        ->and(judulNotifSb($siswa))->toContain('Lolos tahap Tes tulis');

    // penilaian tahap 1 tidak dihitung di tahap 2: keputusan tahap 2 menunggu penilaian baru
    $this->actingAs($admin)->patch(route('casis.putuskan', $casis->id), ['keputusan' => 'lolos'])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'Belum semua unsur'));

    // tahap 2 (terakhir)
    nilaiSb($this, $admin, $casis);
    nilaiSb($this, $pembina, $casis);
    $this->actingAs($pembina)->patch(route('casis.putuskan', $casis->id), ['keputusan' => 'lolos'])->assertSessionHas('success');

    $baris = PendaftaranEkskul::where('user_id', $siswa->id)->where('ekstrakurikuler_id', $ekskul->id)->firstOrFail();

    expect($casis->fresh()->status)->toBe('diterima')
        ->and($casis->fresh()->tahapSekarang())->toBeNull()
        ->and($baris->status)->toBe('diterima')
        ->and($baris->status_anggota)->toBe('aktif')
        ->and($baris->angkatan_ekskul)->toBe('2026/2027')
        ->and(CasisReview::where('casis_pendaftaran_id', $casis->id)->count())->toBe(4)
        ->and(judulNotifSb($siswa))->toContain('Selamat, kamu lolos seleksi Paskibra Garuda!');
});

test('tidak lolos di tahap mana pun = ditolak, dengan catatan alasan yang wajib', function () {
    $ekskul = ekskulSb();
    $periode = periodeSb($ekskul);
    tahapSb($periode, 1, 'Tes tulis');
    tahapSb($periode, 2, 'Wawancara');
    $admin = akunSb('admin');
    $siswa = akunSb('siswa');
    $casis = pendaftarSb($periode, $siswa);

    nilaiSb($this, $admin, $casis);

    $this->actingAs($admin)->patch(route('casis.putuskan', $casis->id), ['keputusan' => 'tolak'])->assertSessionHasErrors('catatan');
    expect($casis->fresh()->status)->toBe('diajukan');

    $this->actingAs($admin)->patch(route('casis.putuskan', $casis->id), ['keputusan' => 'tolak', 'catatan' => 'Nilai tes belum cukup.'])
        ->assertSessionHas('success');

    $casis->refresh();

    expect($casis->status)->toBe('ditolak')
        ->and($casis->catatan_keputusan)->toBe('Nilai tes belum cukup.')
        ->and($casis->hasils()->value('hasil'))->toBe('tidak_lolos')
        ->and(PendaftaranEkskul::where('user_id', $siswa->id)->count())->toBe(0)
        ->and(judulNotifSb($siswa))->toContain('Hasil seleksi Paskibra Garuda');
});

test('hasil tahap hanya bisa diputuskan admin atau pembina ekskul itu, dan tidak untuk pendaftarannya sendiri', function () {
    $ekskul = ekskulSb();
    $periode = periodeSb($ekskul);
    tahapSb($periode, 1, 'Seleksi');
    $casis = pendaftarSb($periode);
    $senior = akunSb('siswa', 'Kakak');
    $periode->seniors()->attach($senior->id);

    $this->actingAs($senior)->patch(route('casis.putuskan', $casis->id), ['keputusan' => 'lolos'])->assertForbidden();
    $this->actingAs(pembinaSb(ekskulSb('Ekskul Lain')))->patch(route('casis.putuskan', $casis->id), ['keputusan' => 'lolos'])->assertForbidden();
    $this->actingAs($casis->user)->patch(route('casis.putuskan', $casis->id), ['keputusan' => 'lolos'])->assertForbidden();

    expect($casis->fresh()->status)->toBe('diajukan');
});

test('penilaian hanya saat periode berstatus seleksi, oleh penilai periode itu', function () {
    $ekskul = ekskulSb();
    $periode = periodeSb($ekskul, 'rekrutmen');
    $casis = pendaftarSb($periode);
    $admin = akunSb('admin');

    nilaiSb($this, $admin, $casis)->assertSessionHas('error');
    expect(CasisReview::count())->toBe(0);

    $periode->update(['status' => 'seleksi']);

    nilaiSb($this, akunSb('siswa'), $casis)->assertForbidden();
    nilaiSb($this, $admin, $casis)->assertSessionHas('success');
    expect(CasisReview::count())->toBe(1);
});

// ---------------------------------------------------------------------------
// Satu ekskul tidak mengganggu ekskul lain
// ---------------------------------------------------------------------------

test('seleksi tiap organisasi berjalan terpisah: pendaftar MPK tidak muncul di OSIS', function () {
    $osis = ekskulSb('OSIS', 'organisasi');
    $mpk = ekskulSb('MPK', 'organisasi');
    $periodeOsis = periodeSb($osis, 'rekrutmen');
    $periodeMpk = periodeSb($mpk, 'rekrutmen');
    $siswa = akunSb('siswa', 'Calon Anggota MPK');

    $this->actingAs($siswa)->post(route('casis.simpan', $mpk->id), ['aksi' => 'ajukan', 'motivasi' => str_repeat('Ingin mengawasi dan memberi masukan. ', 2)])
        ->assertSessionHas('success');

    expect(CasisPendaftaran::where('osis_periode_id', $periodeMpk->id)->count())->toBe(1)
        ->and(CasisPendaftaran::where('osis_periode_id', $periodeOsis->id)->count())->toBe(0);

    $admin = akunSb('admin');
    $this->actingAs($admin)->get(route('casis.index', $mpk->id))->assertOk()->assertSee('Calon Anggota MPK');
    $this->actingAs($admin)->get(route('casis.index', $osis->id))->assertOk()->assertDontSee('Calon Anggota MPK');
});

test('halaman seleksi hanya untuk pengelola ekskul dan penilai, dan menampilkan pengaturan tahap', function () {
    $ekskul = ekskulSb();
    $periode = periodeSb($ekskul);
    tahapSb($periode, 1, 'Tes tulis');
    $pembina = pembinaSb($ekskul);

    $this->actingAs(akunSb('siswa'))->get(route('casis.index', $ekskul->id))->assertForbidden();
    $this->actingAs(pembinaSb(ekskulSb('Ekskul Lain')))->get(route('casis.index', $ekskul->id))->assertForbidden();

    $this->actingAs($pembina)->get(route('casis.index', $ekskul->id))
        ->assertOk()->assertSee('Tahap seleksi')->assertSee('Tes tulis')->assertSee('Tambah tahap seleksi');
});

test('siswa melihat tahap, jadwal, dan hasilnya di halaman pendaftarannya', function () {
    $ekskul = ekskulSb();
    $periode = periodeSb($ekskul);
    $satu = tahapSb($periode, 1, 'Tes tulis');
    tahapSb($periode, 2, 'Wawancara')->update(['tempat' => 'Ruang OSIS']);
    $siswa = akunSb('siswa');
    $casis = pendaftarSb($periode, $siswa);
    $casis->hasils()->create(['seleksi_tahap_id' => $satu->id, 'hasil' => 'lolos']);

    $this->actingAs($siswa)->get(route('casis.create', $ekskul->id))
        ->assertOk()->assertSee('Tahap seleksi periode 2026/2027')->assertSee('Tes tulis')->assertSee('Wawancara')
        ->assertSee('Ruang OSIS')->assertSee('Sedang berjalan');
});
