<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Edit User</h1>
                <a href="{{ route('users.index') }}" class="text-gray-600 hover:underline text-sm">← Batal</a>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border p-2 rounded-lg mt-1">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border p-2 rounded-lg mt-1">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">NISN <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <input type="text" name="nisn" value="{{ old('nisn', $user->nisn) }}" class="w-full border p-2 rounded-lg mt-1">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Kelas <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <input type="text" name="kelas" value="{{ old('kelas', $user->kelas) }}" class="w-full border p-2 rounded-lg mt-1">
                    </div>
                </div>

                <div class="border-t pt-4">
                    <p class="text-sm font-semibold text-gray-700">Reset Password <span class="text-gray-400 font-normal">(kosongkan kalau tidak ingin mengubah)</span></p>
                    <div class="grid grid-cols-2 gap-4 mt-2">
                        <div>
                            <label class="block text-xs text-gray-500">Password baru</label>
                            <input type="password" name="password" minlength="6" autocomplete="new-password" class="w-full border p-2 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Ulangi password baru</label>
                            <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full border p-2 rounded-lg">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-4">
                    <a href="{{ route('users.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Batal</a>
                    <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-yellow-600">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>