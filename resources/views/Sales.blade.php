<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Penjualan</title>
</head>
<body>
    <h1>Riwayat Penjualan</h1>
    <a href="/cars">kembali ke daftar mobil</a>
    <br><br>

    @if (session('success'))
        <p style="color: green">{{ session('success') }}</p>
    @endif

    <table border="1">
        <thead>
            <tr>
                <th>No.</th>
                <th>Mobil</th>
                <th>Pembeli</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $s)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $s->car->car_brand }} {{ $s->car->car_model }}</td>
                <td>{{ $s->buyer_name }}</td>
                <td>{{ $s->quantity }}</td>
                <td>{{ number_format($s->total_price) }}</td>
                <td>{{ $s->created_at }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6">Belum ada penjualan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>