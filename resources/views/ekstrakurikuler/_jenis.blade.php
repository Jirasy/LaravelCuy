{{-- Pilihan tipe ekskul (khusus admin). Dipakai create & edit. Tipe nonaktif hanya tampil kalau sedang dipakai ekskul ini.
     Komunitas tidak ditawarkan: ekskul buatan admin langsung resmi (komunitas dibuat pembina/siswa, lalu diresmikan). --}}
@php
    $idSekarang = $ekstrakurikuler->jenis_ekskul_id ?? 0;
    $daftarJenis = \App\Models\JenisEkskul::urut()
        ->where(fn ($q) => $q->where('aktif', true)->orWhere('id', $idSekarang))
        ->where(fn ($q) => $q->where('kategori', '!=', 'komunitas')->orWhere('id', $idSekarang))
        ->get();
    $terpilih = (int) old('jenis_ekskul_id', $idSekarang ?: ($daftarJenis->firstWhere('kode', 'ekskul')->id ?? 0));
@endphp
<div>
    <label class="block text-sm font-semibold text-gray-700">Tipe ekskul</label>
    <select name="jenis_ekskul_id" class="w-full border p-2 rounded-lg mt-1">
        @foreach($daftarJenis as $j)
            <option value="{{ $j->id }}" {{ $terpilih === $j->id ? 'selected' : '' }}>
                {{ $j->nama }}{{ $j->memakaiCasis() ? ' (pendaftaran lewat seleksi bertahap)' : '' }}{{ $j->aktif ? '' : ' (nonaktif)' }}
            </option>
        @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">
        Ekskul Biasa: daftar langsung. Ekskul Seleksi dan Organisasi (OSIS, MPK, dst): masuk lewat seleksi yang bisa diatur beberapa tahap.
        <a href="{{ route('jenis-ekskul.index') }}" class="underline">Kelola tipe ekskul</a>
    </p>
</div>
