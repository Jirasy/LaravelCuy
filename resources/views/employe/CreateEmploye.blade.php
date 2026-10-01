<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>form tambah karyawan</h2>
    <a href="/Employe">kembali kedaftar karyawan</a>
    <br><br>
    <form action="/Employe" method="POST">
        @csrf
        <label for="emp_name">nama:<label><br>
        <input type="text" name="emp_name" required><br>
        <label for="emp_number">nomor karyawan:<label><br>
        <input type="text" name="emp_number" required><br>
        <label for="emp_position">jabatan:<label><br>
        <input type="text" name="emp_position" required><br>
        <label for="emp_status">status:<label><br>
        <input type="text" name="emp_status" required><br>
        <button type="submit">kirim</button>
    </form>
</body>
</html>