<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Ekstrakurikuler</title>
    @include('partials.head')
</head>
<body class="app-bg">
    <div class="theme-fixed">@include('partials.theme-toggle')</div>
    @include('partials.auth-bg')

    <main class="relative min-h-screen grid lg:grid-cols-2">
        {{-- Kiri: gambar anime (opsional) atau bola kaca fitur tambahan nanti--}}
        <section class="hidden lg:flex flex-col items-center justify-center p-12 text-center" data-aos="fade-right">
            @if(file_exists(public_path('images/login-anime.png')))
                <img src="{{ asset('images/login-anime.png') }}" alt="" class="float-slow max-h-[60vh] drop-shadow-2xl">
            @else
                <div class="orb"></div>
            @endif
            <h2 class="mt-10 text-4xl font-extrabold leading-tight text-gray-800">
                <span class="text-aurora">Temukan</span> ekskul<br>favoritmu
            </h2>
            <p class="mt-3 max-w-sm text-gray-600">Daftar, pantau status pendaftaran, dan ikuti pengumuman kegiatan dalam satu tempat.</p>
        </section>

        {{-- Kanan: form nyatu sama wallpaper (tanpa kartu) --}}
        <section class="login-pane flex items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-sm" data-aos="fade-up">
                <h1 class="text-3xl font-extrabold text-gray-800">Masuk</h1>
                <p class="text-gray-600 text-sm mt-1 mb-8">Masuk untuk mendaftar atau mengelola ekstrakurikuler.</p>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif

                <form action="{{ route('login.process') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com" class="field-glass">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Password</label>
                        <input type="password" name="password" required placeholder="••••••••" class="field-glass">
                    </div>
                    <label class="flex items-center text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="mr-2 accent-indigo-500"> Ingat saya
                    </label>
                    <button type="submit" class="btn-glass">Masuk</button>
                </form>

                <p class="text-sm text-gray-600 mt-8 text-center">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:underline">Daftar</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>