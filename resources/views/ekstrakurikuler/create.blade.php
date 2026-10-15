<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Ekstrakurikuler</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Ekstrakurikuler Baru</h1>
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Kembali</a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc pl-5 text-sm">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ekstrakurikuler.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama Ekstrakurikuler</label>
                <input type="text" name="nama_ekstrakurikuler" placeholder="Contoh: Basket" required class="w-full border p-2 rounded-lg mt-1 focus:ring focus:ring-indigo-200">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama Pembina</label>
                <input type="text" name="pembina" placeholder="Contoh: Pak Herman" required class="w-full border p-2 rounded-lg mt-1 focus:ring focus:ring-indigo-200">
            </div>

            @include('ekstrakurikuler._jadwal', ['awal' => []])

            <div>
                <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat kegiatan..." class="w-full border p-2 rounded-lg mt-1"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <a href="{{ route('ekstrakurikuler.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Batal</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700">Simpan Data</button>
            </div>
        </form>
    </div>
</body>
</html>