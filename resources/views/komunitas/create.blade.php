<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Komunitas</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">🌱 Buat Komunitas</h1>
            <p class="text-gray-600 text-sm">Komunitas adalah ekskul yang baru lahir. Admin akan meresmikannya jadi ekskul dan menentukan tipenya (biasa, seleksi, atau organisasi).</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('komunitas.store') }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4" data-aos="fade-up">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama komunitas</label>
                <input type="text" name="nama_ekstrakurikuler" value="{{ old('nama_ekstrakurikuler') }}" maxlength="255" required class="w-full border p-2 rounded-lg mt-1">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Deskripsi <span class="font-normal text-gray-400">(opsional)</span></label>
                <textarea name="deskripsi" rows="4" maxlength="2000" class="w-full border p-2 rounded-lg mt-1">{{ old('deskripsi') }}</textarea>
            </div>
            <p class="text-xs text-gray-500">
                @if(auth()->user()->isPembina())
                    Kamu otomatis menjadi pembina komunitas ini. Jadwal bisa diatur lewat Edit Ekskul setelah komunitas dibuat.
                @else
                    Kamu otomatis menjadi anggota pertama komunitas ini. Pembina dan jadwal ditentukan admin saat meresmikan.
                @endif
            </p>
            <div class="flex gap-2">
                <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-emerald-700">Buat komunitas</button>
                <a href="{{ route('ekstrakurikuler.index') }}" class="px-5 py-2 text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>
