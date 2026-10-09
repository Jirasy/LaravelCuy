<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Ekstrakurikuler</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Masuk</h1>
        <p class="text-gray-600 text-sm mb-6">Masuk untuk mendaftar atau mengelola ekstrakurikuler.</p>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border p-2 rounded-lg mt-1">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Password</label>
                <input type="password" name="password" required class="w-full border p-2 rounded-lg mt-1">
            </div>
            <label class="flex items-center text-sm text-gray-600">
                <input type="checkbox" name="remember" class="mr-2"> Ingat saya
            </label>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700">Masuk</button>
        </form>

        <p class="text-sm text-gray-600 mt-6 text-center">
            Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:underline">Daftar</a>
        </p>
    </div>
</body>
</html>