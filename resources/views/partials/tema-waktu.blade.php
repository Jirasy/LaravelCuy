{{--
    Mesin tema berbasis jam lokal PERANGKAT (bukan jam server). Dipasang di <head> lewat partials.head,
    jadi dijalankan sebelum halaman tampil dan tidak ada kedip warna.

    Hasilnya dua penanda di <html>:
      - class "dark"        -> mode gelap (malam)
      - data-waktu          -> tampilan yang dipakai: pagi | siang | sore | malam
    CSS tinggal membaca penanda itu: partials/auth-style (login/daftar), partials/tema-waktu-style (latar app, navbar, saklar).

    Aturan:
      - Otomatis: 04.00-10.59 pagi, 11.00-14.59 siang, 15.00-17.59 sore, 18.00-03.59 malam (sama dengan sapaan dashboard).
      - Saklar matahari/bulan = ganti SEMENTARA: berlaku sampai pergantian terang/gelap berikutnya (jam 18.00 / 04.00),
        lalu kembali otomatis. Ubah SEMENTARA ke false kalau pilihan manual ingin permanen.
--}}
<script>
    (function () {
        var root = document.documentElement;
        var KUNCI = 'tema-sementara';
        var BATAS = { pagi: 4, siang: 11, sore: 15, malam: 18 }; // jam lokal, format 24 jam
        var SEMENTARA = true;

        function waktuDariJam(jam) {
            if (jam >= BATAS.malam || jam < BATAS.pagi) { return 'malam'; }
            if (jam >= BATAS.sore) { return 'sore'; }
            if (jam >= BATAS.siang) { return 'siang'; }
            return 'pagi';
        }

        // Kapan tampilan alami berganti terang <-> gelap berikutnya (epoch ms)
        function pergantianBerikut(sekarang) {
            var t = new Date(sekarang.getTime());
            if (waktuDariJam(sekarang.getHours()) === 'malam') {
                t.setHours(BATAS.pagi, 0, 0, 0);
                if (t <= sekarang) { t.setDate(t.getDate() + 1); }
            } else {
                t.setHours(BATAS.malam, 0, 0, 0);
            }
            return t.getTime();
        }

        // Cadangan di memori halaman bila localStorage diblokir (saklar tetap berfungsi, hanya tidak bertahan setelah muat ulang)
        var memori = null;

        // Pilihan manual ('gelap' | 'terang') yang masih berlaku, atau null = otomatis
        function bacaPilihan() {
            var data;
            try { var teks = localStorage.getItem(KUNCI); data = teks ? JSON.parse(teks) : null; } catch (e) { data = memori; }
            if (data && (data.mode === 'gelap' || data.mode === 'terang') && Date.now() < data.sampai) { return data.mode; }
            if (data) { simpanPilihan(null); } // sudah lewat masa berlakunya
            return null;
        }

        function simpanPilihan(mode) {
            memori = mode === null ? null : {
                mode: mode,
                sampai: SEMENTARA ? pergantianBerikut(new Date()) : Number.MAX_SAFE_INTEGER
            };
            try {
                if (memori === null) { localStorage.removeItem(KUNCI); } else { localStorage.setItem(KUNCI, JSON.stringify(memori)); }
            } catch (e) { /* diblokir: cukup memori */ }
        }

        function setAttr(nama, nilai) {
            if (root.getAttribute(nama) !== nilai) { root.setAttribute(nama, nilai); }
        }

        function terapkan() {
            var asli = waktuDariJam(new Date().getHours());
            var pilihan = bacaPilihan();
            var gelap = pilihan ? pilihan === 'gelap' : asli === 'malam';
            // Dipaksa terang saat malam -> pakai tampilan siang; dipaksa gelap saat siang -> tampilan malam
            var tampil = gelap ? 'malam' : (asli === 'malam' ? 'siang' : asli);

            root.classList.toggle('dark', gelap);
            setAttr('data-waktu', tampil);
            setAttr('data-waktu-asli', asli);
            setAttr('data-tema-manual', pilihan ? 'ya' : 'tidak');
            window.syncThemeSwitch();
        }

        // Status saklar untuk pembaca layar (aria-checked = mode gelap aktif) + tooltip yang menjelaskan keadaannya
        window.syncThemeSwitch = function () {
            var gelap = root.classList.contains('dark');
            var info = root.getAttribute('data-tema-manual') === 'ya'
                ? 'Tema diganti manual, kembali otomatis saat pergantian waktu. Klik untuk membalik.'
                : 'Tema otomatis mengikuti jam perangkat (sekarang ' + (root.getAttribute('data-waktu-asli') || '') + '). Klik untuk menggantinya.';
            document.querySelectorAll('[data-theme-switch]').forEach(function (el) {
                el.setAttribute('aria-checked', gelap ? 'true' : 'false');
                el.setAttribute('title', info);
            });
        };

        window.toggleTheme = function () {
            var inginGelap = !root.classList.contains('dark');
            var alamiGelap = waktuDariJam(new Date().getHours()) === 'malam';
            // Kalau pilihannya sama dengan tampilan alami saat ini, kembali ke otomatis
            simpanPilihan(inginGelap === alamiGelap ? null : (inginGelap ? 'gelap' : 'terang'));
            terapkan();
        };

        // Dipakai halaman lain bila butuh tahu waktu lokal (mis. sapaan): TemaWaktu.sekarang() -> 'pagi' | 'siang' | 'sore' | 'malam'
        window.TemaWaktu = {
            sekarang: function () { return waktuDariJam(new Date().getHours()); },
            terapkan: terapkan
        };

        try { localStorage.removeItem('theme'); } catch (e) { /* kunci lama (gelap/terang permanen) sudah tidak dipakai */ }
        terapkan();

        // Tetap segar tanpa muat ulang: tiap menit, saat tab dibuka lagi, dan saat kembali lewat tombol "back"
        setInterval(terapkan, 60000);
        document.addEventListener('visibilitychange', function () { if (!document.hidden) { terapkan(); } });
        window.addEventListener('pageshow', function (e) { if (e.persisted) { terapkan(); } });
        document.addEventListener('DOMContentLoaded', window.syncThemeSwitch);
    })();
</script>
