{{-- Ikon garis untuk halaman masuk/daftar. Pemakaian: @include('partials.auth-icon', ['ikon' => 'mail', 'ukuran' => 18, 'kelas' => '']) --}}
@php
    $jalurIkon = [
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'kunci'     => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'user-plus' => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M19 8v6M16 11h6"/>',
        'kartu'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M15 9h3M15 13h3M6 17a3 3 0 0 1 6 0"/>',
        'topi'      => '<path d="M22 9 12 4 2 9l10 5 10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/><path d="M22 9v6"/>',
        'mata'      => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'mata-tutup' => '<path d="M3 3l18 18"/><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
        'peringatan' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>',
        'centang'   => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
    ];
@endphp
<svg class="auth-ico {{ $kelas ?? '' }}" viewBox="0 0 24 24" width="{{ $ukuran ?? 18 }}" height="{{ $ukuran ?? 18 }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $jalurIkon[$ikon] ?? '' !!}</svg>
