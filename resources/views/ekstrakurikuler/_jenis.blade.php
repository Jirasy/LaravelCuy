{{-- Pilihan jenis ekskul (khusus admin). Dipakai create & edit. Jenis nonaktif hanya tampil kalau sedang dipakai ekskul ini. --}}
@php
    $idSekarang = $ekstrakurikuler->jenis_ekskul_id ?? 0;
    $daftarJenis = \App\Models\JenisEkskul::urut()
        ->where(fn ($q) => $q->where('aktif', true)->orWhere('id', $idSekarang))
        ->get();
    $terpilih = (int) old('jenis_ekskul_id', $idSekarang ?: ($daftarJenis->firstWhere('kode', 'ekskul')->id ?? 0));
@endphp
<div>
    <label class="block text-sm font-semibold text-gray-700">Jenis ekskul</label>
    <select name="jenis_ekskul_id" class="w-full border p-2 rounded-lg mt-1">
        @foreach($daftarJenis as $j)
            <option value="{{ $j->id }}" {{ $terpilih === $j->id ? 'selected' : '' }}>
                {{ $j->nama }}{{ $j->memakaiCasis() ? ' (pendaftaran lewat seleksi CaSis)' : '' }}{{ $j->aktif ? '' : ' (nonaktif)' }}
            </option>
        @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">
        Jenis bertanda "hanya satu" (mis. OSIS, MPK) hanya boleh dimiliki satu ekskul.
        <a href="{{ route('jenis-ekskul.index') }}" class="underline">Kelola jenis ekskul</a>
    </p>
</div>
