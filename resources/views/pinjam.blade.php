<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form action="/pinjam" method="POST">
        @csrf
        <input type="number" name="nominalpinjam" id="nominalpinjam" required>
        <select name="tenor">
            <option value="1">1 Bulan</option>
            <option value="3">3 Bulan</option>
            <option value="6">6 Bulan</option>
            <option value="12">12 Bulan</option>
            <option value="24">24 Bulan</option>
            <option value="36">36 Bulan</option>
        </select>

        {{-- <label for="alamat">Alamat:</label>
        <input type="text" name="alamat" id="alamat" required><br><br>

        <label for="no_hp">No HP:</label>
        <input type="text" name="no_hp" id="no_hp" required><br><br>

        <label for="buku">Buku:</label>
        <select name="buku_id" id="buku" required>
            @foreach ($bukus as $buku)
                <option value="{{ $buku->id }}">{{ $buku->judul }}</option>
            @endforeach
        </select><br><br> --}}

        <button type="submit">Pinjam</button>
</body>
</html>