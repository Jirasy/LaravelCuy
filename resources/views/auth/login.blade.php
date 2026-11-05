@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="auth-card">
        <header class="auth-head">
            @include('partials.auth-sekolah')
            <h1 class="auth-title">Masuk</h1>
            <p class="auth-sub">Masuk untuk mendaftar atau mengelola ekstrakurikuler.</p>
        </header>

        @if(session('success'))
            <div class="auth-alert auth-alert-ok" role="status">
                @include('partials.auth-icon', ['ikon' => 'centang'])
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if($errors->any())
            <div class="auth-alert auth-alert-error" role="alert">
                @include('partials.auth-icon', ['ikon' => 'peringatan'])
                <ul>
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" class="auth-form" data-auth-form>
            @csrf
            @include('partials.auth-field', [
                'nama' => 'email', 'label' => 'Email', 'ikon' => 'mail', 'tipe' => 'email',
                'placeholder' => 'nama@email.com', 'autocomplete' => 'username', 'fokus' => true,
                'tandai' => $errors->has('email') || $errors->has('password'),
            ])
            @include('partials.auth-field', [
                'nama' => 'password', 'label' => 'Password', 'ikon' => 'kunci', 'tipe' => 'password',
                'placeholder' => 'Masukkan password', 'autocomplete' => 'current-password',
                'tandai' => $errors->has('email') || $errors->has('password'),
            ])

            <label class="auth-check">
                <input type="checkbox" name="remember"> Ingat saya
            </label>

            <button type="submit" class="btn-glass">Masuk</button>
        </form>

        <p class="auth-foot">
            Belum punya akun? <a href="{{ route('register') }}" class="auth-link">Daftar</a>
        </p>
    </div>
@endsection
