<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Ekskul</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Jenis Ekskul</h1>
            <p class="text-gray-600 text-sm">
                Kategori ekskul di sekolah: OSIS, MPK, Pramuka, PMR, Paskibra, dan seterusnya. Jenis yang ditandai
                <strong>hanya satu</strong> hanya boleh dimiliki satu ekskul. Pendaftaran lewat seleksi CaSis saat ini khusus OSIS;
                jenis lain memakai pendaftaran biasa (daftar lalu disetujui pembina).
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
            @foreach($daftar as $j)
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <form action="{{ route('jenis-ekskul.update', $j) }}" method="POST" class="flex flex-wrap items-end gap-3">
                        @csrf
                        @method('PATCH')
                        <div class="flex-1 min-w-[10rem]">
                            <label class="block text-xs font-semibold text-gray-600">Nama</label>
                            <input type="text" name="nama" value="{{ $j->nama }}" maxlength="60" required class="w-full border p-2 rounded-lg text-sm">
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \App\Models\JenisEkskul::ALUR[$j->alur_pendaftaran] ?? $j->alur_pendaftaran }}
                                &middot; {{ $j->ekskuls_count }} ekskul
                                @if($j->bawaan) &middot; bawaan sistem @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600">Urutan</label>
                            <input type="number" name="urutan" value="{{ $j->urutan }}" min="0" max="99" class="w-20 border p-2 rounded-lg text-sm">
                        </div>
                        <label class="flex items-center gap-1 text-sm text-gray-700">
                            <input type="checkbox" name="hanya_satu" value="1" {{ $j->hanya_satu ? 'checked' : '' }} {{ $j->kode === 'osis' ? 'disabled' : '' }}> Hanya satu
                        </label>
                        <label class="flex items-center gap-1 text-sm text-gray-700">
                            <input type="checkbox" name="aktif" value="1" {{ $j->aktif ? 'checked' : '' }} {{ $j->bawaan ? 'disabled' : '' }}> Aktif
                        </label>
                        <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Simpan</button>
                    </form>

                    @unless($j->bawaan)
                        <form action="{{ route('jenis-ekskul.destroy', $j) }}" method="POST" class="mt-2" onsubmit="return confirm('Hapus jenis {{ e($j->nama) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">Hapus jenis ini</button>
                        </form>
                    @endunless
                </div>
            @endforeach
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="font-bold text-gray-800 mb-3">Tambah jenis baru</h2>
            <form action="{{ route('jenis-ekskul.store') }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[10rem]">
                    <label class="block text-xs font-semibold text-gray-600">Nama jenis</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" maxlength="60" required placeholder="Contoh: Paduan Suara" class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600">Urutan</label>
                    <input type="number" name="urutan" value="{{ old('urutan', 50) }}" min="0" max="99" class="w-20 border p-2 rounded-lg text-sm">
                </div>
                <label class="flex items-center gap-1 text-sm text-gray-700">
                    <input type="checkbox" name="hanya_satu" value="1" {{ old('hanya_satu') ? 'checked' : '' }}> Hanya satu
                </label>
                <button type="submit" class="bg-emerald-600 text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700">Tambah</button>
            </form>
        </div>
    </div>
</body>
</html>
