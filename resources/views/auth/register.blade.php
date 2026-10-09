<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Ekstrakurikuler</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Daftar Akun Siswa</h1>
        <p class="text-gray-600 text-sm mb-6">Buat akun untuk mendaftar ekstrakurikuler.</p>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.process') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border p-2 rounded-lg mt-1">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border p-2 rounded-lg mt-1">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">NISN</label>
                    <input type="text" name="nisn" value="{{ old('nisn') }}" required class="w-full border p-2 rounded-lg mt-1">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Kelas</label>
                    <input type="text" name="kelas" value="{{ old('kelas') }}" placeholder="XII PPLG" required class="w-full border p-2 rounded-lg mt-1">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Password</label>
                <input type="password" name="password" required minlength="6" class="w-full border p-2 rounded-lg mt-1">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Ulangi Password</label>
                <input type="password" name="password_confirmation" required class="w-full border p-2 rounded-lg mt-1">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700">Buat Akun</button>
        </form>

        <p class="text-sm text-gray-600 mt-6 text-center">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">Masuk</a>
        </p>
    </div>
</body>
</html>