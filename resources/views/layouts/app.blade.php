<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Ekstrakurikuler')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col">

    <!-- Header / Navigasi Ringkas -->
    <header class="bg-indigo-600 text-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/ekstrakurikuler" class="text-xl font-bold tracking-wide">EkskulApp</a>
            <div class="space-x-4 text-sm font-medium">
                <a href="/ekstrakurikuler" class="hover:text-indigo-200">Daftar Ekskul</a>
                <a href="/pendaftaran" class="hover:text-indigo-200">Pendaftaran</a>
            </div>
        </div>
    </header>

    <!-- Konten Utama (Berubah-ubah sesuai halaman) -->
    <main class="flex-grow max-w-6xl w-full mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r text-sm">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500">
        &copy; {{ date('Y') }} Sistem Informasi Ekstrakurikuler.
    </footer>

</body>
</html>