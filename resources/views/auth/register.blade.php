<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Ekstrakurikuler</title>
    @include('partials.head')
</head>
<body class="app-bg">
    <div class="theme-fixed">@include('partials.theme-toggle')</div>
    @include('partials.auth-bg')

    <main class="relative min-h-screen flex items-center justify-center p-6">
        <div class="pane-soft w-full max-w-md p-8 md:p-10" data-aos="fade-up">
            <h1 class="text-3xl font-extrabold text-gray-800">Daftar Akun <span class="text-aurora">Siswa</span></h1>
            <p class="text-gray-600 text-sm mt-1 mb-8">Buat akun untuk mendaftar ekstrakurikuler.</p>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.process') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="field-glass">
                </div>
                <div>
                    <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="field-glass">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">NISN</label>
                        <input type="text" name="nisn" value="{{ old('nisn') }}" required class="field-glass">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Kelas</label>
                        <input type="text" name="kelas" value="{{ old('kelas') }}" placeholder="XII PPLG" required class="field-glass">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Password</label>
                    <input type="password" name="password" required minlength="10" class="field-glass">
                </div>
                @include('partials.password-hint')
                <div>
                    <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Ulangi Password</label>
                    <input type="password" name="password_confirmation" required class="field-glass">
                </div>
                <div class="pt-2"><button type="submit" class="btn-glass">Buat Akun</button></div>
            </form>

            <p class="text-sm text-gray-600 mt-8 text-center">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">Masuk</a>
            </p>
        </div>
    </main>
</body>
</html>