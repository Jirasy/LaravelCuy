<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Ekskul</title>
    @include('partials.head')
</head>
<body class="app-bg p-3 sm:p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')
        @include('partials.tab-pengelompokan')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Kategori Ekskul</h1>
            <p class="text-gray-600 text-sm">
                Kategori adalah label topik (mis. <strong>Bela Diri</strong>, <strong>Seni &amp; Olahraga</strong>). Satu ekskul boleh punya
                lebih dari satu kategori, dan siswa bisa menyaring katalog berdasarkan kategori. Kategori berbeda dengan
                <a href="{{ route('jenis-ekskul.index') }}" class="underline">tipe ekskul</a>, yang menentukan cara masuk. Kategori yang
                dinonaktifkan tidak bisa dipilih lagi, tetapi label lama di ekskul tetap tampil.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                @foreach($errors->all() as $galat)<p>{{ $galat }}</p>@endforeach
            </div>
        @endif

        <div class="space-y-3 mb-8">
            @forelse($daftar as $k)
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <form action="{{ route('kategori-ekskul.update', $k) }}" method="POST" class="flex flex-wrap items-end gap-3">
                        @csrf
                        @method('PATCH')
                        <div class="flex-1 min-w-[10rem]">
                            <label class="block text-xs font-semibold text-gray-600">Nama</label>
                            <input type="text" name="nama" value="{{ $k->nama }}" maxlength="60" required class="w-full border p-2 rounded-lg text-sm">
                            <p class="text-xs text-gray-500 mt-1">{{ $k->ekskuls_count }} ekskul</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600">Urutan</label>
                            <input type="number" name="urutan" value="{{ $k->urutan }}" min="0" max="99" class="w-20 border p-2 rounded-lg text-sm">
                        </div>
                        <label class="flex items-center gap-1 text-sm text-gray-700">
                            <input type="checkbox" name="aktif" value="1" {{ $k->aktif ? 'checked' : '' }}> Aktif
                        </label>
                        <x-tombol jenis="sukses" type="submit">Simpan</x-tombol>
                    </form>

                    <form action="{{ route('kategori-ekskul.destroy', $k) }}" method="POST" class="mt-2" onsubmit="return confirm({{ json_encode('Hapus kategori '.$k->nama.'?') }})">
                        @csrf
                        @method('DELETE')
                        <x-tombol jenis="hapus" type="submit">Hapus kategori</x-tombol>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada kategori. Tambahkan lewat form di bawah.</p>
            @endforelse
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="font-bold text-gray-800 mb-3">Tambah kategori baru</h2>
            <form action="{{ route('kategori-ekskul.store') }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[10rem]">
                    <label class="block text-xs font-semibold text-gray-600">Nama kategori</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" maxlength="60" required placeholder="Contoh: Seni Pertunjukan" class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600">Urutan</label>
                    <input type="number" name="urutan" value="{{ old('urutan', 50) }}" min="0" max="99" class="w-20 border p-2 rounded-lg text-sm">
                </div>
                <x-tombol jenis="sukses" type="submit">Tambah</x-tombol>
            </form>
        </div>
    </div>
</body>
</html>
