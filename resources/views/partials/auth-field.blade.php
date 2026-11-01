{{--
    Satu isian untuk halaman masuk/daftar: label, ikon di kiri, dan (untuk password) tombol mata.
    Wajib: nama, label, ikon. Opsional: tipe, placeholder, autocomplete, minlength, mode, fokus, tandai, wadah.
    Pemakaian: @include('partials.auth-field', ['nama' => 'email', 'label' => 'Email', 'ikon' => 'mail', 'tipe' => 'email'])
--}}
@php
    $tipe = $tipe ?? 'text';
    $idField = 'f-'.$nama;
    $salah = $tandai ?? $errors->has($nama);
@endphp
<div class="{{ $wadah ?? '' }}">
    <label for="{{ $idField }}" class="auth-label">{{ $label }}</label>
    <div class="auth-field">
        @include('partials.auth-icon', ['ikon' => $ikon, 'kelas' => 'auth-field-ico'])
        <input id="{{ $idField }}" type="{{ $tipe }}" name="{{ $nama }}"
               @if($tipe !== 'password') value="{{ old($nama) }}" @endif
               required
               @if(!empty($fokus)) autofocus @endif
               @isset($placeholder) placeholder="{{ $placeholder }}" @endisset
               @isset($autocomplete) autocomplete="{{ $autocomplete }}" @endisset
               @isset($minlength) minlength="{{ $minlength }}" @endisset
               @isset($mode) inputmode="{{ $mode }}" @endisset
               @if($salah) aria-invalid="true" @endif
               class="field-glass {{ $tipe === 'password' ? 'has-eye' : '' }} {{ $salah ? 'is-invalid' : '' }}">
        @if($tipe === 'password')
            <button type="button" class="auth-eye" data-eye="{{ $idField }}" aria-pressed="false" aria-label="Tampilkan password" title="Tampilkan/sembunyikan password">
                @include('partials.auth-icon', ['ikon' => 'mata', 'kelas' => 'eye-on'])
                @include('partials.auth-icon', ['ikon' => 'mata-tutup', 'kelas' => 'eye-off'])
            </button>
        @endif
    </div>
</div>
