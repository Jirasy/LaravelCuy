<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Data Pohon</h1>
    nama pohon: {{ $Tree->tre_name }} <br>
    kelompok pohon: {{ $Tree->tre_family }} <br>
    Umur pohon: {{ $Tree->tre_age }} <br>
    Asal pohon: {{ $Tree->tre_origin }} <br>
    Kondisi pohon: {{ $Tree->tre_condition }} <br>
</body>
</html>