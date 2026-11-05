{{--
    Logo ekskul dalam lingkaran berukuran tetap, supaya katalog tetap rapi walau tiap ekskul punya desain sendiri.
    Belum ada logo -> monogram (singkatan atau huruf awal nama) di atas gradasi warna yang tetap untuk nama itu.
    Pakai: <x-logo-ekskul :ekskul="$ekskul" />   <x-logo-ekskul :ekskul="$ekskul" :ukuran="72" />
    ukuran: lebar & tinggi dalam px (default 48)
--}}
@props(['ekskul', 'ukuran' => 48])
@php $alt = 'Logo '.$ekskul->nama_ekstrakurikuler; @endphp
@if($ekskul->logo_url)
    {{-- Logo apa adanya (object-contain + sedikit bantalan) di atas dasar putih, supaya tidak terpotong lingkaran dan tetap terbaca di mode gelap.
         Warna dasar dan garis tepi sengaja inline: kelas bg-white / border-gray-200 ditimpa tema aplikasi (semi-transparan di mode terang, gelap di mode gelap),
         dan logo yang dirancang untuk latar putih (mis. palang merah) jadi nyaris tak terlihat di atasnya. --}}
    <span {{ $attributes->merge(['class' => 'rounded-full shrink-0 inline-flex items-center justify-center overflow-hidden']) }}
          style="width:{{ $ukuran }}px;height:{{ $ukuran }}px;padding:{{ max(2, round($ukuran * .1)) }}px;background:#fff;border:1px solid rgba(148,163,184,.55)">
        <img src="{{ $ekskul->logo_url }}" alt="{{ $alt }}" loading="lazy" class="w-full h-full object-contain">
    </span>
@else
    @php
        [$warna1, $warna2] = $ekskul->warna_monogram;
        $huruf = $ekskul->monogram;
        $skala = mb_strlen($huruf) >= 3 ? .30 : .38;
    @endphp
    <span {{ $attributes->merge(['class' => 'rounded-full shrink-0 inline-flex items-center justify-center font-bold text-white select-none']) }}
          role="img" aria-label="{{ $alt }}"
          style="width:{{ $ukuran }}px;height:{{ $ukuran }}px;font-size:{{ round($ukuran * $skala) }}px;letter-spacing:-.02em;background:linear-gradient(135deg, {{ $warna1 }}, {{ $warna2 }})">{{ $huruf }}</span>
@endif
