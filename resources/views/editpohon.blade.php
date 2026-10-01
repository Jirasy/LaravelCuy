<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h2>form edit pohon</h2>
    <a href="/Tree">kembali kedaftar pohon</a>
    <br><br>
    <form action="/Tree/{{ $Tree->id }}/edit" method="POST">
        @csrf
        {{-- @method('PUT') --}}
        <label for="">Nama Pohon:<label><br>
        <input type="text" name="tre_name" value="{{ $Tree->tre_name }}" required><br>
        <label for="">Family:<label><br>
        <input type="text" name="tre_family" value="{{ $Tree->tre_family }}" required><br>
        <label for="">Usia:<label><br>
        <input type="number" name="tre_age" value="{{ $Tree->tre_age }}" required><br>
        <label for="">Asal:<label><br>
        <input type="text" name="tre_origin" value="{{ $Tree->tre_origin }}" required><br>
        <label for="">Kondisi:<label><br>
        <select name="tre_condition" id="">
            <option value="Healthy">Healthy</option>
            <option value="Lumayan Healthy">Lumayan Healthy</option>
            <option value="Unhealthy">Unhealthy</option>
        </select><br>
        <input type="submit" value="Kirim">
    </form>
</body>
</html>