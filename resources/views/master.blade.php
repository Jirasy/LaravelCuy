<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Default Title')</title>
</head>
<body>
    @include('tambahan') <br>
    <h1>berita hari ini</h1>
    @yield('content') <br>
    Lorem, ipsum dolor sit amet consectetur adipisicing elit.@yield('tengah') Consequuntur voluptates corrupti soluta.

    @yield('aratel')
</body>
</html>