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
<div id="blok-tipe">
    <label class="block text-sm font-semibold text-gray-700">Tipe ekskul</label>
    <select name="jenis_ekskul_id" class="w-full border p-2 rounded-lg mt-1">
        @foreach($daftarJenis as $j)
            <option value="{{ $j->id }}" data-organisasi="{{ $j->isOrganisasi() ? '1' : '0' }}" {{ $terpilih === $j->id ? 'selected' : '' }}>
                {{ $j->nama }}{{ $j->memakaiCasis() ? ' (pendaftaran lewat seleksi bertahap)' : '' }}{{ $j->aktif ? '' : ' (nonaktif)' }}
            </option>
        @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">
        Ekskul Biasa: daftar langsung. Ekskul Seleksi dan Organisasi (OSIS, MPK, dst): masuk lewat seleksi yang bisa diatur beberapa tahap.
        <a href="{{ route('jenis-ekskul.index') }}" class="underline">Kelola tipe ekskul</a>
    </p>

    {{-- Hierarki organisasi: hanya untuk tipe Organisasi (mis. OSIS berada di bawah MPK) --}}
    @php
        $calonInduk = \App\Models\Ekstrakurikuler::organisasi()
            ->when(($ekstrakurikuler ?? null)?->id, fn ($q, $id) => $q->where('id', '!=', $id))
            ->orderBy('nama_ekstrakurikuler')
            ->get(['id', 'nama_ekstrakurikuler']);
        $indukTerpilih = (int) old('induk_id', $ekstrakurikuler->induk_id ?? 0);
    @endphp
    <div id="blok-induk" class="mt-3" style="display:none">
        <label for="induk_id" class="block text-sm font-semibold text-gray-700">Berada di bawah organisasi <span class="font-normal text-gray-400">(opsional)</span></label>
        <select id="induk_id" name="induk_id" class="w-full border p-2 rounded-lg mt-1">
            <option value="">Tidak ada (organisasi tingkat tertinggi)</option>
            @foreach($calonInduk as $c)
                <option value="{{ $c->id }}" {{ $indukTerpilih === $c->id ? 'selected' : '' }}>{{ $c->nama_ekstrakurikuler }}</option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Contoh: pilih <strong>MPK</strong> pada OSIS, karena MPK berada di atas OSIS. Hanya organisasi yang bisa menjadi induk.</p>
    </div>
</div>

<script>
    (function () {
        var tipe = document.querySelector('select[name="jenis_ekskul_id"]');
        var blok = document.getElementById('blok-induk');
        if (!tipe || !blok) return;
        function atur() {
            var opsi = tipe.options[tipe.selectedIndex];
            blok.style.display = opsi && opsi.dataset.organisasi === '1' ? '' : 'none';
        }
        tipe.addEventListener('change', atur);
        atur();
    })();
</script>
