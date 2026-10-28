{{-- Dirender oleh App\Exports\LaporanExport (FromView), lalu dibaca PhpSpreadsheet sebagai HTML.
     Sengaja tanpa CSS dan tanpa tombol: yang terbaca hanya tabel. Isi tabel memakai partial yang sama dengan PDF. --}}
@php
    $lebar = match ($jenis) {
        'anggota' => 7,
        'absensi' => 8 + $d['pertemuans']->count(),
        default   => 7 + count(\App\Models\EkskulNilai::ASPEK),
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
</head>
<body>
    <table>
        <tr><td colspan="{{ $lebar }}"><strong>{{ $judul }}</strong></td></tr>
        @foreach($info as $baris)
            <tr><td colspan="{{ $lebar }}">{{ $baris }}</td></tr>
        @endforeach
    </table>

    @include('laporan._' . $jenis, ['d' => $d])

    <table>
        <tr><td colspan="{{ $lebar }}">Dicetak oleh {{ $dicetakOleh }} pada {{ $dicetakPada->format('d-m-Y H:i') }}</td></tr>
    </table>
</body>
</html>
