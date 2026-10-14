<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beli Mobil</title>
</head>
<body>
    <h2>Beli Mobil</h2>
    <a href="/cars">kembali ke daftar mobil</a>
    <br><br>

    <p>
        {{ $car->car_brand }} {{ $car->car_model }} |
        Harga: {{ number_format($car->car_price) }} |
        Stok: {{ $car->car_stock }}
    </p>

    <form action="/cars/{{ $car->id }}/buy" method="POST">
        @csrf
        <label>Nama Pembeli:</label><br>
        <input type="text" name="buyer_name" value="{{ old('buyer_name') }}" required><br>
        @error('buyer_name') <small style="color: red">{{ $message }}</small><br> @enderror

        <label>Jumlah:</label><br>
        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="{{ $car->car_stock }}" required><br>
        @error('quantity') <small style="color: red">{{ $message }}</small><br> @enderror

        <br>
        <input type="submit" value="Beli">
    </form>
</body>
</html>