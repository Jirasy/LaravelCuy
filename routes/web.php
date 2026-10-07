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


Route::get('/ekstrakurikuler', [EkstrakurikulerController::class, 'index'])->name('ekstrakurikuler.index');
Route::get('/ekstrakurikuler/create', [EkstrakurikulerController::class, 'create'])->name('ekstrakurikuler.create');
Route::post('/ekstrakurikuler/store', [EkstrakurikulerController::class, 'store'])->name('ekstrakurikuler.store');
Route::get('/ekstrakurikuler/{id}/edit', [EkstrakurikulerController::class, 'edit'])->name('ekstrakurikuler.edit');
Route::put('/ekstrakurikuler/{id}', [EkstrakurikulerController::class, 'update'])->name('ekstrakurikuler.update');
Route::delete('/ekstrakurikuler/{id}', [EkstrakurikulerController::class, 'destroy'])->name('ekstrakurikuler.destroy');

Route::get('/pendaftaran', [PendaftaranEkskulController::class, 'index'])->name('pendaftaran.index');
Route::get('/pendaftaran/create', [PendaftaranEkskulController::class, 'create'])->name('pendaftaran.create');
Route::post('/pendaftaran/store', [PendaftaranEkskulController::class, 'store'])->name('pendaftaran.store');
Route::patch('/pendaftaran/{pendaftaran}/status', [PendaftaranEkskulController::class, 'updateStatus'])->name('pendaftaran.updateStatus');