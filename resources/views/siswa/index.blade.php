<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Siswa</title>
</head>
<body>
    <form action="/siswa" method="GET">
    <input type="text" name="search" placeholder="Cari nama/NISN..." value="{{ request('search') }}">
    <button type="submit">Cari</button>
    </form>
    <h2>Daftar Siswa</h2>
    <a href="/siswa/create">+ Tambah Siswa Baru</a>
    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NISN</th>
                <th>Kelas</th>
                <th>Jurusan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($siswas as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->ssw_name }}</td>
                    <td>{{ $item->ssw_nisn }}</td>
                    <td>{{ $item->ssw_class }}</td>
                    <td>{{ $item->ssw_major }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data siswa. Silakan tambah data!</td>
                </tr>
            @endforelse

            @if(session('success'))
            <div style="color: green; font-weight: bold; margin-bottom: 10px;">
            {{ session('success') }}
            </div>
            @endif
        </tbody>
    </table>
</body>
</html>