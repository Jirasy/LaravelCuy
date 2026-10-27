{{-- Dipasang di <head> setiap halaman: tema gelap/terang, Tailwind, AOS, sapaan, animasi --}}
<script>
    // Terapkan tema tersimpan SEBELUM halaman tampil (supaya tidak kedip putih)
    (function () {
        var t = localStorage.getItem('theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
    function toggleTheme() {
        var dark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    }
</script>
@auth
<script>document.documentElement.dataset.palet = @json(auth()->user()->palet ?? 'aurora');</script>
@if(auth()->user()->wallpaper_url)
<style>:root { --wall: url("{{ auth()->user()->wallpaper_url }}"); }</style>
<script>document.documentElement.classList.add('has-wall');</script>
@endif
@endauth
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config = { darkMode: 'class' }</script>
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.AOS) AOS.init({ duration: 700, once: true, easing: 'ease-out-cubic' });

        // Sapaan sesuai jam di perangkat pengguna (bukan jam server)
        var h = new Date().getHours();
        var w = h < 4  ? ['malam', 'Jangan lupa cek pengumuman ekskul sebelum istirahat 🌙']
              : h < 11 ? ['pagi',  'Siap untuk jadwal ekskul hari ini?']
              : h < 15 ? ['siang', 'Siap untuk jadwal ekskul hari ini?']
              : h < 18 ? ['sore',  'Sudah siap latihan sore ini?']
              :          ['malam', 'Jangan lupa cek pengumuman ekskul sebelum istirahat 🌙'];
        document.querySelectorAll('[data-sapaan]').forEach(function (el) {
            el.textContent = 'Selamat ' + w[0] + ', ' + el.dataset.nama + '! 👋';
        });
        // Kilau cahaya pada semua tombol berwarna (jeda acak supaya tidak serempak)
        document.querySelectorAll('button.text-white, a.text-white, .btn-glass').forEach(function (el) {
            if (!el.classList.contains('btn-glass')) el.classList.add('shine');
            el.style.setProperty('--shine-delay', (-Math.random() * 5).toFixed(2) + 's');
        });

        document.querySelectorAll('[data-sapaan-sub]').forEach(function (el) {
            el.textContent = w[1];
        });
    });
</script>
@verbatim
<style>
    html { scroll-behavior: smooth; }

    /* ============ WALLPAPER AURORA: warna bergeser pelan ============
   Ganti warna cukup lewat variabel --a1..--a4 (tiap mode punya set sendiri).
   Contoh palet lain (tinggal tempel):
   Sunset : --a1 rgba(251,146,60,.5)  --a2 rgba(244,114,182,.5) --a3 rgba(168,85,247,.45) --a4 rgba(253,224,71,.4)
   Ocean  : --a1 rgba(14,165,233,.5)  --a2 rgba(45,212,191,.45) --a3 rgba(59,130,246,.5)  --a4 rgba(165,243,252,.5)  */
:root {
    --bg-base: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 45%, #ecfeff 100%);
    --a1: rgba(129, 140, 248, .55);  /* indigo muda */
    --a2: rgba(244, 114, 182, .40);  /* pink */
    --a3: rgba(56, 189, 248, .45);   /* biru langit */
    --a4: rgba(196, 181, 253, .55);  /* lavender */
    --p1-rgb: 99 102 241; --p2-rgb: 168 85 247; --p3-rgb: 56 189 248; /* warna aksen tombol/judul */
}
html.dark {
    --bg-base: #060816;
    --a1: rgba(79, 70, 229, .65);    /* indigo */
    --a2: rgba(168, 85, 247, .55);   /* ungu */
    --a3: rgba(37, 99, 235, .55);    /* biru */
    --a4: rgba(14, 165, 233, .35);   /* cyan */
}
.app-bg { min-height: 100vh; background: var(--bg-base); background-attachment: fixed; }
.app-bg::before {
    content: ''; position: fixed; inset: -25%; z-index: -1; pointer-events: none;
    background:
        radial-gradient(circle at 15% 20%, var(--a1), transparent 42%),
        radial-gradient(circle at 85% 25%, var(--a2), transparent 44%),
        radial-gradient(circle at 55% 85%, var(--a3), transparent 48%),
        radial-gradient(circle at 8% 85%, var(--a4), transparent 40%);
    will-change: transform, filter;
    animation: auroraMove 24s ease-in-out infinite alternate, auroraHue 30s ease-in-out infinite alternate;
}
/* Bintang kecil berkedip (mode gelap saja) */
html.dark .app-bg::after {
    content: ''; position: fixed; inset: 0; z-index: -1; pointer-events: none;
    background-image:
        radial-gradient(1.5px 1.5px at 12% 18%, #fff, transparent),
        radial-gradient(1px 1px at 28% 64%, #c7d2fe, transparent),
        radial-gradient(1.5px 1.5px at 44% 32%, #fff, transparent),
        radial-gradient(1px 1px at 61% 78%, #e0e7ff, transparent),
        radial-gradient(1.5px 1.5px at 73% 14%, #fff, transparent),
        radial-gradient(1px 1px at 86% 52%, #c7d2fe, transparent),
        radial-gradient(1px 1px at 93% 86%, #fff, transparent),
        radial-gradient(1.5px 1.5px at 7% 82%, #e0e7ff, transparent);
    animation: twinkle 5s ease-in-out infinite alternate;
}
@keyframes auroraMove {
    0%   { transform: translate3d(0, 0, 0) scale(1) rotate(0deg); }
    50%  { transform: translate3d(3%, -4%, 0) scale(1.12) rotate(6deg); }
    100% { transform: translate3d(-4%, 3%, 0) scale(1.05) rotate(-5deg); }
}
@keyframes auroraHue { from { filter: hue-rotate(-25deg); } to { filter: hue-rotate(45deg); } }
@keyframes twinkle { from { opacity: .25; } to { opacity: .85; } }
@keyframes gradientShift {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Kartu di semua halaman jadi kaca buram, aurora tembus samar di belakangnya */
.app-bg .bg-white { background-color: rgba(255, 255, 255, .78); backdrop-filter: blur(12px); }
html.dark .app-bg .bg-white { background-color: rgba(30, 41, 59, .62); }

/* ============ HALAMAN LOGIN / REGISTER ============ */
.login-pane { background: transparent; }
@media (min-width: 1024px) {
    .login-pane { background: linear-gradient(to left, rgba(255, 255, 255, .5), rgba(255, 255, 255, 0)); }
    html.dark .login-pane { background: linear-gradient(to left, rgba(6, 8, 22, .55), rgba(6, 8, 22, 0)); }
}
.pane-soft {
    background: rgba(255, 255, 255, .3);
    backdrop-filter: blur(20px) saturate(140%);
    border-radius: 2rem;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .5);
}
html.dark .pane-soft { background: rgba(15, 23, 42, .38); box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08); }

.field-glass {
    width: 100%; margin-top: .35rem; padding: .7rem 1rem; border-radius: .9rem;
    color: #1e1b4b; background: rgba(255, 255, 255, .4);
    border: 1px solid rgba(255, 255, 255, .7); backdrop-filter: blur(8px);
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}
.field-glass::placeholder { color: rgba(55, 65, 81, .5); }
.field-glass:focus {
    outline: none; background: rgba(255, 255, 255, .6); border-color: rgba(129, 140, 248, .9);
    box-shadow: 0 0 0 4px rgba(129, 140, 248, .25), 0 8px 24px -8px rgba(99, 102, 241, .5);
}
html.dark .field-glass { background: rgba(255, 255, 255, .07); border-color: rgba(255, 255, 255, .16); color: #e0e7ff; }
html.dark .field-glass::placeholder { color: rgba(226, 232, 240, .4); }
html.dark .field-glass:focus {
    background: rgba(255, 255, 255, .12); border-color: rgba(165, 180, 252, .8);
    box-shadow: 0 0 0 4px rgba(129, 140, 248, .2), 0 8px 28px -8px rgba(99, 102, 241, .6);
}
.field-glass:-webkit-autofill { -webkit-box-shadow: 0 0 0 100px rgba(224, 231, 255, .95) inset; -webkit-text-fill-color: #1e1b4b; }
html.dark .field-glass:-webkit-autofill { -webkit-box-shadow: 0 0 0 100px #1e1b4b inset; -webkit-text-fill-color: #e0e7ff; }

/* Tombol serpihan kaca */
.btn-glass {
    position: relative; overflow: hidden; isolation: isolate; width: 100%;
    padding: .85rem 1rem; border-radius: 1rem; font-weight: 700; letter-spacing: .03em; color: #fff;
    background: linear-gradient(135deg, rgb(var(--p1-rgb) / .78), rgb(var(--p2-rgb) / .68) 55%, rgb(var(--p3-rgb) / .62));
    border: 1px solid rgba(255, 255, 255, .55);
    backdrop-filter: blur(12px) saturate(170%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .65), inset 0 -12px 24px rgba(30, 27, 75, .25), 0 14px 34px -10px rgb(var(--p1-rgb) / .7);
    text-shadow: 0 1px 8px rgba(30, 27, 75, .5);
    transition: transform .25s ease, box-shadow .25s ease;
}
.btn-glass::before { /* retakan / serpihan kaca */
    content: ''; position: absolute; inset: 0; z-index: -1;
    background:
        linear-gradient(115deg, transparent 0 18%, rgba(255, 255, 255, .32) 18% 19.5%, transparent 19.5% 43%, rgba(255, 255, 255, .16) 43% 44%, transparent 44% 71%, rgba(255, 255, 255, .28) 71% 72.5%, transparent 72.5%),
        linear-gradient(200deg, rgba(255, 255, 255, .22), transparent 38%);
}
.btn-glass::after { /* kilau yang menyapu: otomatis berkala, lebih cepat saat hover */
    content: ''; position: absolute; top: -20%; bottom: -20%; left: -60%; width: 38%; z-index: -1;
    background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .6), transparent);
    transform: skewX(-20deg);
    animation: shineSweep 5s ease-in-out infinite; animation-delay: var(--shine-delay, 0s);
}
.btn-glass:hover { transform: translateY(-2px); box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7), inset 0 -12px 24px rgba(30, 27, 75, .25), 0 20px 44px -10px rgb(var(--p1-rgb) / .85); }
.btn-glass:hover::after { animation-duration: 1.4s; }
.btn-glass:active { transform: translateY(0) scale(.98); }

/* Dekorasi: serpihan kaca, bola kaca, kilau */
.shard {
    position: absolute; animation: floaty 10s ease-in-out infinite;
    background: linear-gradient(135deg, rgba(255, 255, 255, .6), rgba(255, 255, 255, .08) 70%);
    backdrop-filter: blur(6px);
}
html.dark .shard { background: linear-gradient(135deg, rgba(255, 255, 255, .2), rgba(255, 255, 255, .03) 70%); }
.shard-1 { width: 8rem;  height: 11rem; left: 5%;   top: 10%;    clip-path: polygon(50% 0, 100% 35%, 80% 100%, 8% 75%); }
.shard-2 { width: 6rem;  height: 8rem;  left: 18%;  bottom: 8%;  clip-path: polygon(0 20%, 70% 0, 100% 70%, 30% 100%); animation-delay: -3s; }
.shard-3 { width: 10rem; height: 7rem;  left: 52%;  top: 6%;     clip-path: polygon(0 40%, 60% 0, 100% 55%, 35% 100%); animation-delay: -5s; }
.shard-4 { width: 7rem;  height: 10rem; right: 6%;  top: 40%;    clip-path: polygon(40% 0, 100% 30%, 70% 100%, 0 65%); animation-delay: -2s; }
.shard-5 { width: 5rem;  height: 6rem;  right: 22%; bottom: 6%;  clip-path: polygon(0 30%, 55% 0, 100% 60%, 40% 100%); animation-delay: -7s; }
.sparkle { position: absolute; color: #a5b4fc; font-size: 1.1rem; animation: twinkle 3s ease-in-out infinite alternate; }
html.dark .sparkle { color: #e0e7ff; }
.orb {
    width: 14rem; height: 14rem; border-radius: 9999px; animation: floaty 6s ease-in-out infinite;
    background: radial-gradient(circle at 30% 25%, rgba(255, 255, 255, .95), rgba(255, 255, 255, .25) 28%, rgba(99, 102, 241, .55) 62%, rgba(168, 85, 247, .6));
    box-shadow: inset -20px -30px 50px rgba(30, 27, 75, .35), 0 40px 80px -20px rgba(99, 102, 241, .6);
}
.text-aurora {
    background: linear-gradient(90deg, rgb(var(--p1-rgb)), rgb(var(--p2-rgb)), rgb(var(--p3-rgb)), rgb(var(--p1-rgb))); background-size: 300% 100%;
    -webkit-background-clip: text; background-clip: text; color: transparent; animation: gradientShift 8s linear infinite;
}

/* ---- Kartu melayang saat hover ---- */
    .card-hover { transition: transform .25s ease, box-shadow .25s ease; }
    .card-hover:hover { transform: translateY(-6px); box-shadow: 0 20px 35px -10px rgba(79, 70, 229, .3); }

    /* ---- Badge status (pill) ---- */
    .badge-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .25rem .75rem; border-radius: 9999px;
        font-size: .75rem; font-weight: 600; border: 1px solid transparent;
    }
    .badge-pill .dot { width: .45rem; height: .45rem; border-radius: 9999px; background: currentColor; }
    .badge-pending  { background: #fef9c3; color: #854d0e; border-color: #fde68a; animation: softPulse 2.2s ease-in-out infinite; }
    .badge-pending .dot { animation: dotPulse 1.4s ease-in-out infinite; }
    .badge-diterima { background: #d1fae5; color: #047857; border-color: #a7f3d0; }
    .badge-ditolak  { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
    @keyframes softPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(250, 204, 21, .45); }
        50%      { box-shadow: 0 0 0 6px rgba(250, 204, 21, 0); }
    }
    @keyframes dotPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%      { opacity: .4; transform: scale(.7); }
    }

    /* ---- Melayang (login) ---- */
    @keyframes floaty {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50%      { transform: translateY(-16px) rotate(3deg); }
    }
    .float-slow { animation: floaty 6s ease-in-out infinite; }
    .float-emoji { position: absolute; font-size: 2.4rem; opacity: .8; user-select: none; animation: floaty 7s ease-in-out infinite; }
    .blob { position: absolute; border-radius: 9999px; filter: blur(60px); opacity: .45; animation: floaty 12s ease-in-out infinite; }
    .blob-1 { width: 22rem; height: 22rem; background: #a5b4fc; top: -6rem; left: -6rem; }
    .blob-2 { width: 20rem; height: 20rem; background: #f0abfc; bottom: -7rem; right: -5rem; animation-delay: 3s; }

    .glass {
        background: rgba(255, 255, 255, .75);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, .6);
        box-shadow: 0 20px 40px -15px rgba(79, 70, 229, .25);
    }
    html.dark .glass { background: rgba(15, 23, 42, .72); border-color: rgba(148, 163, 184, .2); }

    .hero-fallback {
        background: linear-gradient(135deg, rgb(var(--p1-rgb)), rgb(var(--p2-rgb)), rgb(var(--p3-rgb)));
        background-size: 200% 200%;
        animation: gradientShift 12s ease infinite;
    }

    /* ---- Tombol ganti tema ---- */
    .theme-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.25rem; height: 2.25rem; border-radius: 9999px;
        border: 1px solid rgba(148, 163, 184, .5); background: rgba(255, 255, 255, .7);
        transition: transform .2s ease;
    }
    .theme-btn:hover { transform: rotate(15deg) scale(1.08); }
    html.dark .theme-btn { background: rgba(30, 41, 59, .8); }
    .theme-icon-sun { display: none; }
    html.dark .theme-icon-sun { display: inline; }
    html.dark .theme-icon-moon { display: none; }
    .theme-fixed { position: fixed; top: 1rem; right: 1rem; z-index: 50; }

    /* ---- Mode gelap: petakan warna Tailwind yang dipakai di view ---- */
    html.dark { color-scheme: dark; }
    html.dark body { color: #e2e8f0; }
    html.dark .bg-white { background-color: #1e293b; }
    html.dark .bg-gray-50 { background-color: #172033; }
    html.dark .bg-gray-100 { background-color: #1e293b; }
    html.dark .bg-gray-200 { background-color: #334155; }
    html.dark .bg-indigo-50 { background-color: #1e1b4b; }
    html.dark .hover\:bg-gray-50:hover { background-color: #273449; }
    html.dark .hover\:bg-gray-300:hover { background-color: #475569; }
    html.dark .hover\:bg-indigo-50:hover { background-color: #312e81; }
    html.dark .text-gray-900, html.dark .text-gray-800 { color: #f1f5f9; }
    html.dark .text-gray-700 { color: #e2e8f0; }
    html.dark .text-gray-600 { color: #cbd5e1; }
    html.dark .text-gray-500, html.dark .text-gray-400 { color: #94a3b8; }
    html.dark .text-indigo-700 { color: #a5b4fc; }
    html.dark .bg-amber-50 { background-color: #3b2a0a; }
    html.dark .border-amber-300, html.dark .border-amber-200 { border-color: #92660f; }
    html.dark .text-amber-900 { color: #fde68a; }
    html.dark .border-gray-100, html.dark .border-gray-200, html.dark .border-gray-300 { border-color: #334155; }
    html.dark input, html.dark textarea, html.dark select:not(.status-select) {
        background-color: #0f172a; color: #e2e8f0; border-color: #475569;
    }
    html.dark input::placeholder, html.dark textarea::placeholder { color: #64748b; }

    @media (prefers-reduced-motion: reduce) {
        * { animation: none !important; transition: none !important; }
    }

    /* ============ KILAU PADA SEMUA TOMBOL (cahaya lewat) ============ */
    .shine { position: relative; overflow: hidden; isolation: isolate; }
    .shine::after {
        content: ''; position: absolute; top: -30%; bottom: -30%; left: -60%; width: 35%; z-index: 1; pointer-events: none;
        background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .55), transparent);
        transform: skewX(-20deg);
        animation: shineSweep 5s ease-in-out infinite; animation-delay: var(--shine-delay, 0s);
    }
    .shine:hover::after { animation-duration: 1.4s; }
    @keyframes shineSweep { 0% { left: -60%; } 40%, 100% { left: 140%; } }

    /* ============ PALET WARNA PILIHAN USER (Pengaturan) ============ */
    html[data-palet="sunset"] { --bg-base: linear-gradient(135deg, #fff7ed 0%, #fdf2f8 45%, #faf5ff 100%); --a1: rgba(251,146,60,.5); --a2: rgba(244,114,182,.45); --a3: rgba(168,85,247,.4); --a4: rgba(253,224,71,.4); --p1-rgb: 249 115 22; --p2-rgb: 236 72 153; --p3-rgb: 168 85 247; }
    html.dark[data-palet="sunset"] { --bg-base: #0c0710; --a1: rgba(234,88,12,.55); --a2: rgba(219,39,119,.5); --a3: rgba(126,34,206,.55); --a4: rgba(250,204,21,.22); }
    html[data-palet="ocean"] { --bg-base: linear-gradient(135deg, #ecfeff 0%, #eff6ff 45%, #f0fdfa 100%); --a1: rgba(14,165,233,.5); --a2: rgba(45,212,191,.45); --a3: rgba(59,130,246,.5); --a4: rgba(165,243,252,.5); --p1-rgb: 14 165 233; --p2-rgb: 20 184 166; --p3-rgb: 59 130 246; }
    html.dark[data-palet="ocean"] { --bg-base: #04101a; --a1: rgba(2,132,199,.6); --a2: rgba(13,148,136,.5); --a3: rgba(37,99,235,.55); --a4: rgba(34,211,238,.28); }
    html[data-palet="sakura"] { --bg-base: linear-gradient(135deg, #fdf2f8 0%, #fff1f2 45%, #faf5ff 100%); --a1: rgba(244,114,182,.5); --a2: rgba(251,113,133,.4); --a3: rgba(192,132,252,.45); --a4: rgba(251,207,232,.6); --p1-rgb: 236 72 153; --p2-rgb: 244 114 182; --p3-rgb: 192 132 252; }
    html.dark[data-palet="sakura"] { --bg-base: #12070f; --a1: rgba(219,39,119,.55); --a2: rgba(225,29,72,.4); --a3: rgba(147,51,234,.5); --a4: rgba(244,114,182,.25); }
    html[data-palet="forest"] { --bg-base: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 45%, #f7fee7 100%); --a1: rgba(52,211,153,.5); --a2: rgba(163,230,53,.4); --a3: rgba(45,212,191,.45); --a4: rgba(187,247,208,.6); --p1-rgb: 16 185 129; --p2-rgb: 20 184 166; --p3-rgb: 132 204 22; }
    html.dark[data-palet="forest"] { --bg-base: #04100a; --a1: rgba(5,150,105,.6); --a2: rgba(101,163,13,.4); --a3: rgba(13,148,136,.5); --a4: rgba(52,211,153,.25); }

    /* ============ WALLPAPER FOTO SENDIRI ============ */
    html.has-wall .app-bg { background: var(--wall) center / cover no-repeat fixed; }
    html.has-wall .app-bg::before { display: none; }
    html.has-wall .app-bg::after,
    html.dark.has-wall .app-bg::after {
        content: ''; position: fixed; inset: 0; z-index: -1; pointer-events: none; animation: none;
        background: rgba(255, 255, 255, .35); backdrop-filter: blur(3px);
    }
    html.dark.has-wall .app-bg::after { background: rgba(6, 8, 22, .55); }

    /* ============ NAVBAR: tenggelam ke atas di halaman non-utama ============ */
    .nav-sunk {
        position: fixed; top: 0; left: 0; right: 0; z-index: 40; padding: .75rem 1.5rem 0; pointer-events: none;
        transform: translateY(-100%); transition: transform .35s cubic-bezier(.2, .8, .2, 1);
    }
    .nav-sunk .nav-bar { pointer-events: auto; max-width: 72rem; margin: 0 auto; }
    .nav-sunk .nav-handle {
        pointer-events: auto; position: absolute; left: 50%; bottom: -1.5rem; transform: translateX(-50%);
        width: 3.2rem; height: 1.5rem; border-radius: 0 0 1rem 1rem; border: 1px solid rgba(148, 163, 184, .4); border-top: none;
        background: rgba(255, 255, 255, .8); backdrop-filter: blur(10px); color: #475569; font-size: .85rem; line-height: 1; cursor: pointer;
    }
    html.dark .nav-sunk .nav-handle { background: rgba(30, 41, 59, .8); color: #cbd5e1; }
    .nav-sunk:hover, .nav-sunk:focus-within, .nav-sunk.open { transform: translateY(0); }

    /* ============ HERO / BANNER: kaca bening yang menyatu dengan wallpaper ============ */
    .hero-glass {
        color: #1e1b4b;
        background: linear-gradient(135deg, rgb(var(--p1-rgb) / .26), rgb(var(--p2-rgb) / .20), rgb(var(--p3-rgb) / .26));
        background-size: 200% 200%; animation: gradientShift 14s ease infinite;
        backdrop-filter: blur(16px) saturate(150%);
        border: 1px solid rgba(255, 255, 255, .65);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7), 0 18px 40px -18px rgb(var(--p1-rgb) / .45);
    }
    html.dark .hero-glass {
        color: #f1f5f9;
        background: linear-gradient(135deg, rgb(var(--p1-rgb) / .34), rgb(var(--p2-rgb) / .26), rgb(var(--p3-rgb) / .34));
        background-size: 200% 200%;
        border-color: rgba(255, 255, 255, .14);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .10), 0 18px 40px -18px rgba(0, 0, 0, .6);
    }

    /* ============ NAVBAR: 4 gaya pilihan user ============ */
    .nav-bar { padding: .75rem 1rem; font-size: .875rem; border-radius: .85rem; border: 1px solid rgba(148, 163, 184, .35); transition: background .25s ease, box-shadow .25s ease; }
    .nav-melayang { background: rgba(255, 255, 255, .78); backdrop-filter: blur(12px); box-shadow: 0 8px 24px -12px rgb(var(--p1-rgb) / .35); }
    html.dark .nav-melayang { background: rgba(30, 41, 59, .7); border-color: rgba(148, 163, 184, .2); }
    .nav-kaca { background: rgba(255, 255, 255, .3); backdrop-filter: blur(22px) saturate(160%); border-color: rgba(255, 255, 255, .65); border-radius: 1.25rem;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7), 0 10px 30px -14px rgb(var(--p1-rgb) / .5); }
    html.dark .nav-kaca { background: rgba(15, 23, 42, .35); border-color: rgba(255, 255, 255, .14);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 10px 30px -14px rgba(0, 0, 0, .6); }
    .nav-solid { background: #fff; border-radius: .6rem; border-bottom: 3px solid rgb(var(--p1-rgb)); box-shadow: 0 1px 2px rgba(15, 23, 42, .08); }
    html.dark .nav-solid { background: #1e293b; border-color: rgba(148, 163, 184, .2); border-bottom-color: rgb(var(--p1-rgb)); }
    .nav-ringkas { background: rgba(255, 255, 255, .78); backdrop-filter: blur(12px); padding: .35rem 1rem; font-size: .8rem; border-radius: 9999px; }
    html.dark .nav-ringkas { background: rgba(30, 41, 59, .7); border-color: rgba(148, 163, 184, .2); }
    .nav-ringkas .nav-role { display: none; }

    /* ============ KARTU PROFIL: 3 gaya x aksen warna (teks dijaga tetap kontras) ============ */
    .pk { position: relative; overflow: hidden; border-radius: 1.25rem; --pk-rgb: var(--p1-rgb); }
    .pk-muted { opacity: .78; }

    .pk-kaca {
        color: #1e1b4b;
        background: linear-gradient(135deg, rgb(var(--pk-rgb) / .22), rgb(var(--pk-rgb) / .08));
        backdrop-filter: blur(16px) saturate(150%);
        border: 1px solid rgba(255, 255, 255, .65);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7), 0 18px 40px -18px rgb(var(--pk-rgb) / .55);
    }
    html.dark .pk-kaca {
        color: #f1f5f9;
        background: linear-gradient(135deg, rgb(var(--pk-rgb) / .30), rgb(var(--pk-rgb) / .10));
        border-color: rgba(255, 255, 255, .14);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .10), 0 18px 40px -18px rgba(0, 0, 0, .6);
    }

    /* Gradasi: lapisan gelap 40% di atas warna aksen menjamin rasio kontras teks putih minimal 4,5:1 */
    .pk-gradasi {
        color: #fff;
        background:
            linear-gradient(rgba(15, 23, 42, .40), rgba(15, 23, 42, .40)),
            linear-gradient(135deg, rgb(var(--pk-rgb)), rgb(var(--pk-rgb) / .75));
        box-shadow: 0 18px 40px -18px rgb(var(--pk-rgb) / .7);
    }

    .pk-polos {
        color: #1e1b4b; background: #fff;
        border: 1px solid rgba(148, 163, 184, .35); border-top: 5px solid rgb(var(--pk-rgb));
        box-shadow: 0 10px 24px -14px rgba(15, 23, 42, .35);
    }
    html.dark .pk-polos { color: #f1f5f9; background: #1e293b; border-color: rgba(148, 163, 184, .25); border-top-color: rgb(var(--pk-rgb)); }

    .pk-chip { display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .7rem; border-radius: 9999px; font-size: .75rem; font-weight: 600; }
    .pk-kaca .pk-chip, .pk-polos .pk-chip { background: rgb(var(--pk-rgb) / .15); border: 1px solid rgb(var(--pk-rgb) / .35); }
    .pk-gradasi .pk-chip { background: rgba(255, 255, 255, .2); border: 1px solid rgba(255, 255, 255, .35); }
</style>
@endverbatim