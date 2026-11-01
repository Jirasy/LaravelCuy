<?php

use App\Support\Nisn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('Nisn::bersihkan membuang spasi dan ".0" sisa sel angka, tanpa menyentuh titik pemisah', function (?string $masukan, ?string $hasil) {
    expect(Nisn::bersihkan($masukan))->toBe($hasil);
})->with([
    ['2526.0.10.001', '2526.0.10.001'],
    ['  2526 .0.10.001 ', '2526.0.10.001'],
    ['0051234567', '0051234567'],
    ['3001234567.0', '3001234567'],
    ['3001234567.00', '3001234567'],
    ["0051234567\u{00A0}", '0051234567'],
    ['', null],
    ['   ', null],
    [null, null],
]);

test('Nisn::valid menerima huruf, angka, dan pemisah . - / di tengah (5-30 karakter)', function (string $nisn, bool $valid) {
    expect(Nisn::valid($nisn))->toBe($valid);
})->with([
    ['2526.0.10.001', true],
    ['0051234567', true],
    ['12345-6789/01', true],
    ['N1234567', true],
    [str_repeat('1', 30), true],
    ['1234', false],
    ['.12345', false],
    ['12345.', false],
    ['12@4567', false],
    ['12 345', false],
    [str_repeat('1', 31), false],
]);

test('Nisn::dariBerkas menolak isi yang bukan NISN (kurang dari 4 angka atau format salah)', function (string $sel, ?string $hasil) {
    expect(Nisn::dariBerkas($sel))->toBe($hasil);
})->with([
    ['2526.0.10.001', '2526.0.10.001'],
    ['0012345678', '0012345678'],
    ['hadir', null],
    ['-', null],
    ['12', null],
    ['Budi', null],
]);

test('kolom NISN di database bertipe teks, bukan angka', function () {
    expect(Schema::getColumnType('users', 'nisn'))->toBeIn(['varchar', 'string', 'text', 'char'])
        ->and(Schema::getColumnType('pendaftaran_ekskuls', 'nisn'))->toBeIn(['varchar', 'string', 'text', 'char']);
});
