<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Prestasi</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Edit Prestasi</h1>
                <a href="{{ route('ekstrakurikuler.show', ['id' => $prestasi->ekstrakurikuler_id, 'tab' => 'prestasi']) }}" class="text-gray-600 hover:underline text-sm">← Batal</a>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('ekskul-prestasi.update', $prestasi->id) }}" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                @include('ekstrakurikuler._form_prestasi', ['anggotaOpsi' => $anggotaOpsi, 'prestasi' => $prestasi])
                <div class="flex justify-end space-x-2 pt-2">
                    <a href="{{ route('ekstrakurikuler.show', ['id' => $prestasi->ekstrakurikuler_id, 'tab' => 'prestasi']) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Batal</a>
                    <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-yellow-600">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>