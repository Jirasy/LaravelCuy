{{-- Gaya khusus halaman masuk/daftar: pemandangan, kartu kaca, isian, tombol. Dipasang setelah partials.head. --}}
@verbatim
<style>
    /* ============ PALET: siang (bawaan) | pagi & sore (html[data-waktu]) | malam (html.dark) ============ */
    .auth-body {
        --sc-sky-top: #9ec5ff; --sc-sky-mid: #c8dfff; --sc-sky-bottom: #e4f0ff; --sc-glow: #fff7da; --sc-sun: #fffbe8; --sc-moon: #eef2ff;
        --sc-cloud: rgba(255, 255, 255, .88); --sc-bird: #1e3a5f;
        --sc-far: #9db9ea; --sc-snow: #f6f9ff; --sc-mid: #7a9fdc; --sc-near: #4f78b8; --sc-hill: #34598f;
        --sc-tree-far: #1f4373; --sc-tree: #10294a; --sc-mist: #ffffff;

        --au-text: #0f172a; --au-label: #1e293b; --au-muted: #475569; --au-link: #4338ca; --au-ok: #047857;
        --au-card: rgba(255, 255, 255, .52); --au-card-solid: #f1f5fb; --au-card-border: rgba(255, 255, 255, .8);
        --au-shadow: rgba(30, 58, 138, .55); --au-chip: rgba(15, 23, 42, .07); --au-ico: #64748b;
        --au-err-bg: rgba(254, 226, 226, .85); --au-err-border: #f87171; --au-err-text: #991b1b;
        --au-ok-bg: rgba(209, 250, 229, .85); --au-ok-border: #34d399; --au-ok-text: #065f46;
    }
    html.dark .auth-body {
        --sc-sky-top: #060818; --sc-sky-mid: #1d1342; --sc-sky-bottom: #2c1a5e; --sc-glow: #a5b4fc;
        --sc-cloud: rgba(165, 180, 252, .08);
        --sc-far: #2a2d6b; --sc-snow: #5f69c2; --sc-mid: #1d2060; --sc-near: #14174a; --sc-hill: #0d0f34;
        --sc-tree-far: #0a0c2a; --sc-tree: #04051a; --sc-mist: #7c6bd6;

        --au-text: #f1f5f9; --au-label: #e2e8f0; --au-muted: #b4c0d8; --au-link: #a5b4fc; --au-ok: #6ee7b7;
        --au-card: rgba(12, 16, 45, .58); --au-card-solid: #0f1330; --au-card-border: rgba(255, 255, 255, .16);
        --au-shadow: rgba(0, 0, 0, .75); --au-chip: rgba(255, 255, 255, .09); --au-ico: #94a3b8;
        --au-err-bg: rgba(127, 29, 29, .45); --au-err-border: #f87171; --au-err-text: #fecaca;
        --au-ok-bg: rgba(6, 78, 59, .5); --au-ok-border: #34d399; --au-ok-text: #a7f3d0;
    }

    /* Pagi: langit biru segar, ufuk keemasan, matahari rendah */
    html[data-waktu="pagi"] .auth-body {
        --sc-sky-top: #78bdf5; --sc-sky-mid: #cfe6fb; --sc-sky-bottom: #ffe9c7; --sc-glow: #ffefb8; --sc-sun: #fffbe6;
        --sc-cloud: rgba(255, 255, 255, .92); --sc-bird: #2b4a6f;
        --sc-far: #a8c3ea; --sc-snow: #fffaf0; --sc-mid: #86a6dc; --sc-near: #5c86c0; --sc-hill: #3b6399;
        --sc-tree-far: #254b7a; --sc-tree: #12305a; --sc-mist: #fff3df;
    }
    /* Sore: senja, langit ungu -> merah muda -> oranye, matahari tenggelam di sela gunung */
    html[data-waktu="sore"] .auth-body {
        --sc-sky-top: #4f4fa3; --sc-sky-mid: #d9778f; --sc-sky-bottom: #ffb86b; --sc-glow: #ffc77a; --sc-sun: #ffe3a3;
        --sc-cloud: rgba(255, 170, 130, .8); --sc-bird: #3b2150;
        --sc-far: #a26aa6; --sc-snow: #ffd6bd; --sc-mid: #7d548f; --sc-near: #563e7d; --sc-hill: #3a2c66;
        --sc-tree-far: #2a2158; --sc-tree: #17123e; --sc-mist: #ffb48a;
        --au-card: rgba(255, 250, 245, .56); --au-shadow: rgba(76, 29, 90, .55);
    }

    .auth-body { margin: 0; min-height: 100vh; background: var(--sc-sky-bottom); color: var(--au-text); }

    /* ============ PEMANDANGAN ============ */
    .auth-scene { position: fixed; inset: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
    .auth-scene .sc-night { display: none; }
    html.dark .auth-scene .sc-night { display: inline; }
    html.dark .auth-scene .sc-day { display: none; }
    .auth-scene .sc-matahari { transition: transform 1.2s cubic-bezier(.2, .8, .2, 1); }
    html[data-waktu="pagi"] .auth-scene .sc-matahari { transform: translate(190px, 130px); }   /* terbit: rendah di sisi kanan */
    html[data-waktu="sore"] .auth-scene .sc-matahari { transform: translate(180px, 180px); }   /* terbenam di balik gunung kanan */
    .auth-scene .sc-belakang { fill: var(--sc-far); }
    .auth-scene .sc-salju { fill: var(--sc-snow); opacity: .92; }
    .auth-scene .sc-tengah { fill: var(--sc-mid); }
    .auth-scene .sc-bukit { fill: var(--sc-near); }
    .auth-scene .sc-hutan-jauh { fill: var(--sc-tree-far); }
    .auth-scene .sc-hutan-dekat { fill: var(--sc-tree); }
    .auth-scene .sc-awan { fill: var(--sc-cloud); animation: authAwan 40s ease-in-out infinite alternate; }
    .auth-scene .sc-awan-2 { animation-duration: 56s; animation-delay: -18s; }
    .auth-scene .sc-awan-3 { animation-duration: 48s; animation-delay: -30s; }
    .auth-scene .sc-bintang { fill: #fff; animation: authKedip 3.2s ease-in-out infinite alternate; }
    .auth-scene .sc-burung { animation: authBurung 9s ease-in-out infinite alternate; }
    @keyframes authAwan { from { transform: translateX(-35px); } to { transform: translateX(55px); } }
    @keyframes authKedip { from { opacity: .2; } to { opacity: .95; } }
    @keyframes authBurung { from { transform: translate(-12px, 6px); } to { transform: translate(18px, -8px); } }

    /* ============ TATA LETAK ============ */
    .auth-main {
        position: relative; z-index: 1; min-height: 100vh; min-height: 100dvh;
        display: flex; align-items: center; justify-content: center; padding: 4.25rem 1rem 2rem;
    }

    /* ============ KARTU KACA ============ */
    .auth-card {
        position: relative; width: 100%; max-width: 27rem; padding: 2rem 1.5rem; border-radius: 1.75rem;
        color: var(--au-text);
        background: var(--au-card); border: 1px solid var(--au-card-border);
        -webkit-backdrop-filter: blur(22px) saturate(150%); backdrop-filter: blur(22px) saturate(150%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .5), 0 34px 70px -28px var(--au-shadow);
        animation: authNaik .7s cubic-bezier(.2, .8, .2, 1) both;
    }
    .auth-card-lebar { max-width: 40rem; }
    @media (min-width: 640px) { .auth-card { padding: 2.5rem 2.5rem 2.25rem; } }
    @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
        .auth-card { background: var(--au-card-solid); }
    }
    @keyframes authNaik { from { opacity: 0; transform: translateY(18px) scale(.985); } to { opacity: 1; transform: none; } }

    .auth-head { text-align: center; margin-bottom: 1.5rem; }
    .auth-badge {
        display: inline-grid; place-items: center; width: 3.25rem; height: 3.25rem; margin-bottom: .9rem;
        border-radius: 1.05rem; color: #fff;
        background: linear-gradient(135deg, rgb(var(--p1-rgb)), rgb(var(--p2-rgb)));
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .45), 0 12px 26px -10px rgb(var(--p1-rgb) / .85);
    }
    .auth-title { margin: 0; font-size: 1.85rem; line-height: 1.15; font-weight: 800; color: var(--au-text); letter-spacing: -.01em; }
    .auth-sub { margin: .45rem 0 0; font-size: .875rem; line-height: 1.45; color: var(--au-muted); }

    /* ============ FORM ============ */
    .auth-form { display: grid; gap: 1rem; }
    @media (min-width: 640px) { .auth-grid { grid-template-columns: 1fr 1fr; gap: 1rem 1.1rem; } }
    .auth-span { grid-column: 1 / -1; }
    .auth-label { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--au-label); }

    .auth-field { position: relative; margin-top: .35rem; }
    .auth-card .auth-field .field-glass { margin-top: 0; padding-left: 2.75rem; }
    .auth-card .auth-field .field-glass.has-eye { padding-right: 2.9rem; }
    .auth-field-ico { position: absolute; left: .95rem; top: 50%; transform: translateY(-50%); color: var(--au-ico); pointer-events: none; transition: color .2s ease; }
    .auth-field:focus-within .auth-field-ico { color: rgb(var(--p1-rgb)); }
    html.dark .auth-field:focus-within .auth-field-ico { color: #a5b4fc; }
    .auth-card .field-glass.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, .2); }

    .auth-eye {
        position: absolute; right: .4rem; top: 50%; transform: translateY(-50%); width: 2.2rem; height: 2.2rem;
        display: grid; place-items: center; border: 0; border-radius: .65rem; background: transparent;
        color: var(--au-ico); cursor: pointer; transition: background .2s ease, color .2s ease;
    }
    .auth-eye:hover { background: var(--au-chip); color: var(--au-text); }
    .auth-eye:focus-visible { outline: 2px solid rgb(var(--p1-rgb)); outline-offset: 1px; }
    .auth-eye .eye-off { display: none; }
    .auth-eye[aria-pressed="true"] .eye-on { display: none; }
    .auth-eye[aria-pressed="true"] .eye-off { display: block; }

    /* Centang "Ingat saya": dibuat sendiri supaya tampil sama di mode terang & gelap */
    .auth-check { display: inline-flex; align-items: center; gap: .55rem; font-size: .85rem; color: var(--au-muted); cursor: pointer; user-select: none; }
    .auth-card .auth-check input {
        -webkit-appearance: none; appearance: none; display: inline-grid; place-content: center; flex: none;
        width: 1.15rem; height: 1.15rem; margin: 0; border-radius: .38rem; cursor: pointer;
        border: 1.5px solid var(--au-ico); background-color: rgba(255, 255, 255, .35); transition: background .15s ease, border-color .15s ease;
    }
    html.dark .auth-card .auth-check input { background-color: rgba(255, 255, 255, .08); }
    .auth-card .auth-check input::after {
        content: ''; width: .55rem; height: .3rem; border-left: 2px solid #fff; border-bottom: 2px solid #fff;
        transform: rotate(-45deg) scale(0) translateY(-1px); transition: transform .15s ease;
    }
    .auth-card .auth-check input:checked { background-image: linear-gradient(135deg, rgb(var(--p1-rgb)), rgb(var(--p2-rgb))); border-color: transparent; }
    .auth-card .auth-check input:checked::after { transform: rotate(-45deg) scale(1) translateY(-1px); }
    .auth-card .auth-check input:focus-visible { outline: 2px solid rgb(var(--p1-rgb)); outline-offset: 2px; }

    /* Tombol utama: lapisan gelap tipis menjaga teks putih tetap terbaca di atas kartu terang */
    .auth-card .btn-glass {
        background:
            linear-gradient(rgba(30, 27, 75, .26), rgba(30, 27, 75, .26)),
            linear-gradient(135deg, rgb(var(--p1-rgb) / .96), rgb(var(--p2-rgb) / .92) 55%, rgb(var(--p3-rgb) / .92));
        font-size: 1rem; cursor: pointer;
    }
    .auth-card .btn-glass[disabled] { opacity: .8; cursor: wait; transform: none; }
    .auth-spinner {
        display: inline-block; width: 1em; height: 1em; margin-right: .55rem; vertical-align: -.15em;
        border: 2px solid rgba(255, 255, 255, .4); border-top-color: #fff; border-radius: 9999px; animation: authPutar .7s linear infinite;
    }
    @keyframes authPutar { to { transform: rotate(360deg); } }

    .auth-foot { margin: 1.5rem 0 0; text-align: center; font-size: .875rem; color: var(--au-muted); }
    .auth-link { color: var(--au-link); font-weight: 700; text-decoration: none; }
    .auth-link:hover { text-decoration: underline; text-underline-offset: 3px; }

    /* ============ PESAN ============ */
    .auth-alert { display: flex; gap: .65rem; align-items: flex-start; margin-bottom: 1.1rem; padding: .75rem .9rem; border-radius: 1rem; font-size: .85rem; line-height: 1.4; border: 1px solid; }
    .auth-alert .auth-ico { flex: none; margin-top: .1rem; }
    .auth-alert ul { margin: 0; padding: 0; list-style: none; }
    .auth-alert-error { background: var(--au-err-bg); border-color: var(--au-err-border); color: var(--au-err-text); }
    .auth-alert-ok { background: var(--au-ok-bg); border-color: var(--au-ok-border); color: var(--au-ok-text); }

    /* Syarat password (partials.password-hint) jadi deretan label kecil yang hijau saat terpenuhi */
    .auth-card ul.pw-hint { display: flex; flex-wrap: wrap; gap: .35rem .45rem; margin: 0; padding: 0; list-style: none; }
    .auth-card ul.pw-hint > li { margin-top: 0 !important; padding: .15rem .6rem; border-radius: 9999px; font-size: .72rem; background: var(--au-chip); color: var(--au-muted); transition: background .2s ease, color .2s ease; }
    .auth-card ul.pw-hint > li.text-emerald-600 { background: rgba(16, 185, 129, .18); color: var(--au-ok); }
</style>
@endverbatim
