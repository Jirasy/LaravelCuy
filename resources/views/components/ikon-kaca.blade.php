{{--
    Ikon dalam ubin kaca berfaset (gaya di partials/ikon-style). Untuk ikon yang berdiri sendiri: kartu ringkasan, judul, status kosong.
    Ikon di tengah kalimat tetap pakai <x-ikon>.
    Pakai: <x-ikon-kaca nama="piala" warna="amber" ukuran="lg" />   <x-ikon-kaca :nama="$n->data['ikon']" ukuran="sm" class="mr-2 align-middle" />
    warna : aksen (default, ikut jam lokal) | zamrud | amber | mawar | langit | ungu | abu
    ukuran: sm (32px) | md (44px, default) | lg (56px)
--}}
@props(['nama', 'warna' => 'aksen', 'ukuran' => 'md'])
@php
    $glif = ['sm' => 16, 'md' => 22, 'lg' => 28][$ukuran] ?? 22;
@endphp
<span {{ $attributes->class(['ikon-kaca', 'ikon-kaca-'.$ukuran, 'ikon-kaca-'.$warna]) }}><span class="ikon-kaca-isi"><x-ikon :nama="$nama" :ukuran="$glif" /></span></span>
