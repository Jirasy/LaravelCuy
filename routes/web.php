<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BeresController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\templatingController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\kenzoController as KenzoController;
use App\Http\Controllers\siswaController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\JihyController;
use App\Http\Controllers\EkstrakurikulerController;
use App\Http\Controllers\PendaftaranEkskulController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\EnsureUserIsActive;   
use App\Http\Controllers\EkskulPengumumanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\EkskulPrestasiController;
use App\Http\Controllers\EkskulEventController;
use App\Http\Controllers\KeanggotaanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\EkskulPembinaController;
use App\Http\Controllers\ProfilPenggunaController;
use App\Http\Controllers\EkskulJabatanController;
use App\Http\Controllers\AnggotaJabatanController;
use App\Http\Controllers\PengunduranDiriController;
use App\Http\Controllers\EkskulTugasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\EkskulTautanController;
use App\Http\Controllers\PendaftaranKontrolController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\EkskulMateriController;
use App\Http\Controllers\MateriAksesController;
use App\Http\Controllers\EkskulPrController;
use App\Http\Controllers\EkskulSaranController;
use App\Http\Controllers\EkskulFotoController;
use App\Http\Controllers\CasisController;
use App\Http\Controllers\OsisPeriodeController;
use App\Http\Middleware\PaksaGantiPassword;
use App\Http\Controllers\CarsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/blog', function () {
    return view('blog');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/oce', function () {
    return view('oce');
});

Route::get('/beranda', function () {
    return view('beranda');
});

Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/oce', function () {
    return view('oce');
});
Route::get('/beranda', function () {
    return view('beranda');
});
Route::get('/yeberes', [BeresController::class, 'beres']);

Route::get('/profile', [ProfileController::class, 'biodata']);
Route::get('/previous-school', [ProfileController::class, 'previousSchool']);

Route::get('/templating', [templatingController::class, 'blade']);

Route::get('/mapel', [TeacherController::class, 'mapel']);

Route::get('/kenzo', [KenzoController::class, 'kenzo']);

Route::get('/siswa', [siswaController::class, 'siswa']);
Route::get('/siswa', [siswaController::class, 'index']);
Route::get('/siswa/create', [siswaController::class, 'create']);
Route::post('/siswa', [siswaController::class, 'store']);

Route::get('/Asset', [AssetController::class, 'Asset']);

Route::get('/Tree', [TreeController::class, 'index']);
Route::get('/Tree/create', [TreeController::class, 'create']);
Route::post('/Tree/store', [TreeController::class, 'store']);
Route::get('/Tree/{id}/edit', [TreeController::class, 'edit']);
Route::post('/Tree/{id}/edit', [TreeController::class, 'update']);
Route::get('/Tree/{id}/destroy', [TreeController::class, 'destroy']);

Route::get('/Health', [HealthController::class, 'index']);

Route::get('/Employe', [EmployeController::class, 'index']);
Route::get('/Employe/create', [EmployeController::class, 'create']);
Route::post('/Employe', [EmployeController::class, 'store']);

Route::get('/violation', [ViolationController::class, 'index']);
Route::get('/violation/create', [ViolationController::class, 'create']);
Route::post('/violation/store', [ViolationController::class, 'store']);

Route::get('/jihy', [JihyController::class, 'index']);
Route::get('/jihy/create', [JihyController::class, 'create']);
Route::post('/jihy/store', [JihyController::class, 'store']);

// Route::get('/', function () {
//     return redirect()->route('ekstrakurikuler.index');
// });

// ---------- Auth ----------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.process');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', EnsureUserIsActive::class, PaksaGantiPassword::class])->group(function () {

    Route::get('/pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
    Route::put('/pengaturan/tampilan', [PengaturanController::class, 'updateTampilan'])->name('pengaturan.tampilan');
    Route::put('/pengaturan/password', [PengaturanController::class, 'updatePassword'])->name('pengaturan.password');
    Route::delete('/pengaturan/wallpaper', [PengaturanController::class, 'hapusWallpaper'])->name('pengaturan.wallpaper.hapus');

    Route::get('/riwayat/{user?}', [RiwayatController::class, 'show'])->name('riwayat.show');

    // Pengunduran diri: siswa mengajukan keluar dan membatalkan pengajuannya sendiri
    Route::post('/ekstrakurikuler/{ekstrakurikuler}/keluar', [PengunduranDiriController::class, 'ajukanAnggota'])->name('pengunduran.anggota');
    Route::delete('/pengunduran-diri/{pengunduran}', [PengunduranDiriController::class, 'batalkan'])->name('pengunduran.batal');

    // Notifikasi in-app
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');
    Route::get('/notifikasi/{id}', [NotifikasiController::class, 'buka'])->where('id', '[0-9a-fA-F\-]{36}')->name('notifikasi.buka');

    // Task: semua user bisa membuat task pribadi; admin & pembina juga bisa memberi task ke orang lain
    Route::get('/tugas', [EkskulTugasController::class, 'index'])->name('tugas.index');
    Route::post('/tugas', [EkskulTugasController::class, 'store'])->name('tugas.store');
    Route::patch('/tugas/penerima/{penerima}', [EkskulTugasController::class, 'status'])->name('tugas.status');
    Route::delete('/tugas/{tugas}', [EkskulTugasController::class, 'destroy'])->name('tugas.destroy');

    // Materi & PR: akses berkas dicek di controller (Policy lihatMateri); anggota mengumpulkan PR
    Route::get('/materi/{materi}/berkas', [EkskulMateriController::class, 'berkas'])->name('materi.berkas');
    Route::post('/ekstrakurikuler/{ekstrakurikuler}/minta-akses', [MateriAksesController::class, 'mintaAkses'])->middleware('throttle:3,10')->name('materi.minta-akses');
    Route::post('/pr/{pr}/kumpul', [EkskulPrController::class, 'kumpul'])->name('pr.kumpul');
    Route::get('/pr/pengumpulan/{pengumpulan}/berkas', [EkskulPrController::class, 'berkas'])->name('pr.berkas');

    // Galeri: anggota aktif dan pengelola. Foto siswa menunggu persetujuan pengelola.
    Route::post('/ekstrakurikuler/{ekstrakurikuler}/galeri', [EkskulFotoController::class, 'store'])->middleware('throttle:15,60')->name('galeri.store');
    Route::get('/galeri/{foto}/berkas', [EkskulFotoController::class, 'berkas'])->name('galeri.berkas');
    Route::delete('/galeri/{foto}', [EkskulFotoController::class, 'destroy'])->name('galeri.destroy');

    // OSIS & CaSis: siswa mendaftar; penilai (pembina OSIS, admin, senior) menilai. Hak akses dicek di controller.
    Route::get('/osis/casis/daftar', [CasisController::class, 'create'])->name('casis.create');
    Route::post('/osis/casis/daftar', [CasisController::class, 'simpan'])->name('casis.simpan');
    Route::post('/osis/casis/mundur', [CasisController::class, 'mundur'])->name('casis.mundur');
    Route::get('/osis/casis', [CasisController::class, 'index'])->name('casis.index');
    Route::get('/osis/casis/{casis}', [CasisController::class, 'show'])->whereNumber('casis')->name('casis.show');
    Route::post('/osis/casis/{casis}/review', [CasisController::class, 'review'])->whereNumber('casis')->name('casis.review');

    // Kritik & saran: semua pengguna boleh mengirim (dibatasi 10 kiriman per jam)
    Route::get('/kritik-saran', [EkskulSaranController::class, 'index'])->name('saran.index');
    Route::post('/kritik-saran', [EkskulSaranController::class, 'store'])->middleware('throttle:10,60')->name('saran.store');

    // Profil publik (semua user login) dan edit profil milik sendiri
    Route::get('/profil/edit', [ProfilPenggunaController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilPenggunaController::class, 'update'])->name('profil.update');
    Route::delete('/profil/foto', [ProfilPenggunaController::class, 'hapusFoto'])->name('profil.foto.hapus');
    Route::get('/profil/{user?}', [ProfilPenggunaController::class, 'show'])->whereNumber('user')->name('profil.show');

    // Semua user yang login
    Route::get('/ekstrakurikuler', [EkstrakurikulerController::class, 'index'])->name('ekstrakurikuler.index');
    Route::get('/ekstrakurikuler/{id}', [EkstrakurikulerController::class, 'show'])->whereNumber('id')->name('ekstrakurikuler.show');
    Route::get('/pendaftaran', [PendaftaranEkskulController::class, 'index'])->name('pendaftaran.index');
    Route::get('/pendaftaran/create', [PendaftaranEkskulController::class, 'create'])->name('pendaftaran.create');
    Route::post('/pendaftaran/store', [PendaftaranEkskulController::class, 'store'])->name('pendaftaran.store');

    // Admin & pembina aja
    Route::middleware(RoleMiddleware::class . ':admin,pembina')->group(function () {
        Route::get('/ekstrakurikuler/create', [EkstrakurikulerController::class, 'create'])->name('ekstrakurikuler.create');
        Route::post('/ekstrakurikuler/store', [EkstrakurikulerController::class, 'store'])->name('ekstrakurikuler.store');
        Route::get('/ekstrakurikuler/{id}/edit', [EkstrakurikulerController::class, 'edit'])->name('ekstrakurikuler.edit');
        Route::put('/ekstrakurikuler/{id}', [EkstrakurikulerController::class, 'update'])->name('ekstrakurikuler.update');
        Route::delete('/ekstrakurikuler/{id}', [EkstrakurikulerController::class, 'destroy'])->name('ekstrakurikuler.destroy');
        Route::patch('/pendaftaran/{pendaftaran}/status', [PendaftaranEkskulController::class, 'updateStatus'])->name('pendaftaran.updateStatus');
        Route::post('/ekstrakurikuler/{ekstrakurikuler}/pengumuman', [EkskulPengumumanController::class, 'store'])->name('ekskul-pengumuman.store');
        Route::get('/ekskul-pengumuman/{pengumuman}/edit', [EkskulPengumumanController::class, 'edit'])->name('ekskul-pengumuman.edit');
        Route::put('/ekskul-pengumuman/{pengumuman}', [EkskulPengumumanController::class, 'update'])->name('ekskul-pengumuman.update');
        Route::delete('/ekskul-pengumuman/{pengumuman}', [EkskulPengumumanController::class, 'destroy'])->name('ekskul-pengumuman.destroy');

        Route::post('/ekstrakurikuler/{ekstrakurikuler}/prestasi', [EkskulPrestasiController::class, 'store'])->name('ekskul-prestasi.store');
        Route::get('/ekskul-prestasi/{prestasi}/edit', [EkskulPrestasiController::class, 'edit'])->name('ekskul-prestasi.edit');
        Route::put('/ekskul-prestasi/{prestasi}', [EkskulPrestasiController::class, 'update'])->name('ekskul-prestasi.update');
        Route::delete('/ekskul-prestasi/{prestasi}', [EkskulPrestasiController::class, 'destroy'])->name('ekskul-prestasi.destroy');

        Route::post('/ekstrakurikuler/{ekstrakurikuler}/event', [EkskulEventController::class, 'store'])->name('ekskul-event.store');
        Route::get('/ekskul-event/{event}/edit', [EkskulEventController::class, 'edit'])->name('ekskul-event.edit');
        Route::put('/ekskul-event/{event}', [EkskulEventController::class, 'update'])->name('ekskul-event.update');
        Route::delete('/ekskul-event/{event}', [EkskulEventController::class, 'destroy'])->name('ekskul-event.destroy');

        Route::patch('/pendaftaran/{pendaftaran}/keanggotaan', [KeanggotaanController::class, 'update'])->name('keanggotaan.update');

        // Struktur jabatan kustom per ekskul + penetapannya ke anggota
        Route::post('/ekstrakurikuler/{ekstrakurikuler}/jabatan', [EkskulJabatanController::class, 'store'])->name('ekskul-jabatan.store');
        Route::get('/ekskul-jabatan/{jabatan}/edit', [EkskulJabatanController::class, 'edit'])->name('ekskul-jabatan.edit');
        Route::put('/ekskul-jabatan/{jabatan}', [EkskulJabatanController::class, 'update'])->name('ekskul-jabatan.update');
        Route::delete('/ekskul-jabatan/{jabatan}', [EkskulJabatanController::class, 'destroy'])->name('ekskul-jabatan.destroy');
        Route::post('/pendaftaran/{pendaftaran}/jabatan', [AnggotaJabatanController::class, 'store'])->name('anggota-jabatan.store');
        Route::delete('/anggota-jabatan/{penetapan}', [AnggotaJabatanController::class, 'destroy'])->name('anggota-jabatan.destroy');

        // Pengunduran diri: panel keputusan, pembina mengajukan keluar, mengeluarkan anggota
        Route::get('/pengunduran-diri', [PengunduranDiriController::class, 'index'])->name('pengunduran.index');
        Route::patch('/pengunduran-diri/{pengunduran}', [PengunduranDiriController::class, 'putuskan'])->name('pengunduran.putuskan');
        Route::post('/ekstrakurikuler/{ekstrakurikuler}/pembina/keluar', [PengunduranDiriController::class, 'ajukanPembina'])->name('pengunduran.pembina');
        Route::post('/pendaftaran/{pendaftaran}/keluarkan', [PengunduranDiriController::class, 'keluarkan'])->name('pengunduran.keluarkan');

        // Buka/tutup pendaftaran dan tautan ekskul
        Route::patch('/ekstrakurikuler/{ekstrakurikuler}/pendaftaran-kontrol', [PendaftaranKontrolController::class, 'update'])->name('pendaftaran-kontrol.update');
        Route::post('/tautan', [EkskulTautanController::class, 'store'])->name('tautan.store');
        Route::delete('/tautan/{tautan}', [EkskulTautanController::class, 'destroy'])->name('tautan.destroy');

        // Absensi, nilai (hanya admin & pembina ekskul itu, dijaga Policy), dan laporan
        Route::post('/ekstrakurikuler/{ekstrakurikuler}/pertemuan', [AbsensiController::class, 'store'])->name('pertemuan.store');
        Route::get('/pertemuan/{pertemuan}', [AbsensiController::class, 'edit'])->name('absensi.edit');
        Route::put('/pertemuan/{pertemuan}', [AbsensiController::class, 'update'])->name('absensi.update');
        Route::delete('/pertemuan/{pertemuan}', [AbsensiController::class, 'destroy'])->name('pertemuan.destroy');

        Route::get('/pendaftaran/{pendaftaran}/nilai', [NilaiController::class, 'edit'])->name('nilai.edit');
        Route::post('/pendaftaran/{pendaftaran}/nilai', [NilaiController::class, 'store'])->name('nilai.store');
        Route::delete('/nilai/{nilai}', [NilaiController::class, 'destroy'])->name('nilai.destroy');

        // Materi, akses materi, PR, dan kotak masuk kritik/saran (pengelola)
        Route::post('/ekstrakurikuler/{ekstrakurikuler}/materi', [EkskulMateriController::class, 'store'])->name('materi.store');
        Route::delete('/materi/{materi}', [EkskulMateriController::class, 'destroy'])->name('materi.destroy');
        Route::patch('/pendaftaran/{pendaftaran}/akses-materi', [MateriAksesController::class, 'toggle'])->name('materi.akses');
        Route::post('/ekstrakurikuler/{ekstrakurikuler}/akses-materi', [MateriAksesController::class, 'semua'])->name('materi.akses-semua');
        Route::post('/ekstrakurikuler/{ekstrakurikuler}/pr', [EkskulPrController::class, 'store'])->name('pr.store');
        Route::get('/pr/{pr}', [EkskulPrController::class, 'show'])->name('pr.show');
        Route::delete('/pr/{pr}', [EkskulPrController::class, 'destroy'])->name('pr.destroy');
        Route::patch('/pr/pengumpulan/{pengumpulan}', [EkskulPrController::class, 'nilai'])->name('pr.nilai');
        Route::get('/kritik-saran/masuk', [EkskulSaranController::class, 'masuk'])->name('saran.masuk');
        Route::patch('/kritik-saran/{saran}', [EkskulSaranController::class, 'tanggapi'])->name('saran.tanggapi');
        Route::patch('/galeri/{foto}/setujui', [EkskulFotoController::class, 'setujui'])->name('galeri.setujui');
        Route::post('/ekstrakurikuler/{ekstrakurikuler}/galeri/setujui-semua', [EkskulFotoController::class, 'setujuiSemua'])->name('galeri.setujui-semua');

        // OSIS: keputusan akhir, periode, senior penilai, dan status OSIS siswa dari Kelola User (admin & pembina OSIS)
        Route::patch('/osis/casis/{casis}/keputusan', [CasisController::class, 'putuskan'])->whereNumber('casis')->name('casis.putuskan');
        Route::post('/users/{user}/casis', [CasisController::class, 'masukkan'])->name('casis.masukkan');
        Route::post('/users/{user}/pengurus-osis', [CasisController::class, 'jadikanPengurus'])->name('casis.pengurus');
        Route::post('/osis/periode', [OsisPeriodeController::class, 'store'])->name('periode.store');
        Route::patch('/osis/periode/{periode}/status', [OsisPeriodeController::class, 'status'])->name('periode.status');
        Route::post('/osis/periode/{periode}/senior', [OsisPeriodeController::class, 'seniorTambah'])->name('periode.senior.store');
        Route::delete('/osis/periode/{periode}/senior/{user}', [OsisPeriodeController::class, 'seniorHapus'])->name('periode.senior.destroy');

        Route::get('/ekstrakurikuler/{ekstrakurikuler}/laporan/{jenis}/{format}', [LaporanController::class, 'unduh'])
            ->whereIn('jenis', ['anggota', 'absensi', 'nilai'])
            ->whereIn('format', ['cetak', 'pdf', 'excel'])
            ->name('laporan.unduh');

        // Kelola User: admin & pembina (batasan per akun diatur UserPolicy)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Admin aja
    Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
        Route::get('/tautan', [EkskulTautanController::class, 'index'])->name('tautan.index');

        Route::post('/ekstrakurikuler/{ekstrakurikuler}/pembina', [EkskulPembinaController::class, 'store'])->name('ekskul-pembina.store');
        Route::delete('/ekstrakurikuler/{ekstrakurikuler}/pembina/{user}', [EkskulPembinaController::class, 'destroy'])->name('ekskul-pembina.destroy');
    });
});

Route::get('/cars', [CarsController::class, 'index']);
Route::get('/cars/create', [CarsController::class, 'create']);
Route::post('/cars', [CarsController::class, 'store']);
Route::get('/cars/{id}/edit', [CarsController::class, 'edit']);
Route::put('/cars/{id}', [CarsController::class, 'update']);
Route::delete('/cars/{id}', [CarsController::class, 'destroy']);
