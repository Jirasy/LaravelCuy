<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>form tambah pelanggaran</h2>
    <a href="/violation">kembali kedaftar pelanggaran</a>
    <br><br>
    <form action="/violation/store" method="POST">
        @csrf
        <label for="">Pelanggaran:<label><br>
        <input type="text" name="vlt_name" required><br>
        <label for="">Poin:<label><br>
        <input type="text" name="vlt_point" required><br>
        <label for="">Hukuman:<label><br>
        <input type="text" name="vlt_punishment" required><br>
        <input type="submit" value="Kirim">
    </form>
</body>
</html>