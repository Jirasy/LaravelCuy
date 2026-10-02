<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pendaftaran Ekskul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Data Pendaftaran Ekskul</h1>
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Kembali ke Katalog</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3">Siswa</th>
                        <th class="p-3">Kelas</th>
                        <th class="p-3">Ekskul</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Alasan</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftarans as $p)
                        <tr class="border-t">
                            <td class="p-3">{{ $p->user->name ?? '-' }}</td>
                            <td class="p-3">{{ $p->user->kelas ?? '-' }}</td>
                            <td class="p-3">{{ $p->ekstrakurikuler->nama_ekstrakurikuler ?? '-' }}</td>
                            <td class="p-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $p->status === 'diterima' ? 'bg-green-100 text-green-800' : ($p->status === 'ditolak' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td class="p-3">{{ $p->alasan ?? '-' }}</td>
                            <td class="p-3">
                                <form action="{{ route('pendaftaran.updateStatus', $p->id) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="alasan" placeholder="Alasan (opsional)" class="border p-1 rounded text-xs">
                                    <button name="status" value="diterima" class="bg-green-600 text-white px-2 py-1 rounded text-xs">Terima</button>
                                    <button name="status" value="ditolak" class="bg-red-600 text-white px-2 py-1 rounded text-xs">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500">Belum ada pendaftaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>