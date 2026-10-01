<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .card { border: 1px solid #ccc; padding: 20px; border-radius: 8px; width: 300px; }
        h2 { margin-top: 0; }
    </style>
    <title>siswa</title>
</head>
<body>
    <div class="card">
        <h2>profil siswa</h2>
        <hr>
        @if(is_array($siswa))
            <p>nama: {{ $siswa['ssw_name'] }}</p>
            <p>nisn: {{ $siswa['ssw_nisn'] }}</p>
            <p>kelas: {{ $siswa['ssw_class'] }}</p>
            <p>jurusan: {{ $siswa['ssw_major'] }}</p>
        @else
            <p>nama: {{ $siswa->ssw_name }}</p>
            <p>nisn: {{ $siswa->ssw_nisn }}</p>
            <p>kelas: {{ $siswa->ssw_class }}</p>
            <p>jurusan: {{ $siswa->ssw_major }}</p>
        @endif
    </div>

</body>
</html>