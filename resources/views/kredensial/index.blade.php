<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kredensial Sementara</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Kredensial Sementara</h1>
            <p class="text-gray-600 text-sm">
                Password sementara akun pembina yang dibuat otomatis. Disimpan terenkripsi, berlaku {{ \App\Models\KredensialSementara::MASA_BERLAKU_HARI }} hari,
                dan terhapus otomatis begitu pembina mengganti passwordnya.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif

        <div class="space-y-3">
            @forelse($daftar as $k)
                @php $dibuka = session('kredensial_dibuka.id') === $k->id; @endphp
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex flex-wrap justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $k->user?->name ?? 'Akun dihapus' }}</p>
                            <p class="text-sm text-gray-600">{{ $k->user?->email }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Ekskul: {{ $k->ekskul?->nama_ekstrakurikuler ?? '-' }}
                                · Dibuat {{ $k->created_at->format('d-m-Y H:i') }} oleh {{ $k->pembuat?->name ?? '-' }}
                            </p>
                            <p class="text-xs mt-1 {{ $k->sudahKedaluwarsa() ? 'text-red-600' : 'text-gray-500' }}">
                                {{ $k->sudahKedaluwarsa() ? 'Kedaluwarsa' : 'Berlaku sampai' }} {{ $k->kedaluwarsa_pada->format('d-m-Y H:i') }}
                            </p>
                            @if($k->terakhir_dilihat_pada)
                                <p class="text-xs text-gray-500">Terakhir dibuka {{ $k->terakhir_dilihat_pada->format('d-m-Y H:i') }} oleh {{ $k->pelihat?->name ?? '-' }}</p>
                            @endif
                        </div>

                        <div class="flex items-start gap-2">
                            @unless($k->sudahKedaluwarsa())
                                <form action="{{ route('kredensial.tampilkan', $k) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-indigo-700">Tampilkan password</button>
                                </form>
                            @endunless
                            <form action="{{ route('kredensial.hapus', $k) }}" method="POST" onsubmit="return confirm('Hapus kredensial ini? Password tidak bisa dibuka lagi.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm font-semibold px-2 py-1.5">Hapus</button>
                            </form>
                        </div>
                    </div>

                    @if($dibuka)
                        <div class="mt-3 bg-amber-50 border border-amber-300 rounded-lg px-4 py-3">
                            <p class="text-xs text-amber-800 mb-1">Password sementara (hanya tampil sekali setelah tombol ditekan):</p>
                            <code class="text-lg font-mono font-bold text-gray-900 select-all">{{ session('kredensial_dibuka.password') }}</code>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">
                    Tidak ada kredensial sementara yang tersimpan.
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
