<?php

// Identitas sekolah untuk logo web, judul halaman, dan halaman masuk/daftar.
// Cukup ubah di sini (atau lewat .env) tanpa menyentuh tampilan.
//
// Mengganti logo web dengan logo resmi sekolah:
//   1. Simpan berkasnya di public/images/ (SVG atau PNG transparan, persegi, minimal 256 px), mis. public/images/logo-resmi.png
//   2. Isi SEKOLAH_LOGO=images/logo-resmi.png di .env  (atau ubah nilai bawaan di bawah)
//   3. Ganti juga public/favicon.svg dan public/favicon.ico kalau ingin ikon tab browser ikut berubah.

return [
    // Nama lengkap: halaman masuk/daftar, teks alternatif logo, dan tooltip di navbar
    'nama' => env('SEKOLAH_NAMA', 'SMKS Mahaputra Cerdas Utama'),

    // Nama ringkas di navbar dan judul tab browser
    'singkatan' => env('SEKOLAH_SINGKATAN', 'SMKS MCU'),

    // Berkas logo, relatif terhadap folder public/
    'logo' => env('SEKOLAH_LOGO', 'images/logo-sekolah.svg'),
];
