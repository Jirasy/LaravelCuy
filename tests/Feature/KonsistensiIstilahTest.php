<?php

use Illuminate\Support\Facades\Route;

// Penjaga konsistensi (tanpa database): istilah di UI dan nama route yang dipakai kode.

function berkasPhp(array $folder): array
{
    $hasil = [];

    foreach ($folder as $f) {
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($f), FilesystemIterator::SKIP_DOTS));

        foreach ($iter as $berkas) {
            if ($berkas->isFile() && str_ends_with($berkas->getFilename(), '.php')) {
                $hasil[] = $berkas->getPathname();
            }
        }
    }

    return $hasil;
}

test('istilah PR / Pekerjaan Rumah tidak dipakai lagi, semuanya "Task"', function () {
    // database/migrations sengaja tidak dipindai: riwayat migration tidak diubah
    $bermasalah = [];

    foreach (berkasPhp(['app', 'resources/views', 'routes', 'database/seeders']) as $path) {
        if (preg_match('/\bPR\b|Pekerjaan Rumah/', file_get_contents($path))) {
            $bermasalah[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
        }
    }

    expect($bermasalah)->toBe([]);
});

test('semua nama route yang dipanggil lewat route() terdefinisi', function () {
    $hilang = [];

    foreach (berkasPhp(['app', 'resources/views']) as $path) {
        // Sisa proyek lain (penjualan mobil) yang tidak punya route di aplikasi ini
        if (basename($path) === 'CarsController.php') {
            continue;
        }

        preg_match_all('/(?<![>\w$])route\(\s*([\'"])([\w.\-]+)\1/', file_get_contents($path), $cocok);

        foreach ($cocok[2] as $nama) {
            if (! Route::has($nama)) {
                $hilang[] = $nama . ' <- ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
            }
        }
    }

    expect(array_values(array_unique($hilang)))->toBe([]);
});
