@php
    $warnaRek = ['lolos' => 'bg-green-100 text-green-800', 'pertimbangkan' => 'bg-amber-100 text-amber-800', 'tidak_lolos' => 'bg-red-100 text-red-800'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaSis {{ $casis->user->name ?? '' }}</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-3xl mx-auto">
        @include('partials.navbar')

        <div class="flex flex-wrap justify-between items-start gap-3 mb-6" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $casis->user->name ?? 'Pengguna dihapus' }}</h1>
                <p class="text-gray-600 text-sm">{{ $casis->user->kelas ?? '-' }} &middot; CaSis periode {{ $periode->nama }} &middot; {{ \App\Models\CasisPendaftaran::STATUS[$casis->status] }}</p>
            </div>
            <a href="{{ route('casis.index', ['periode' => $periode->id]) }}" class="text-gray-600 hover:underline text-sm">← Kembali</a>
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

        {{-- Berkas pendaftar --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4 mb-6" data-aos="fade-up">
            <div><p class="text-xs font-semibold text-gray-500 uppercase">Motivasi</p><p class="text-sm text-gray-800 whitespace-pre-line mt-1">{{ $casis->motivasi ?? '-' }}</p></div>
            <div><p class="text-xs font-semibold text-gray-500 uppercase">Pengalaman organisasi</p><p class="text-sm text-gray-800 whitespace-pre-line mt-1">{{ $casis->pengalaman ?? '-' }}</p></div>
            <div><p class="text-xs font-semibold text-gray-500 uppercase">Visi &amp; misi</p><p class="text-sm text-gray-800 whitespace-pre-line mt-1">{{ $casis->visi_misi ?? '-' }}</p></div>
        </div>

        {{-- Kelengkapan unsur penilai --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6" data-aos="fade-up">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Unsur penilai yang wajib menilai</p>
            <div class="flex flex-wrap gap-2">
                @foreach($syarat as $kodeTipe => $sudah)
                    <span class="px-3 py-1 text-sm rounded-full {{ $sudah ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $sudah ? '✓' : '•' }} {{ \App\Models\CasisReview::TIPE[$kodeTipe] }}</span>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 mt-2">Keputusan akhir baru bisa dibuat setelah semua unsur di atas menilai.</p>
        </div>

        {{-- Penilaianku --}}
        @if($bolehMenilai)
            <form action="{{ route('casis.review', $casis->id) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6" data-aos="fade-up">
                @csrf
                <h2 class="font-bold text-gray-800">Penilaianmu <span class="text-sm font-normal text-gray-500">({{ \App\Models\CasisReview::TIPE[$tipe] }})</span></h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500">Skor (0-100)</label>
                        <input type="number" name="skor" min="0" max="100" required value="{{ old('skor', $saya->skor ?? '') }}" class="w-full border p-2 rounded-lg text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-gray-500">Rekomendasi</label>
                        <select name="rekomendasi" required class="w-full border p-2 rounded-lg text-sm">
                            @foreach(\App\Models\CasisReview::REKOMENDASI as $kode => $label)
                                <option value="{{ $kode }}" {{ old('rekomendasi', $saya->rekomendasi ?? '') === $kode ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <textarea name="catatan" rows="3" maxlength="2000" placeholder="Catatan penilaian (opsional)" class="w-full border p-2 rounded-lg text-sm">{{ old('catatan', $saya->catatan ?? '') }}</textarea>
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">{{ $saya ? 'Perbarui penilaian' : 'Simpan penilaian' }}</button>
            </form>
        @else
            <div class="bg-white p-5 rounded-xl border border-gray-200 text-sm text-gray-600 mb-6">
                Penilaian hanya bisa dilakukan saat tahap seleksi untuk pendaftar yang berstatus <strong>Diajukan</strong>.
            </div>
        @endif

        {{-- Penilaian unsur lain: terbuka setelah menilai sendiri --}}
        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Penilaian</h2>
        @if(!$lihatSemua)
            <div class="bg-white p-5 rounded-xl border border-gray-200 text-sm text-gray-600 mb-6">
                Penilaian unsur lain disembunyikan supaya penilaianmu tidak terpengaruh. Hasilnya terbuka setelah kamu menyimpan penilaianmu.
            </div>
        @else
            <div class="space-y-3 mb-6">
                @foreach($casis->reviews as $r)
                    <div class="bg-white p-4 rounded-xl border border-gray-200">
                        <div class="flex flex-wrap justify-between items-center gap-2">
                            <p class="font-semibold text-gray-900">{{ $r->reviewer->name ?? 'Penilai dihapus' }}
                                <span class="text-xs font-normal text-gray-500">&middot; {{ \App\Models\CasisReview::TIPE[$r->tipe] }}</span></p>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold text-gray-900">{{ $r->skor }}</span>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $warnaRek[$r->rekomendasi] }}">{{ \App\Models\CasisReview::REKOMENDASI[$r->rekomendasi] }}</span>
                            </div>
                        </div>
                        @if($r->catatan)<p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $r->catatan }}</p>@endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Keputusan akhir --}}
        @if($bolehMemutuskan)
            <form action="{{ route('casis.putuskan', $casis->id) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border-2 border-indigo-300 space-y-3" data-aos="fade-up">
                @csrf
                @method('PATCH')
                <h2 class="font-bold text-gray-800">Keputusan akhir</h2>
                <textarea name="catatan" rows="3" maxlength="1000" placeholder="Catatan untuk pendaftar (wajib diisi kalau menolak)" class="w-full border p-2 rounded-lg text-sm"></textarea>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" name="keputusan" value="terima" class="bg-green-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-green-700"
                            onclick="return confirm('Terima sebagai pengurus OSIS? Ia langsung tercatat sebagai anggota ekskul OSIS.')">Terima sebagai pengurus</button>
                    <button type="submit" name="keputusan" value="tolak" class="bg-red-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-red-700">Tolak</button>
                </div>
            </form>
        @elseif($casis->status === 'diterima' || $casis->status === 'ditolak')
            <div class="bg-white p-5 rounded-xl border border-gray-200 text-sm text-gray-700">
                <strong>{{ \App\Models\CasisPendaftaran::STATUS[$casis->status] }}</strong>
                @if($casis->pemutus) oleh {{ $casis->pemutus->name }} @endif
                @if($casis->catatan_keputusan)<p class="mt-1 whitespace-pre-line">{{ $casis->catatan_keputusan }}</p>@endif
            </div>
        @endif
    </div>
</body>
</html>