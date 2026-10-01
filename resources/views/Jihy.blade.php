<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <a href="/Jihy/create">Tambah Mobil</a>
    <h1>Mobil</h1>
<table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Model</th>
                <th>Tipe Mobil</th>
                <th>Tipe Bahan Bakar</th>
                <th>Transmisi</th>
                <th>Tipe Body</th>
                <th>Kapasitas Kursi</th>
                <th>Kapasitas Mesin</th>
                <th>Harga</th>
                <th>Tahun Lahir</th>
                <th>Ide </th>
                <th>Headquarter</th>
                <th>Asal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jihys as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->jihy_model_name }}</td>
                    <td>{{ $item->jihy_model_type }}</td>
                    <td>{{ $item->jihy_fuel_type }}</td>
                    <td>{{ $item->jihy_transmission }}</td>
                    <td>{{ $item->jihy_body_type }}</td>
                    <td>{{ $item->jihy_seating_capacity }}</td>
                    <td>{{ $item->jihy_engine_capacity }}</td>
                    <td>{{ $item->jihy_price }}</td>
                    <td>{{ $item->jihy_year }}</td>
                    <td>{{ $item->jihy_id }}</td>
                    <td>{{ $item->jihy_headquarter }}</td>
                    <td>{{ $item->jihy_origin }}</td>
                    <td>{{ $item->jihy_status ? 'Diproduksi' : 'berhenti' : 'Coming soon' : 'rumor' : 'Tidak diproduksi' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>    
</body>
</html>