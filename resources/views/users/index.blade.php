<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto">
        @include('partials.navbar')

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Kelola User</h1>
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Katalog</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Kelas / NISN</th>
                        <th class="p-3">Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr class="border-t">
                            <td class="p-3 font-semibold">{{ $u->name }}</td>
                            <td class="p-3">{{ $u->email }}</td>
                            <td class="p-3">{{ $u->kelas ?? '-' }}<br><span class="text-xs text-gray-500">{{ $u->nisn }}</span></td>
                            <td class="p-3">
                                @if($u->id === auth()->id())
                                    <span class="text-gray-500 text-xs">{{ ucfirst($u->role) }} (akunmu)</span>
                                @else
                                    <form action="{{ route('users.updateRole', $u->id) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="border p-1 rounded text-xs">
                                            <option value="siswa"   {{ $u->role === 'siswa' ? 'selected' : '' }}>Siswa</option>
                                            <option value="pembina" {{ $u->role === 'pembina' ? 'selected' : '' }}>Pembina</option>
                                            <option value="admin"   {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded text-xs hover:bg-gray-900">Simpan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>