@php
    $staff = auth()->user()->isStaff();
    $me = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran Ekskul</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Form Pendaftaran Ekskul</h1>
                <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Kembali</a>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @unless($staff)
                <div class="bg-indigo-50 border border-indigo-200 text-indigo-800 px-4 py-3 rounded-lg mb-4 text-sm">
                    Kamu mendaftar sebagai <strong>{{ $me->name }}</strong> ({{ $me->kelas }}, NISN {{ $me->nisn }}).
                    Data ini diambil dari akunmu.
                </div>
            @endunless

            <form action="{{ route('pendaftaran.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Ekstrakurikuler</label>
                    <select name="ekstrakurikuler_id" required class="w-full border p-2 rounded-lg mt-1">
                        <option value="">-- Pilih ekskul --</option>
                        @foreach($ekskuls as $e)
                            <option value="{{ $e->id }}" {{ old('ekstrakurikuler_id', $terpilih) == $e->id ? 'selected' : '' }}>
                                {{ $e->nama_ekstrakurikuler }} ({{ $e->hari_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($staff)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Nama Pendaftar</label>
                            <input type="text" name="nama_pendaftar" value="{{ old('nama_pendaftar') }}" required placeholder="Nama lengkap" class="w-full border p-2 rounded-lg mt-1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Mendaftar sebagai</label>
                            <select name="peran" required class="w-full border p-2 rounded-lg mt-1">
                                <option value="siswa"   {{ old('peran', 'siswa') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                <option value="admin"   {{ old('peran') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="pembina" {{ old('peran') == 'pembina' ? 'selected' : '' }}>Pembina</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Kelas <span class="text-gray-400 font-normal">(wajib untuk siswa)</span></label>
                            <input type="text" name="kelas" value="{{ old('kelas') }}" placeholder="Contoh: XII PPLG" class="w-full border p-2 rounded-lg mt-1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">NISN <span class="text-gray-400 font-normal">(opsional)</span></label>
                            <input type="text" name="nisn" value="{{ old('nisn') }}" class="w-full border p-2 rounded-lg mt-1">
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Alasan Mendaftar</label>
                    <textarea name="alasan_mendaftar" rows="3" placeholder="Kenapa ingin ikut ekskul ini?" class="w-full border p-2 rounded-lg mt-1">{{ old('alasan_mendaftar') }}</textarea>
                </div>

                @if(session('bentrok_baru'))
                    <div class="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-3 rounded-lg text-sm" role="alert">
                        <p class="font-bold mb-1">⚠️ Jadwal ekskul ini bentrok dengan ekskul lain milikmu</p>
                        <ul class="list-disc pl-5 mb-3">
                            @foreach(session('bentrok_baru') as $ringkasan)<li>{{ $ringkasan }}</li>@endforeach
                        </ul>
                        <p class="mb-3">Kalau tetap mendaftar, kamu bisa memilih ekskul yang dihadiri atau mengatur pembagian waktu di profilmu setelah pendaftaran disetujui.</p>
                        <label class="flex items-start gap-2 font-semibold cursor-pointer">
                            <input type="checkbox" name="konfirmasi_bentrok" value="1" required class="mt-1">
                            <span>Saya mengerti jadwalnya bentrok dan tetap ingin mendaftar</span>
                        </label>
                    </div>
                @endif

                <div class="flex justify-end space-x-2 pt-4">
                    <a href="{{ route('ekstrakurikuler.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Batal</a>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700">Kirim Pendaftaran</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>