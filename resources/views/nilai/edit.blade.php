<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai {{ $pendaftaran->nama_pendaftar }}</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="flex flex-wrap justify-between items-center gap-3 mb-6" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Beri Nilai</h1>
                <p class="text-gray-600 text-sm">{{ $pendaftaran->nama_pendaftar }} &middot; {{ $ekskul->nama_ekstrakurikuler }}</p>
            </div>
            <a href="{{ route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'nilai']) }}" class="text-gray-600 hover:underline text-sm">← Kembali</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('nilai.store', $pendaftaran->id) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4" data-aos="fade-up">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700">Periode</label>
                <input type="text" name="periode" list="saran-periode" value="{{ old('periode', $periode) }}" required maxlength="60" class="w-full border p-2 rounded-lg mt-1">
                <datalist id="saran-periode">
                    <option value="{{ \App\Models\EkskulNilai::periodeSaatIni() }}">
                    @foreach($riwayat as $r)<option value="{{ $r->periode }}">@endforeach
                </datalist>
                <p class="text-xs text-gray-500 mt-1">Kalau periode ini sudah pernah dinilai, nilainya akan diperbarui.@if($nilai) <strong>Periode ini sudah punya nilai.</strong>@endif</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                @foreach(\App\Models\EkskulNilai::ASPEK as $kolom => $label)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ $label }} <span class="font-normal text-gray-400">(0-100)</span></label>
                        <input type="number" name="{{ $kolom }}" min="0" max="100" required value="{{ old($kolom, $nilai->{$kolom} ?? '') }}" class="aspek w-full border p-2 rounded-lg mt-1">
                    </div>
                @endforeach
            </div>

            <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2 text-sm text-indigo-900">
                Rata-rata: <strong id="hasil-rata">-</strong>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Saran <span class="font-normal text-gray-400">(terlihat oleh siswa)</span></label>
                <textarea name="saran" rows="3" maxlength="2000" placeholder="Apa yang bisa ditingkatkan atau dipertahankan?" class="w-full border p-2 rounded-lg mt-1">{{ old('saran', $nilai->saran ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Kritik membangun <span class="font-normal text-gray-400">(terlihat oleh siswa)</span></label>
                <textarea name="kritik" rows="3" maxlength="2000" placeholder="Sampaikan dengan sopan dan jelas." class="w-full border p-2 rounded-lg mt-1">{{ old('kritik', $nilai->kritik ?? '') }}</textarea>
            </div>

            <p class="text-xs text-gray-500">Nilai, saran, dan kritik hanya terlihat oleh siswa yang bersangkutan, admin, dan pembina ekskul ini.</p>

            <div class="flex justify-end gap-2">
                <a href="{{ route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'nilai']) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Batal</a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700">Simpan Nilai</button>
            </div>
        </form>

        @if($riwayat->isNotEmpty())
            <h2 class="text-sm font-semibold text-gray-500 uppercase mt-8 mb-2">Riwayat nilai</h2>
            <div class="space-y-2">
                @foreach($riwayat as $r)
                    <div class="bg-white px-4 py-3 rounded-lg border border-gray-200 flex items-center justify-between gap-3">
                        <a href="{{ route('nilai.edit', ['pendaftaran' => $pendaftaran->id, 'periode' => $r->periode]) }}" class="text-sm text-gray-800 hover:underline">
                            {{ $r->periode }} &middot; <strong>{{ number_format($r->rata_rata, 1) }}</strong> ({{ $r->predikat }})
                        </a>
                        <form action="{{ route('nilai.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus nilai periode ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        (function () {
            var kolom = document.querySelectorAll('.aspek'), hasil = document.getElementById('hasil-rata');
            function hitung() {
                var v = Array.prototype.map.call(kolom, function (i) { return parseFloat(i.value); });
                if (v.some(isNaN)) { hasil.textContent = '-'; return; }
                var r = v.reduce(function (a, b) { return a + b; }, 0) / v.length;
                var p = r >= 90 ? 'A' : r >= 80 ? 'B' : r >= 70 ? 'C' : r >= 60 ? 'D' : 'E';
                hasil.textContent = r.toFixed(1) + ' (predikat ' + p + ')';
            }
            kolom.forEach(function (i) { i.addEventListener('input', hitung); });
            hitung();
        })();
    </script>
</body>
</html>