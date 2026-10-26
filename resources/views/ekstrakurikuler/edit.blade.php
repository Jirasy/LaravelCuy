<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ekstrakurikuler</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Ekstrakurikuler</h1>
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Batal</a>
        </div>

        <!-- Action mengarah ke route manual update -->
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc pl-5 text-sm">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ekstrakurikuler.update', $ekstrakurikuler->id) }}" method="POST" class="space-y-4">
    @csrf
    @method('PUT') <!-- 🔴 WAJIB ADA agar Laravel mengenali request ini sebagai UPDATE -->

    <div>
        <label class="block text-sm font-semibold text-gray-700">Nama Ekstrakurikuler</label>
        <input type="text" name="nama_ekstrakurikuler" value="{{ old('nama_ekstrakurikuler', $ekstrakurikuler->nama_ekstrakurikuler) }}" required class="w-full border p-2 rounded-lg mt-1">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700">Nama Pembina</label>
        <input type="text" name="pembina" value="{{ old('pembina', $ekstrakurikuler->pembina) }}" required class="w-full border p-2 rounded-lg mt-1">
    </div>

    @if(auth()->user()->isAdmin())
    <div>
        <label class="block text-sm font-semibold text-gray-700">Jenis ekskul</label>
        <select name="jenis" class="w-full border p-2 rounded-lg mt-1">
            <option value="ekskul" {{ old('jenis', $ekstrakurikuler->jenis ?? 'ekskul') === 'ekskul' ? 'selected' : '' }}>Ekskul biasa</option>
            <option value="osis" {{ old('jenis', $ekstrakurikuler->jenis ?? 'ekskul') === 'osis' ? 'selected' : '' }}>OSIS (pendaftaran lewat seleksi CaSis)</option>
        </select>
        <p class="text-xs text-gray-500 mt-1">Hanya satu ekskul yang boleh bertipe OSIS.</p>
    </div>
    @endif

    @php
        $awalJadwal = $ekstrakurikuler->haris->mapWithKeys(fn ($x) => [
            $x->hari => ['jam_mulai' => substr($x->jam_mulai, 0, 5), 'jam_selesai' => substr($x->jam_selesai, 0, 5)],
        ])->all();
    @endphp
    @include('ekstrakurikuler._jadwal', ['awal' => $awalJadwal])

    <div>
        <label class="block text-sm font-semibold text-gray-700">Status</label>
        <select name="status" class="w-full border p-2 rounded-lg mt-1">
            <option value="aktif" {{ $ekstrakurikuler->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ $ekstrakurikuler->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
        <textarea name="deskripsi" rows="3" class="w-full border p-2 rounded-lg mt-1">{{ old('deskripsi', $ekstrakurikuler->deskripsi) }}</textarea>
    </div>

    <div class="flex justify-end space-x-2 pt-4">
        <a href="{{ route('ekstrakurikuler.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Batal</a>
        <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-yellow-600">Perbarui Data</button>
    </div>
</form>
    </div>

    @include('ekstrakurikuler._pembina')
</body>
</html>