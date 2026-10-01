<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Data Kesehatan</h1>
    nama pasien: {{ $health->hlt_name }} <br>
    jenis darah: {{ $health->hlt_blood_type }} <br>
    penyakit: {{ $health->hlt_illness }} <br>
    disabilitas: {{ $health->hlt_disability }} <br>
    tinggi badan: {{ $health->hlt_height }} <br>
    berat badan: {{ $health->hlt_weight }} <br>
</body>
</html>