<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Karyawan</title>
</head>
<body>
    <h2>Daftar Karyawan</h2>
    <a href="/Employe/create">Tambah Karyawan</a> <br>
     <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Nomor Karyawan</th>
                <th>Jabatan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employes as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->emp_name }}</td>
                    <td>{{ $item->emp_number }}</td>
                    <td>{{ $item->emp_position }}</td>
                    <td>{{ $item->emp_status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data karyawan. Silakan tambah data!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>