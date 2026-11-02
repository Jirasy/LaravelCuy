{{-- Saringan katalog berupa pill: tipe, kategori, status pendaftaran (Buka/Tutup).
     Tiap kelompok dipilih satu; antar-kelompok digabung. Mengeklik pill yang sedang aktif mematikan saringan kelompok itu.
     Jumlah di pill sudah memperhitungkan saringan kelompok lain. Disimpan di URL supaya bisa dibagikan. --}}
@if($totalEkskul > 0)
    @php
        $parameter = array_filter([
            'jenis'    => $jenisAktif?->kode,
            'kategori' => $kategoriAktif?->slug,
            'status'   => $statusAktif,
        ]);
        // Tautan pill: ganti satu kelompok saja, saringan kelompok lain dipertahankan; $nilai null = matikan kelompok itu
        $tautan    = fn (string $kunci, ?string $nilai) => route('ekstrakurikuler.index', array_filter(array_merge($parameter, [$kunci => $nilai])));
        $pillDasar = 'px-3 py-1 rounded-full text-xs font-semibold border';
        $pillBiasa = 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
        $pillAktif = 'bg-indigo-600 text-white border-indigo-600';
    @endphp

    <div class="mb-4 space-y-2" id="saringan-katalog">
        @if($jenisFilter->count() > 1)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-gray-500 w-20">Tipe</span>
                <a href="{{ $tautan('jenis', null) }}" class="{{ $pillDasar }} {{ $jenisAktif ? $pillBiasa : $pillAktif }}">Semua</a>
                @foreach($jenisFilter as $j)
                    @php $aktif = $jenisAktif && $jenisAktif->id === $j->id; @endphp
                    <a href="{{ $tautan('jenis', $aktif ? null : $j->kode) }}" class="{{ $pillDasar }} {{ $aktif ? $pillAktif : $pillBiasa }}" @if($aktif) aria-current="true" @endif>{{ $j->nama }} ({{ $jumlah['jenis'][$j->id] ?? 0 }})</a>
                @endforeach
            </div>
        @endif

        @if($kategoriFilter->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-gray-500 w-20">Kategori</span>
                <a href="{{ $tautan('kategori', null) }}" class="{{ $pillDasar }} {{ $kategoriAktif ? $pillBiasa : $pillAktif }}">Semua</a>
                @foreach($kategoriFilter as $k)
                    @php $aktif = $kategoriAktif && $kategoriAktif->id === $k->id; @endphp
                    <a href="{{ $tautan('kategori', $aktif ? null : $k->slug) }}" class="{{ $pillDasar }} {{ $aktif ? $pillAktif : $pillBiasa }}" @if($aktif) aria-current="true" @endif>{{ $k->nama }} ({{ $jumlah['kategori'][$k->id] ?? 0 }})</a>
                @endforeach
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-gray-500 w-20">Pendaftaran</span>
            <a href="{{ $tautan('status', null) }}" class="{{ $pillDasar }} {{ $statusAktif ? $pillBiasa : $pillAktif }}">Semua</a>
            <a href="{{ $tautan('status', $statusAktif === 'buka' ? null : 'buka') }}" class="{{ $pillDasar }} {{ $statusAktif === 'buka' ? 'bg-emerald-600 text-white border-emerald-600' : $pillBiasa }}" @if($statusAktif === 'buka') aria-current="true" @endif>Buka ({{ $jumlah['status']['buka'] }})</a>
            <a href="{{ $tautan('status', $statusAktif === 'tutup' ? null : 'tutup') }}" class="{{ $pillDasar }} {{ $statusAktif === 'tutup' ? 'bg-gray-700 text-white border-gray-700' : $pillBiasa }}" @if($statusAktif === 'tutup') aria-current="true" @endif>Tutup ({{ $jumlah['status']['tutup'] }})</a>

            @if($jenisAktif || $kategoriAktif || $statusAktif)
                <a href="{{ route('ekstrakurikuler.index') }}" class="text-xs underline text-gray-600 ml-1">Reset filter</a>
            @endif
        </div>
    </div>
@endif
