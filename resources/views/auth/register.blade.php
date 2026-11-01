@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('content')
    <div class="auth-card auth-card-lebar">
        <header class="auth-head">
            <span class="auth-badge">@include('partials.auth-icon', ['ikon' => 'user-plus', 'ukuran' => 26])</span>
            <h1 class="auth-title">Daftar Akun <span class="text-aurora">Siswa</span></h1>
            <p class="auth-sub">Buat akun untuk mendaftar ekstrakurikuler.</p>
        </header>

        @if($errors->any())
            <div class="auth-alert auth-alert-error" role="alert">
                @include('partials.auth-icon', ['ikon' => 'peringatan'])
                <ul>
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.process') }}" method="POST" class="auth-form auth-grid" data-auth-form>
            @csrf
            @include('partials.auth-field', [
                'wadah' => 'auth-span', 'nama' => 'name', 'label' => 'Nama Lengkap', 'ikon' => 'user',
                'autocomplete' => 'name', 'fokus' => true,
            ])
            @include('partials.auth-field', [
                'wadah' => 'auth-span', 'nama' => 'email', 'label' => 'Email', 'ikon' => 'mail', 'tipe' => 'email',
                'placeholder' => 'nama@email.com', 'autocomplete' => 'email',
            ])
            @include('partials.auth-field', [
                'nama' => 'nisn', 'label' => 'NISN', 'ikon' => 'kartu', 'mode' => 'numeric', 'autocomplete' => 'off',
            ])
            @include('partials.auth-field', [
                'nama' => 'kelas', 'label' => 'Kelas', 'ikon' => 'topi', 'placeholder' => 'XII PPLG', 'autocomplete' => 'off',
            ])
            @include('partials.auth-field', [
                'nama' => 'password', 'label' => 'Password', 'ikon' => 'kunci', 'tipe' => 'password',
                'minlength' => 10, 'autocomplete' => 'new-password',
            ])
            @include('partials.auth-field', [
                'nama' => 'password_confirmation', 'label' => 'Ulangi Password', 'ikon' => 'kunci', 'tipe' => 'password',
                'autocomplete' => 'new-password',
            ])

            <div class="auth-span">@include('partials.password-hint')</div>

            <div class="auth-span">
                <button type="submit" class="btn-glass">Buat Akun</button>
            </div>
        </form>

        <p class="auth-foot">
            Sudah punya akun? <a href="{{ route('login') }}" class="auth-link">Masuk</a>
        </p>
    </div>
@endsection
