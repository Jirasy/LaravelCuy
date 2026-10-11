<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Ekstrakurikuler</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        @include('partials.navbar')
        
        <!-- Header & Tombol Aksi -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Daftar Ekstrakurikuler</h1>
                <p class="text-gray-600 text-sm">Pilih dan daftar ekstrakurikuler sekolahmu di sini.</p>
            </div>
            <div class="space-x-2">
                @if(auth()->user()->isStaff())
                <a href="{{ route('ekstrakurikuler.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700">
                    + Tambah Ekskul
                </a>
                @endif
                <a href="{{ route('pendaftaran.index') }}" class="bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-800">
                    {{ auth()->user()->isStaff() ? 'Lihat Pendaftaran' : 'Pendaftaran Saya' }}
                </a>
            </div>
        </div>

        <!-- Notifikasi Pesan -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Grid Kartu Ekstrakurikuler -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($ekskuls as $ekskul)
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col justify-between">
                    <div>
                        <!-- Status Badge -->
                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full {{ $ekskul->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($ekskul->status) }}
                        </span>

                        <h2 class="text-xl font-bold text-gray-900 mt-2">{{ $ekskul->nama_ekstrakurikuler }}</h2>
                        <p class="text-sm text-gray-600 mt-1">Pembina: <strong>{{ $ekskul->pembina }}</strong></p>
                        <p class="text-sm text-gray-600">Jadwal: {{ $ekskul->hari_label }}, {{ $ekskul->jam_mulai }} - {{ $ekskul->jam_selesai }} WIB</p>
                        <p class="text-gray-700 text-sm mt-3">{{ $ekskul->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                    </div>

                    <!-- Tombol Aksi per Kartu -->
                    <div class="mt-6 pt-4 border-t border-gray-100 space-y-2">
                        <a href="{{ route('pendaftaran.create', ['ekstrakurikuler_id' => $ekskul->id]) }}" class="block w-full text-center bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            Daftar Sekarang
                        </a>

                        @if(auth()->user()->isStaff())
                        <div class="flex space-x-2 pt-1">
                            <a href="{{ route('ekstrakurikuler.edit', $ekskul->id) }}" class="w-1/2 text-center bg-yellow-500 text-white py-1.5 rounded-lg text-sm font-semibold hover:bg-yellow-600">
                                Edit
                            </a>
                            <form action="{{ route('ekstrakurikuler.destroy', $ekskul->id) }}" method="POST" class="w-1/2" onsubmit="return confirm('Yakin hapus ekskul ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 text-white py-1.5 rounded-lg text-sm font-semibold hover:bg-red-700">
                                    Hapus
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white p-8 text-center rounded-xl border border-gray-200 text-gray-500">
                    Belum ada data ekstrakurikuler. Klik tombol <strong>+ Tambah Ekskul</strong> untuk menambah data.
                </div>
            @endforelse
        </div>

    </div>
</body>
</html>