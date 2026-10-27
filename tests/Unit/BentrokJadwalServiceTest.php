<?php

use App\Services\BentrokJadwalService;

/**
 * @return array{ekskul_id: int, ekskul: string, hari: string, mulai: string, selesai: string, peran: string}
 */
function barisUji(int $id, string $nama, string $hari, string $mulai, string $selesai): array
{
    return ['ekskul_id' => $id, 'ekskul' => $nama, 'hari' => $hari, 'mulai' => $mulai, 'selesai' => $selesai, 'peran' => 'Anggota'];
}

test('jadwal yang jamnya beririsan di hari yang sama dianggap bentrok', function () {
    $bentrok = (new BentrokJadwalService)->cari(collect([
        barisUji(1, 'Silat', 'sabtu', '08:00', '10:00'),
        barisUji(2, 'Futsal', 'sabtu', '09:30', '11:00'),
    ]));

    expect($bentrok)->toHaveCount(1)
        ->and($bentrok[0]['hari'])->toBe('sabtu')
        ->and($bentrok[0]['a']['ekskul'])->toBe('Silat')
        ->and($bentrok[0]['b']['ekskul'])->toBe('Futsal');
});

test('jadwal yang selesai tepat saat yang lain mulai tidak bentrok', function () {
    $bentrok = (new BentrokJadwalService)->cari(collect([
        barisUji(1, 'Silat', 'sabtu', '08:00', '10:00'),
        barisUji(2, 'Futsal', 'sabtu', '10:00', '12:00'),
    ]));

    expect($bentrok)->toBeEmpty();
});

test('jam yang sama di hari berbeda tidak bentrok', function () {
    $bentrok = (new BentrokJadwalService)->cari(collect([
        barisUji(1, 'Silat', 'sabtu', '08:00', '10:00'),
        barisUji(2, 'Futsal', 'minggu', '08:00', '10:00'),
    ]));

    expect($bentrok)->toBeEmpty();
});

test('jadwal yang satu berada penuh di dalam jadwal lain tetap bentrok', function () {
    $bentrok = (new BentrokJadwalService)->cari(collect([
        barisUji(1, 'Pramuka', 'jumat', '13:00', '17:00'),
        barisUji(2, 'Paduan Suara', 'jumat', '14:00', '15:00'),
    ]));

    expect($bentrok)->toHaveCount(1);
});

test('pasangan selalu diurutkan dengan id ekskul terkecil sebagai a', function () {
    $bentrok = (new BentrokJadwalService)->cari(collect([
        barisUji(9, 'Futsal', 'sabtu', '08:00', '10:00'),
        barisUji(3, 'Silat', 'sabtu', '09:00', '11:00'),
    ]));

    expect($bentrok[0]['a']['ekskul_id'])->toBe(3)
        ->and($bentrok[0]['b']['ekskul_id'])->toBe(9);
});

test('tiga jadwal yang saling beririsan menghasilkan semua pasangannya, urut menurut hari', function () {
    $bentrok = (new BentrokJadwalService)->cari(collect([
        barisUji(1, 'A', 'sabtu', '08:00', '12:00'),
        barisUji(2, 'B', 'sabtu', '09:00', '11:00'),
        barisUji(3, 'C', 'sabtu', '10:00', '13:00'),
        barisUji(4, 'D', 'senin', '15:00', '17:00'),
        barisUji(5, 'E', 'senin', '16:00', '18:00'),
    ]));

    expect($bentrok)->toHaveCount(4)
        ->and($bentrok->first()['hari'])->toBe('senin');
});

test('ringkasan bentrok memuat kedua ekskul, jam, dan nama hari', function () {
    $service = new BentrokJadwalService;
    $bentrok = $service->cari(collect([
        barisUji(1, 'Silat', 'sabtu', '08:00', '10:00'),
        barisUji(2, 'Futsal', 'sabtu', '09:30', '11:00'),
    ]))->first();

    expect($service->ringkas($bentrok))
        ->toBe('Silat (08:00–10:00) bentrok dengan Futsal (09:30–11:00) pada hari Sabtu');
});
