<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Edit Mobil</title>
</head>
<body>
    <h2>form edit mobil</h2>
    <a href="/cars">kembali kedaftar mobil</a>
    <br><br>
    <form action="/cars/{{ $cars->id }}/edit" method="POST">
        @csrf
        {{-- @method('PUT') --}}
        <label for="">Merek Mobil:<label><br>
        <input type="text" name="car_brand" value="{{ $cars->car_brand }}" required><br>
        <label for="">Model Mobil:<label><br>
        <input type="text" name="car_model" value="{{ $cars->car_model }}" required><br>
        <label for="">Mesin:<label><br>
        <input type="text" name="car_engine" value="{{ $cars->car_engine }}" required><br>
        <label for="">Bahan Bakar:<label><br>
        <input type="text" name="car_fuel" value="{{ $cars->car_fuel }}" required><br>
        <label for="">Tipe Mobil:<label><br>
        <input type="text" name="car_type" value="{{ $cars->car_type }}" required><br>
        <label for="">Stock:<label><br>
        <input type="number" name="car_stock" value="{{ $cars->car_stock }}" required><br>
        <label for="">Harga Mobil:<label><br>
        <input type="number" name="car_price" value="{{ $cars->car_price }}" required><br>
        <input type="submit" value="Kirim">
    </form>
</body>
</html>