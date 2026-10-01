<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <a href="/Tree/create">Tambah Pohon</a>
    <h1>Pohon</h1>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pohon</th>
                <th>Family</th>
                <th>Usia</th>
                <th>Asal</th>
                <th>Kondisi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($trees as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->tre_name }}</td>
                    <td>{{ $item->tre_family }}</td>
                    <td>{{ $item->tre_age }}</td>
                    <td>{{ $item->tre_origin }}</td>
                    <td>{{ $item->tre_condition }}</td>
                    <td>
                    <a href="/Tree/{{$item->id}}/edit">Edit</a>
                    <a href="/Tree/{{ $item->id }}/destroy">Delete</a>
                </td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>    
</body>
</html>