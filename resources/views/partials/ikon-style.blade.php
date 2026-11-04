{{--
    Gaya khas ikon: "ubin kaca berfaset" (serpihan kaca) yang jadi ciri tampilan situs ini. Dipasang lewat partials.head.
    Bentuknya potongan miring seperti .shard di halaman masuk, isinya gradasi + faset terang/gelap + kilau saat disorot.
    Warna "aksen" mengikuti jam lokal (html[data-waktu], dipasang partials/tema-waktu): biru-teal pagi, biru-indigo siang,
    oranye-mawar sore, indigo-ungu malam. Warna lain (zamrud, amber, mawar, langit, ungu, abu) tetap, untuk makna tertentu.
    Pakai: <x-ikon-kaca nama="piala" warna="amber" ukuran="lg" />
--}}
@verbatim
<style>
    /* Warna aksen per waktu; ikon putih di atasnya dijaga kontras >= 3:1 (batas untuk elemen grafis) */
    :root                    { --ikon-a: 79 70 229;  --ikon-b: 168 85 247; }
    html[data-waktu="pagi"]  { --ikon-a: 13 148 136; --ikon-b: 2 132 199; }
    html[data-waktu="siang"] { --ikon-a: 2 132 199;  --ikon-b: 79 70 229; }
    html[data-waktu="sore"]  { --ikon-a: 234 88 12;  --ikon-b: 225 29 72; }
    html[data-waktu="malam"] { --ikon-a: 99 102 241; --ikon-b: 147 51 234; }

    .ikon-kaca {
        --ik-a: var(--ikon-a); --ik-b: var(--ikon-b); --ik-u: 44px;
        position: relative; display: inline-block; flex: none; width: var(--ik-u); height: var(--ik-u);
        filter: drop-shadow(0 8px 10px rgb(var(--ik-a) / .38)); /* bayangan di luar, mengikuti bentuk serpihan */
        transition: transform .25s ease, filter .25s ease;
    }
    .ikon-kaca-sm { --ik-u: 32px; }
    .ikon-kaca-lg { --ik-u: 56px; }
    .ikon-kaca:hover, .card-hover:hover .ikon-kaca, a:hover > .ikon-kaca { transform: translateY(-2px) rotate(-3deg); }
    html.dark .ikon-kaca { filter: drop-shadow(0 8px 14px rgb(var(--ik-a) / .6)); }

    .ikon-kaca-isi {
        position: absolute; inset: 0; display: grid; place-items: center; overflow: hidden; isolation: isolate; color: #fff;
        clip-path: polygon(0 22%, 22% 0, 100% 0, 100% 78%, 78% 100%, 0 100%); /* sudut kiri-atas & kanan-bawah dipotong miring */
        background: linear-gradient(150deg, rgb(var(--ik-a)), rgb(var(--ik-b)));
    }
    .ikon-kaca-isi::before { /* faset: serpihan terang kiri-atas, garis retak tipis, bayangan kanan-bawah */
        content: ''; position: absolute; inset: 0; z-index: -1;
        background:
            linear-gradient(122deg, rgba(255, 255, 255, .42) 0 30%, transparent 30.4%),
            linear-gradient(122deg, transparent 0 56%, rgba(255, 255, 255, .22) 56% 57.4%, transparent 57.8%),
            linear-gradient(302deg, rgba(15, 23, 42, .24) 0 26%, transparent 26.4%);
    }
    .ikon-kaca-isi::after { /* kilau yang menyapu saat disorot */
        content: ''; position: absolute; top: -20%; bottom: -20%; left: -70%; width: 40%; pointer-events: none;
        background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .55), transparent);
        transform: skewX(-20deg); transition: left .7s ease;
    }
    .ikon-kaca:hover .ikon-kaca-isi::after, .card-hover:hover .ikon-kaca-isi::after, a:hover > .ikon-kaca .ikon-kaca-isi::after { left: 130%; }
    .ikon-kaca-isi > svg { position: relative; filter: drop-shadow(0 1px 1.5px rgba(15, 23, 42, .4)); }

    /* Warna bermakna: tidak ikut jam */
    .ikon-kaca-zamrud { --ik-a: 5 150 105;  --ik-b: 13 148 136; }
    .ikon-kaca-amber  { --ik-a: 217 119 6;  --ik-b: 234 88 12; }
    .ikon-kaca-mawar  { --ik-a: 225 29 72;  --ik-b: 192 38 211; }
    .ikon-kaca-langit { --ik-a: 2 132 199;  --ik-b: 79 70 229; }
    .ikon-kaca-ungu   { --ik-a: 124 58 237; --ik-b: 192 38 211; }
    .ikon-kaca-abu    { --ik-a: 71 85 105;  --ik-b: 51 65 85; }
</style>
@endverbatim
