<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <a href="/cars/create">Tambah Mobil</a>
    <h1>Mobil</h1>
    <table border="1">
        <thead>
            <tr>
                <th>No.</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Mesin</th>
                <th>Bahan Bakar</th>
                <th>Tipe</th>
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
                    <td>
                    <a href="/cars/{{$item->id}}/edit">Edit</a>
                    <a href="/cars/{{ $item->id }}/destroy">Delete</a>
                </td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>   
</body>
</html>