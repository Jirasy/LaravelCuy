<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-6xl mx-auto">
        @include('partials.navbar')

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Kelola User</h1>
                <p class="text-gray-600 text-sm">
                    {{ $users->count() }} user &middot; {{ $users->where('is_active', true)->count() }} aktif
                    &middot; {{ $users->where('is_active', false)->count() }} nonaktif
                </p>
            </div>
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Katalog</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto" data-aos="fade-up">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Kelas / NISN</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        @php $saya = $u->id === auth()->id(); @endphp
                        <tr class="border-t align-top {{ $u->is_active ? '' : 'bg-gray-50 text-gray-500' }}">
                            <td class="p-3 font-semibold">{{ $u->name }}</td>
                            <td class="p-3">{{ $u->email }}</td>
                            <td class="p-3">{{ $u->kelas ?? '-' }}<br><span class="text-xs text-gray-500">{{ $u->nisn }}</span></td>

                            <td class="p-3">
                                @if($saya)
                                    <span class="text-xs">{{ ucfirst($u->role) }} (akunmu)</span>
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

                            <td class="p-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $u->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td class="p-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('users.edit', $u->id) }}" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-yellow-600">Edit</a>

                                    @unless($saya)
                                        <form action="{{ route('users.toggle', $u->id) }}" method="POST"
                                              onsubmit="return confirm('{{ $u->is_active ? 'Nonaktifkan akun ini? User tidak bisa login sampai diaktifkan lagi.' : 'Aktifkan kembali akun ini?' }}')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="{{ $u->is_active ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }} text-white px-3 py-1 rounded text-xs font-semibold">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST"
                                              onsubmit="return confirm('Hapus akun {{ e($u->name) }} secara permanen? Riwayat pendaftarannya tetap tersimpan tanpa akun.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>