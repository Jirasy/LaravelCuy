<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pr->judul }}</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        <div class="flex flex-wrap justify-between items-start gap-3 mb-6" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $pr->judul }}</h1>
                <p class="text-gray-600 text-sm">
                    {{ $ekskul->nama_ekstrakurikuler }} &middot; nilai maks {{ $pr->nilai_maks }}
                    @if($pr->tenggat) &middot; tenggat {{ $pr->tenggat->format('d-m-Y') }} @endif
                    &middot; {{ $pengumpulans->count() }} terkumpul
                </p>
            </div>
            <a href="{{ route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'materi']) }}" class="text-gray-600 hover:underline text-sm">← Kembali</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if($pr->deskripsi)
            <div class="bg-white p-5 rounded-xl border border-gray-200 mb-6 text-sm text-gray-800 whitespace-pre-line">{{ $pr->deskripsi }}</div>
        @endif

        <div class="space-y-3">
            @forelse($anggota as $a)
                @php $p = $pengumpulans->get($a->id); @endphp
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $a->nama_pendaftar }}</p>
                            <p class="text-xs text-gray-500">{{ $a->kelas ?? '-' }}@if(!$a->akses_materi) &middot; belum diberi akses materi @endif</p>
                        </div>
                        <div>
                            @if(!$p)
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Belum mengumpulkan</span>
                            @elseif($p->sudah_dinilai)
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Dinilai: {{ $p->nilai }}/{{ $pr->nilai_maks }}</span>
                            @else
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $p->terlambat ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">{{ $p->terlambat ? 'Terlambat' : 'Menunggu nilai' }}</span>
                            @endif
                        </div>
                    </div>

                    @if($p)
                        <p class="text-xs text-gray-500 mt-2">Dikumpulkan {{ $p->dikumpulan_pada ?? $p->dikumpulkan_pada->format('d-m-Y H:i') }}</p>

                        @if($p->jawaban)
                            <div class="mt-2 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-800 whitespace-pre-line">{{ $p->jawaban }}</div>
                        @endif
                        @if($p->berkas)
                            <p class="text-sm mt-2">📎 <a href="{{ route('pr.berkas', $p->id) }}" class="text-indigo-600 hover:underline">{{ $p->nama_berkas }}</a></p>
                        @endif

                        <form action="{{ route('pr.nilai', $p->id) }}" method="POST" class="mt-3 grid grid-cols-1 sm:grid-cols-4 gap-2 items-start">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="block text-xs text-gray-500">Nilai (0-{{ $pr->nilai_maks }})</label>
                                <input type="number" name="nilai" min="0" max="{{ $pr->nilai_maks }}" required value="{{ $p->nilai }}" class="w-full border p-2 rounded-lg text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-gray-500">Komentar untuk siswa</label>
                                <input type="text" name="komentar" maxlength="2000" value="{{ $p->komentar }}" class="w-full border p-2 rounded-lg text-sm">
                            </div>
                            <div class="sm:pt-5">
                                <button type="submit" class="w-full bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">{{ $p->sudah_dinilai ? 'Ubah nilai' : 'Simpan nilai' }}</button>
                            </div>
                        </form>
                    @endif
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada anggota aktif.</div>
            @endforelse
        </div>
    </div>
</body>
</html>