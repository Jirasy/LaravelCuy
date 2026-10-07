<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftaran Ekskul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Data Pendaftaran Ekskul</h1>
            <div class="space-x-2">
                <a href="{{ route('pendaftaran.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700">+ Daftar Baru</a>
                <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Katalog</a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3">Pendaftar</th>
                        <th class="p-3">Kelas / NISN</th>
                        <th class="p-3">Ekskul</th>
                        <th class="p-3">Alasan Mendaftar</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftarans as $p)
                        <tr class="border-t align-top">
                            <td class="p-3">
                                <div class="font-semibold">{{ $p->nama_pendaftar }}</div>
                                <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full
                                    {{ $p->peran === 'admin' ? 'bg-purple-100 text-purple-800' : ($p->peran === 'pembina' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($p->peran) }}
                                </span>
                            </td>
                            <td class="p-3">{{ $p->kelas ?? '-' }}<br><span class="text-xs text-gray-500">{{ $p->nisn ?? '' }}</span></td>
                            <td class="p-3">{{ $p->ekstrakurikuler->nama_ekstrakurikuler ?? '-' }}</td>
                            <td class="p-3 max-w-xs">{{ $p->alasan_mendaftar ?? '-' }}</td>
                            <td class="p-3">
                                <form action="{{ route('pendaftaran.updateStatus', $p->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="warnaStatus(this)" class="status-select border p-1.5 rounded text-xs font-semibold w-full
                                        {{ $p->status === 'diterima' ? 'bg-green-100 text-green-800 border-green-300' : ($p->status === 'ditolak' ? 'bg-red-100 text-red-800 border-red-300' : 'bg-yellow-100 text-yellow-800 border-yellow-300') }}">
                                        <option value="pending"  {{ $p->status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="diterima" {{ $p->status === 'diterima' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="ditolak"  {{ $p->status === 'ditolak' ? 'selected' : '' }}>Tidak Disetujui</option>
                                    </select>
                                    <textarea name="alasan" rows="2" placeholder="Alasan / catatan" class="border p-1 rounded text-xs w-full">{{ $p->alasan }}</textarea>
                                    <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded text-xs hover:bg-gray-900">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-gray-500">Belum ada pendaftaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const WARNA = {
            pending:  ['bg-yellow-100', 'text-yellow-800', 'border-yellow-300'],
            diterima: ['bg-green-100',  'text-green-800',  'border-green-300'],
            ditolak:  ['bg-red-100',    'text-red-800',    'border-red-300'],
        };
        function warnaStatus(el) {
            Object.values(WARNA).forEach(k => el.classList.remove(...k));
            el.classList.add(...WARNA[el.value]);
        }
    </script>
</body>
</html>