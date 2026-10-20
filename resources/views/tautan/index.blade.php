<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tautan Sekolah</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Tautan Sekolah</h1>
            <p class="text-gray-600 text-sm">Tautan ini muncul di menu Pesan/Task di navbar untuk semua pengguna. Hanya admin yang bisa mengaturnya.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('tautan.store') }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6" data-aos="fade-up">
            @csrf
            <h2 class="font-bold text-gray-800">Tambah tautan</h2>
            @include('tautan._form')
        </form>

        <div class="space-y-2">
            @forelse($tautans as $t)
                <div class="bg-white px-4 py-3 rounded-xl border border-gray-200 flex items-center justify-between gap-3" data-aos="fade-up">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800">{{ $t->ikon }} {{ $t->label }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $t->url }}</p>
                    </div>
                    <form action="{{ route('tautan.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus tautan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                    </form>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada tautan sekolah.</div>
            @endforelse
        </div>
    </div>
</body>
</html>