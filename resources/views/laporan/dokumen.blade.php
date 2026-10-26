<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; margin: 24px; }
        h1 { font-size: 16px; margin: 0 0 6px; }
        .info { margin: 0 0 3px; color: #444; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #555; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #e5e7eb; }
        .tengah { text-align: center; }
        .toolbar { margin-bottom: 16px; }
        .toolbar a, .toolbar button { display: inline-block; padding: 6px 12px; margin-right: 6px; border: 1px solid #888; border-radius: 4px; background: #f3f4f6; color: #111; text-decoration: none; font-size: 12px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    @if($mode === 'cetak')
        <div class="toolbar">
            <button type="button" onclick="window.print()">🖨️ Cetak</button>
            <a href="{{ route('laporan.unduh', [$ekskul->id, $jenis, 'pdf']) }}?{{ http_build_query($query) }}">Unduh PDF</a>
            <a href="{{ route('laporan.unduh', [$ekskul->id, $jenis, 'excel']) }}?{{ http_build_query($query) }}">Unduh Excel</a>
            <button type="button" onclick="window.close()">Tutup</button>
        </div>
    @endif

    <h1>{{ $judul }}</h1>
    @foreach($info as $baris)
        <div class="info">{{ $baris }}</div>
    @endforeach

    @include('laporan._' . $jenis, ['d' => $d])

    <p class="info" style="margin-top: 14px;">Dicetak oleh {{ $dicetakOleh }} pada {{ $dicetakPada->format('d-m-Y H:i') }}</p>
</body>
</html>