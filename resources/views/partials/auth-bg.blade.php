{{--
    Latar halaman masuk/daftar: pemandangan gunung berlapis (SVG buatan sendiri, tanpa gambar eksternal).
    Mode terang = siang berawan, mode gelap = malam berbintang. Warna diatur lewat variabel --sc-* di partials/auth-style.
--}}
@php
    // Puncak gunung belakang yang diberi salju, dan polygon bergerigi untuk tutup saljunya
    $puncak = [[360, 340], [700, 300], [1060, 330], [1420, 300]];
    $titikSalju = [[-140, -10], [140, -10], [140, 60], [100, 48], [60, 72], [25, 50], [-10, 78], [-45, 52], [-85, 70], [-140, 55]];

    // Hutan pinus belakang (lebih terang) dan depan (lebih gelap); posisi dibuat deterministik supaya tidak berubah tiap muat ulang
    $hutanBelakang = [];
    for ($i = 0; $i < 44; $i++) {
        $w = 46 + (($i * 7) % 4) * 8;
        $hutanBelakang[] = ['x' => -20 + $i * 38 + (($i * 13) % 9), 'w' => $w, 'y' => round(884 - $w * 2.2)];
    }
    $hutanDepan = [];
    for ($i = 0; $i < 28; $i++) {
        $w = 64 + (($i * 5) % 3) * 14;
        $hutanDepan[] = ['x' => -30 + $i * 60 + (($i * 11) % 13), 'w' => $w, 'y' => round(918 - $w * 2.2)];
    }
    // Pohon besar di tepi kiri-kanan (hanya terlihat di layar lebar)
    $pohonTepi = [[-40, 240], [80, 180], [190, 140], [290, 110], [1400, 250], [1310, 180], [1220, 140], [1130, 110]];
@endphp
<svg class="auth-scene" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="sc-langit" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" style="stop-color:var(--sc-sky-top)"/>
            <stop offset="1" style="stop-color:var(--sc-sky-bottom)"/>
        </linearGradient>
        <radialGradient id="sc-cahaya">
            <stop offset="0" style="stop-color:var(--sc-glow);stop-opacity:.85"/>
            <stop offset="1" style="stop-color:var(--sc-glow);stop-opacity:0"/>
        </radialGradient>
        <linearGradient id="sc-kabut" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" style="stop-color:var(--sc-mist);stop-opacity:0"/>
            <stop offset=".65" style="stop-color:var(--sc-mist);stop-opacity:.5"/>
            <stop offset="1" style="stop-color:var(--sc-mist);stop-opacity:0"/>
        </linearGradient>

        <path id="sc-gunung-belakang" d="M0 520 L120 430 L220 480 L360 340 L470 450 L560 400 L700 300 L820 430 L930 380 L1060 330 L1180 450 L1300 390 L1420 300 L1530 410 L1600 380 L1600 900 L0 900 Z"/>
        <clipPath id="sc-potong-belakang"><use href="#sc-gunung-belakang"/></clipPath>

        <g id="sc-awan">
            <ellipse cx="60" cy="34" rx="62" ry="22"/>
            <ellipse cx="112" cy="22" rx="42" ry="24"/>
            <ellipse cx="28" cy="26" rx="30" ry="18"/>
            <ellipse cx="84" cy="40" rx="70" ry="16"/>
        </g>

        <symbol id="sc-pinus" viewBox="0 0 100 220">
            <path d="M50 0 L82 70 L64 70 L90 125 L68 125 L98 190 L2 190 L32 125 L10 125 L36 70 L18 70 Z"/>
            <rect x="45" y="188" width="10" height="32"/>
        </symbol>
    </defs>

    <rect width="1600" height="900" fill="url(#sc-langit)"/>

    {{-- Siang: matahari + awan + burung --}}
    <g class="sc-day">
        <circle cx="1010" cy="240" r="230" fill="url(#sc-cahaya)"/>
        <circle cx="1010" cy="240" r="76" style="fill:var(--sc-sun)"/>
        <g transform="translate(150 150) scale(1.25)"><g class="sc-awan sc-awan-1"><use href="#sc-awan"/></g></g>
        <g transform="translate(1240 110)"><g class="sc-awan sc-awan-2"><use href="#sc-awan"/></g></g>
        <g transform="translate(560 235) scale(.8)"><g class="sc-awan sc-awan-3"><use href="#sc-awan"/></g></g>
        <g class="sc-burung" fill="none" stroke="var(--sc-bird)" stroke-width="3" stroke-linecap="round">
            <path d="M720 190 q10 -12 20 0 q10 -12 20 0"/>
            <path d="M770 160 q8 -10 16 0 q8 -10 16 0"/>
            <path d="M690 150 q7 -9 14 0 q7 -9 14 0"/>
        </g>
    </g>

    {{-- Malam: bulan + bintang berkedip --}}
    <g class="sc-night">
        <circle cx="1010" cy="240" r="210" fill="url(#sc-cahaya)"/>
        <circle cx="1010" cy="240" r="54" style="fill:var(--sc-moon)"/>
        <circle cx="994" cy="228" r="11" fill="#000" opacity=".07"/>
        <circle cx="1028" cy="256" r="8" fill="#000" opacity=".07"/>
        <circle cx="1022" cy="214" r="5" fill="#000" opacity=".06"/>
        @for($i = 0; $i < 52; $i++)
            <circle class="sc-bintang" cx="{{ round(fmod($i * 197.3 + 40, 1560) + 20) }}" cy="{{ round(fmod($i * 89.7 + 30, 400) + 20) }}"
                    r="{{ 0.8 + ($i % 3) * 0.5 }}" style="animation-delay:{{ ($i % 7) * 0.6 }}s"/>
        @endfor
    </g>

    {{-- Gunung belakang + salju --}}
    <use href="#sc-gunung-belakang" class="sc-belakang"/>
    <g clip-path="url(#sc-potong-belakang)" class="sc-salju">
        @foreach($puncak as [$px, $py])
            <polygon points="{{ collect($titikSalju)->map(fn ($t) => ($px + $t[0]).','.($py + $t[1]))->implode(' ') }}"/>
        @endforeach
    </g>
    <rect y="380" width="1600" height="260" fill="url(#sc-kabut)"/>

    {{-- Gunung tengah --}}
    <path class="sc-tengah" d="M0 640 L100 560 L230 610 L380 500 L520 600 L640 540 L790 620 L930 520 L1080 610 L1230 510 L1370 600 L1490 540 L1600 590 L1600 900 L0 900 Z"/>
    <rect y="560" width="1600" height="240" fill="url(#sc-kabut)"/>

    {{-- Bukit depan --}}
    <path class="sc-bukit" d="M0 720 C200 660 380 700 560 740 S900 700 1100 730 S1450 690 1600 720 L1600 900 L0 900 Z"/>

    {{-- Hutan pinus berlapis --}}
    @foreach($hutanBelakang as $p)
        <use href="#sc-pinus" class="sc-hutan-jauh" x="{{ $p['x'] }}" y="{{ $p['y'] }}" width="{{ $p['w'] }}" height="{{ round($p['w'] * 2.2) }}"/>
    @endforeach
    <rect class="sc-hutan-dekat" y="858" width="1600" height="60"/>
    @foreach($hutanDepan as $p)
        <use href="#sc-pinus" class="sc-hutan-dekat" x="{{ $p['x'] }}" y="{{ $p['y'] }}" width="{{ $p['w'] }}" height="{{ round($p['w'] * 2.2) }}"/>
    @endforeach
    @foreach($pohonTepi as [$tx, $tw])
        <use href="#sc-pinus" class="sc-hutan-dekat" x="{{ $tx }}" y="{{ round(930 - $tw * 2.2) }}" width="{{ $tw }}" height="{{ round($tw * 2.2) }}"/>
    @endforeach
</svg>
