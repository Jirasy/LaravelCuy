{{-- Variabel: $ekskul. Posisi organisasi dalam hierarki: di bawah siapa, dan membawahi siapa. --}}
@php
    $induk   = $ekskul->induk;
    $bawahan = $ekskul->bawahan()->orderBy('nama_ekstrakurikuler')->get(['id', 'nama_ekstrakurikuler', 'nama_singkatan']);
@endphp
@if($induk || $bawahan->isNotEmpty())
    <div class="mt-4 pt-4 border-t border-gray-200">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2"><x-ikon nama="gedung" :ukuran="13" /> Hierarki organisasi</p>
        <div class="flex flex-wrap gap-2 text-sm">
            @if($induk)
                <a href="{{ route('ekstrakurikuler.show', $induk->id) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-purple-100 text-purple-800 font-semibold hover:bg-purple-200"><x-ikon nama="panah-atas" :ukuran="14" /> Di bawah {{ $induk->nama_tampil }}</a>
            @endif
            @foreach($bawahan as $b)
                <a href="{{ route('ekstrakurikuler.show', $b->id) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 font-semibold hover:bg-indigo-200"><x-ikon nama="panah-bawah" :ukuran="14" /> Membawahi {{ $b->nama_tampil }}</a>
            @endforeach
        </div>
        @if($induk && $ekskul->memakaiCasis())
            <p class="text-xs text-gray-500 mt-2">
                Pembina {{ $induk->nama_ekstrakurikuler }} ikut mengelola dan menilai seleksi di sini, dan anggota {{ $induk->nama_ekstrakurikuler }}
                bisa ditunjuk sebagai senior penilai.
            </p>
        @endif
    </div>
@endif
