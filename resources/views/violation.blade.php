<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <a href="/violation/create">Tambah Pelanggaran</a>
    <h1>Violation</h1>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Pelanggaran</th>
                <th>Poin</th>
                <th>Hukuman</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vlt as $no => $item)
                <tr>
                    <td>{{ $no + 1 }}</td>
                    <td>{{ $item->vlt_name }}</td>
                    <td>{{ $item->vlt_point }}</td>
                    <td>{{ $item->vlt_punishment }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>    

</body>
</html>