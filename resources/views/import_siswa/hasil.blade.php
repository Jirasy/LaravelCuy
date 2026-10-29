<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Import Siswa</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        <div class="flex flex-wrap justify-between items-center gap-3 mb-6" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Hasil Import Siswa</h1>
                <p class="text-gray-600 text-sm">Berkas: {{ $data['berkas'] ?? '-' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('import-siswa.create') }}" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800">Import lagi</a>
                <form action="{{ route('import-siswa.unduh', $rekap) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700">Unduh CSV</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif

        <div class="bg-amber-50 border border-amber-300 text-amber-900 text-sm px-4 py-3 rounded-lg mb-4">
            Password sementara disimpan terenkripsi selama {{ \App\Models\KredensialSementara::MASA_BERLAKU_HARI }} hari dan terhapus otomatis
            begitu siswa mengganti passwordnya. Bagikan ke siswa lewat jalur yang aman, jangan ditempel di grup umum.
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-4">
            <div class="flex flex-wrap justify-between items-center gap-3 mb-3">
                <h2 class="font-semibold text-gray-900">{{ $siswa->count() }} akun siswa baru</h2>
                @if(! $dibuka)
                    <form action="{{ route('import-siswa.tampilkan', $rekap) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-indigo-700">Tampilkan password</button>
                    </form>
                @else
                    <span class="text-xs text-gray-500">Password tampil sekali. Muat ulang halaman untuk menyembunyikannya.</span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-3">Nama</th>
                            <th class="py-2 pr-3">Kelas</th>
                            <th class="py-2 pr-3">Email login</th>
                            <th class="py-2">Password sementara</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswa as $s)
                            @php $k = $kredensial->get($s->id); @endphp
                            <tr class="border-b last:border-0">
                                <td class="py-2 pr-3 font-medium text-gray-900">{{ $s->name }}</td>
                                <td class="py-2 pr-3 text-gray-600">{{ $s->kelas ?? '-' }}</td>
                                <td class="py-2 pr-3 text-gray-700">{{ $s->email }}</td>
                                <td class="py-2">
                                    @if(! $k)
                                        <span class="text-xs text-gray-500">Sudah diganti siswa</span>
                                    @elseif($k->sudahKedaluwarsa())
                                        <span class="text-xs text-red-600">Kedaluwarsa, reset lewat Kelola User</span>
                                    @elseif($dibuka && ($password[$s->id] ?? null))
                                        <code class="font-mono font-bold text-gray-900 select-all">{{ $password[$s->id] }}</code>
                                    @else
                                        <span class="text-gray-400">••••••••••••</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(! empty($data['dilewati']))
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h2 class="font-semibold text-gray-900 mb-2">{{ count($data['dilewati']) }} nama dilewati</h2>
                <ul class="list-disc pl-5 text-sm text-gray-700 space-y-0.5">
                    @foreach($data['dilewati'] as $d)
                        <li>{{ $d['nama'] }} <span class="text-gray-500">— {{ $d['alasan'] }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(($data['diabaikan'] ?? 0) > 0)
            <p class="text-xs text-gray-500 mt-3">{{ $data['diabaikan'] }} baris di berkas tidak dikenali sebagai nama dan diabaikan.</p>
        @endif
    </div>
</body>
</html>
