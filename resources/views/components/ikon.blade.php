{{--
    Ikon garis SVG pengganti emoji (daftar nama ada di App\Support\Ikon). Warna mengikuti teks sekitarnya (currentColor).
    Pakai: <x-ikon nama="kunci" />   <x-ikon nama="bintang" :ukuran="14" isi class="text-amber-500" />   <x-ikon :nama="$n->data['ikon']" />
    nama  : nama ikon; emoji lama dari data tersimpan otomatis diterjemahkan, nama tak dikenal jadi ikon lonceng
    ukuran: lebar & tinggi dalam px (default 16)  |  isi: ikon padat (mis. bintang)  |  tebal: tebal garis (default 2)
--}}
@props(['nama', 'ukuran' => 16, 'tebal' => 2, 'isi' => false])
<svg style="display:inline-block;vertical-align:-.15em;flex:none" {{ $attributes }} viewBox="0 0 24 24" width="{{ $ukuran }}" height="{{ $ukuran }}"
     fill="{{ $isi ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $tebal }}" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true" focusable="false">{!! \App\Support\Ikon::jalur((string) $nama) !!}</svg>
