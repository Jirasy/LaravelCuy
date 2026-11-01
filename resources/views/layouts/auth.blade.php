<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Ekstrakurikuler</title>
    @include('partials.head')
    @include('partials.auth-style')
</head>
<body class="auth-body">
    <div class="theme-fixed">@include('partials.theme-toggle')</div>
    @include('partials.auth-bg')

    <main class="auth-main">
        @yield('content')
    </main>

    <script>
        (function () {
            // Tombol mata: tampilkan / sembunyikan password
            document.querySelectorAll('[data-eye]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    var isian = document.getElementById(tombol.dataset.eye);
                    var tampil = isian.type === 'password';
                    isian.type = tampil ? 'text' : 'password';
                    tombol.setAttribute('aria-pressed', tampil ? 'true' : 'false');
                    tombol.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
                });
            });

            // Cegah klik ganda: tombol kirim dikunci selama form diproses
            document.querySelectorAll('[data-auth-form]').forEach(function (form) {
                var tombol = form.querySelector('button[type=submit]');
                var isiAwal = tombol.innerHTML;
                form.addEventListener('submit', function () {
                    tombol.disabled = true;
                    tombol.innerHTML = '<span class="auth-spinner" aria-hidden="true"></span>Memproses…';
                });
                // Kembali lewat tombol "back" browser: aktifkan lagi
                window.addEventListener('pageshow', function (e) {
                    if (e.persisted) { tombol.disabled = false; tombol.innerHTML = isiAwal; }
                });
            });
        })();
    </script>
</body>
</html>
