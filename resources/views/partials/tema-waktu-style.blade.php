{{--
    Gaya tema berbasis jam lokal untuk area yang dipakai semua halaman setelah login: latar utama (aurora), navbar, saklar tema.
    Penandanya dipasang oleh partials/tema-waktu: html[data-waktu="pagi|siang|sore|malam"] dan html.dark.
    Dipasang SETELAH gaya di partials/head supaya menang pada spesifisitas yang sama.
    Pilihan user tetap diutamakan: palet selain "aurora", wallpaper sendiri, aksen warna, dan navbar gaya "solid" tidak ditimpa.
--}}
@verbatim
<style>
    /* ============ A. LATAR UTAMA (aurora): siang biru terang, pagi hangat-segar, sore senja, malam = mode gelap bawaan ============ */
    html:is(:not([data-palet]), [data-palet="aurora"])[data-waktu="pagi"] {
        --bg-base: linear-gradient(135deg, #e0f2fe 0%, #fff7e0 48%, #e0f7ff 100%);
        --a1: rgba(56, 189, 248, .50); --a2: rgba(253, 224, 71, .38); --a3: rgba(125, 211, 252, .55); --a4: rgba(254, 215, 170, .55);
        --hue-dari: -8deg; --hue-ke: 10deg;
    }
    html:is(:not([data-palet]), [data-palet="aurora"])[data-waktu="siang"] {
        --bg-base: linear-gradient(135deg, #dbeafe 0%, #eff8ff 45%, #e0f2fe 100%);
        --a1: rgba(59, 130, 246, .42); --a2: rgba(56, 189, 248, .40); --a3: rgba(125, 211, 252, .55); --a4: rgba(186, 230, 253, .65);
        --hue-dari: -8deg; --hue-ke: 8deg;
    }
    html:is(:not([data-palet]), [data-palet="aurora"])[data-waktu="sore"] {
        --bg-base: linear-gradient(135deg, #fff1e3 0%, #ffe3d3 45%, #fbe1f1 100%);
        --a1: rgba(251, 146, 60, .50); --a2: rgba(244, 114, 182, .45); --a3: rgba(251, 191, 36, .42); --a4: rgba(168, 85, 247, .32);
        --hue-dari: -10deg; --hue-ke: 12deg; /* rentang sempit supaya oranye tidak bergeser jadi hijau */
    }

    /* ============ B. NAVBAR: lapisan warna tipis sesuai waktu (gaya melayang, kaca, ringkas; "solid" sengaja polos) ============
       Aksen warna pilihan user (html.has-aksen-warna .nav-bar) lebih spesifik, jadi tetap menang. */
    html[data-waktu="pagi"]  { --nav-tint: linear-gradient(135deg, rgba(125, 211, 252, .30), rgba(254, 240, 138, .16)); }
    html[data-waktu="siang"] { --nav-tint: linear-gradient(135deg, rgba(96, 165, 250, .26), rgba(186, 230, 253, .18)); }
    html[data-waktu="sore"]  { --nav-tint: linear-gradient(135deg, rgba(251, 146, 60, .30), rgba(244, 114, 182, .18)); }
    html[data-waktu="malam"] {
        --nav-tint:
            radial-gradient(1.2px 1.2px at 9% 34%, rgba(255, 255, 255, .85), transparent),
            radial-gradient(1px 1px at 23% 68%, rgba(199, 210, 254, .8), transparent),
            radial-gradient(1.2px 1.2px at 47% 26%, rgba(255, 255, 255, .8), transparent),
            radial-gradient(1px 1px at 66% 72%, rgba(224, 231, 255, .75), transparent),
            radial-gradient(1.2px 1.2px at 84% 30%, rgba(255, 255, 255, .85), transparent),
            radial-gradient(1px 1px at 95% 64%, rgba(199, 210, 254, .7), transparent),
            linear-gradient(135deg, rgba(37, 45, 120, .50), rgba(76, 29, 149, .28));
    }
    .nav-melayang, .nav-kaca, .nav-ringkas,
    html.dark .nav-melayang, html.dark .nav-kaca, html.dark .nav-ringkas { background-image: var(--nav-tint, none); }

    /* ============ C. SAKLAR TEMA: langit senja saat sore (siang = langit biru, malam = langit gelap, sudah ada di head) ============ */
    html[data-waktu="sore"] .theme-switch {
        background-image: linear-gradient(180deg, #5b50a8 0%, #e879a0 52%, #fdba74 100%);
        box-shadow: inset 0 2px 6px rgba(76, 29, 90, .4), 0 8px 18px -10px rgba(244, 114, 182, .9);
    }
    html[data-waktu="sore"] .ts-knob {
        background: radial-gradient(circle at 35% 30%, #fff3d6 0%, #fdba74 45%, #f97316 100%);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .28), 0 0 14px 3px rgba(251, 146, 60, .75), inset -2px -3px 5px rgba(194, 65, 12, .45);
    }
</style>
@endverbatim
