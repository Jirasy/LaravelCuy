<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi {{ $ekskul->nama_ekstrakurikuler }}</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        <div class="flex flex-wrap justify-between items-center gap-3 mb-6" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Absensi</h1>
                <p class="text-gray-600 text-sm">{{ $ekskul->nama_ekstrakurikuler }} &middot; {{ $pertemuan->tanggal_lengkap }}</p>
            </div>
            <a href="{{ route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'absensi']) }}" class="text-gray-600 hover:underline text-sm">← Kembali</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('absensi.update', $pertemuan->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 grid grid-cols-1 sm:grid-cols-4 gap-3" data-aos="fade-up">
                <div>
                    <label class="block text-xs text-gray-500">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', $pertemuan->tanggal->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Jam mulai</label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $pertemuan->jam_mulai ? substr($pertemuan->jam_mulai, 0, 5) : '') }}" class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Jam selesai</label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $pertemuan->jam_selesai ? substr($pertemuan->jam_selesai, 0, 5) : '') }}" class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Topik</label>
                    <input type="text" name="topik" value="{{ old('topik', $pertemuan->topik) }}" maxlength="200" placeholder="mis. Latihan passing" class="w-full border p-2 rounded-lg text-sm">
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto" data-aos="fade-up">
                <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-b border-gray-200">
                    <p class="text-sm font-semibold text-gray-700 mr-2">Tandai semua:</p>
                    @foreach(\App\Models\EkskulKehadiran::STATUS as $kode => $label)
                        <button type="button" data-semua="{{ $kode }}" class="px-3 py-1 text-xs font-semibold rounded-full border border-gray-300 text-gray-700 hover:bg-gray-100">{{ $label[0] }}</button>
                    @endforeach
                </div>

                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="p-3 w-10">#</th>
                            <th class="p-3">Nama</th>
                            <th class="p-3">Kehadiran</th>
                            <th class="p-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anggota as $a)
                            @php $dipilih = old('status.' . $a->id, $kehadiran->get($a->id)?->status ?? 'hadir'); @endphp
                            <tr class="border-t align-top">
                                <td class="p-3 text-gray-500">{{ $loop->iteration }}</td>
                                <td class="p-3">
                                    <span class="font-semibold">{{ $a->nama_pendaftar }}</span>
                                    <span class="block text-xs text-gray-500">{{ $a->kelas ?? '-' }}@if($a->status_anggota === 'selesai') &middot; purna tugas @elseif($a->status_anggota !== 'aktif') &middot; sudah keluar @endif</span>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                                        @foreach(\App\Models\EkskulKehadiran::STATUS as $kode => $label)
                                            <label class="inline-flex items-center gap-1 text-sm text-gray-800 cursor-pointer">
                                                <input type="radio" class="rs" name="status[{{ $a->id }}]" value="{{ $kode }}" {{ $dipilih === $kode ? 'checked' : '' }}>
                                                {{ $label[0] }}
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-3">
                                    <input type="text" name="catatan[{{ $a->id }}]" value="{{ old('catatan.' . $a->id, $kehadiran->get($a->id)?->catatan) }}" maxlength="200" placeholder="opsional" class="w-full border p-1.5 rounded text-xs">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada anggota aktif di ekskul ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'absensi']) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Batal</a>
                <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-emerald-700">Simpan Absensi</button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('[data-semua]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                document.querySelectorAll('.rs[value="' + tombol.dataset.semua + '"]').forEach(function (r) { r.checked = true; });
            });
        });
    </script>
</body>
</html>