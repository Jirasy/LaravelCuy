<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tambah Mobil</title>
</head>
<body>
    <h2>form tambah mobil</h2>
    <a href="/cars">kembali kedaftar mobil</a>
    <br><br>
    <form action="/cars" method="POST">
        @csrf
        <label for="">Brand:</label><br>
        <input type="text" name="car_brand" required><br>
        <label for="">Model:</label><br>
        <input type="text" name="car_model" required><br>
        <label for="">Engine:</label><br>
        <input type="text" name="car_engine" required><br>
        <label for="">Fuel Type:</label><br>
        <input type="text" name="car_fuel" required><br>
        <label for="">Type:</label><br>
        <input type="text" name="car_type" required><br>
        <label for="">Price:</label><br>
        <input type="number" name="car_price" required><br>
        <label for="">Stock:</label><br>
        <input type="number" name="car_stock" required><br>
        <input type="submit" value="Kirim">
    </form>
</body>
</html>