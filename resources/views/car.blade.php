<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mobil</title>
</head>
<body>
    <a href="/cars/create">Tambah Mobil</a> |
    <a href="/sales">Riwayat Penjualan</a>
    <h1>Mobil</h1>

    @if (session('success'))
        <p style="color: green">{{ session('success') }}</p>
    @endif

    <table border="1">
        <thead>
            <tr>
                <th>No.</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Mesin</th>
                <th>Bahan Bakar</th>
                <th>Tipe</th>
                <th>Stock</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cars as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->car_brand }}</td>
                <td>{{ $item->car_model }}</td>
                <td>{{ $item->car_engine }}</td>
                <td>{{ $item->car_fuel }}</td>
                <td>{{ $item->car_type }}</td>
                <td>{{ $item->car_stock }}</td>
                <td>{{ $item->car_price }}</td>
                <td>
                    <a href="/cars/{{ $item->id }}/edit">Edit</a>

                    <form action="/cars/{{ $item->id }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Hapus mobil ini?')">Delete</button>
                    </form>

                    @if ($item->car_stock > 0)
                        <a href="/cars/{{ $item->id }}/buy">beli</a>
                    @else
                        <span>Stok habis</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>