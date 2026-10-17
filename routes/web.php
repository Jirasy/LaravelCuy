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