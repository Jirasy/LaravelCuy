<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>form tambah siswa</h2>
    <a href="/siswa">kembali kedaftar siswa</a>
    <br><br>
    <form action="/siswa" method="POST">
        @csrf
        <label for="ssw_name">nama:<label><br>
        <input type="text" name="ssw_name" required><br>
        <label for="ssw_nisn">nisn:<label><br>
        <input type="text" name="ssw_nisn" required><br>
        <label for="ssw_class">kelas:<label><br>
        <input type="text" name="ssw_class" required><br>
        <label for="ssw_major">jurusan:<label><br>
        <input type="text" name="ssw_major" required><br>
        <button type="submit">simpan</button>
    </form>
</body>
</html>