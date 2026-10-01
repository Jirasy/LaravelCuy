<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
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
    <title>karyawan</title>
</head>
<body>
    <div class="card">
        <h2>profil karyawan</h2>
        <hr>
        @foreach ($Employe as $item)
            <p>nama: {{ $item['emp_name'] }}</p>
            <p>nomor karyawan: {{ $item['emp_number'] }}</p>
            <p>jabatan: {{ $item['emp_position'] }}</p>
            <p>status: {{ $item['emp_status'] }}</p>
        @endforeach
    </div>

</body>
</html>