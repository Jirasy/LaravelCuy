@php
    $palets = [
        'aurora' => ['Aurora', '#6366f1', '#a855f7', '#38bdf8'],
        'sunset' => ['Sunset', '#f97316', '#ec4899', '#a855f7'],
        'ocean'  => ['Ocean',  '#0ea5e9', '#14b8a6', '#3b82f6'],
        'sakura' => ['Sakura', '#ec4899', '#f472b6', '#c084fc'],
        'forest' => ['Forest', '#10b981', '#14b8a6', '#84cc16'],
    ];
    $paletSaya = old('palet', $user->palet ?? 'aurora');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-3xl mx-auto">
        @include('partials.navbar')

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Pengaturan</h1>
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Katalog</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif
        @if($user->must_change_password)
            <div class="bg-amber-100 border border-amber-400 text-amber-800 px-4 py-3 rounded-lg mb-4 text-sm">
                <strong>Password sementara.</strong> Ganti password di bagian "Ganti Password" di bawah sebelum memakai fitur lain.
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- ===== PROFIL ===== --}}
        <form action="{{ route('pengaturan.profil') }}" method="POST" enctype="multipart/form-data"
              class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4 mb-6" data-aos="fade-up">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-bold text-gray-800">Profil</h2>

            <div class="flex items-center gap-4">
                @include('partials.avatar', ['user' => $user, 'ukuran' => 72])
                <div class="text-sm">
                    <label class="block font-semibold text-gray-700 mb-1">Foto profil</label>
                    <input type="file" name="foto" accept="image/png,image/jpeg,image/webp" class="text-xs">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    @if($user->foto)
                        <button type="submit" form="form-hapus-foto" onclick="return confirm('Hapus foto profil?')" class="text-xs text-red-600 hover:underline mt-1">Hapus foto</button>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border p-2 rounded-lg mt-1">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Angkatan sekolah <span class="font-normal text-gray-400">(mis. 2025)</span></label>
                <input type="text" name="angkatan_sekolah" value="{{ old('angkatan_sekolah', $user->angkatan_sekolah) }}" class="w-full border p-2 rounded-lg mt-1">
                @if($user->is_alumni)
                    <p class="text-xs text-amber-700 mt-1">
                        Kamu terdaftar sebagai alumni
                        @if($user->tahun_lulus) (lulus {{ $user->tahun_lulus }}) @endif
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4 text-sm">
                <div><p class="text-xs text-gray-500">Email</p><p class="text-gray-800 break-all">{{ $user->email }}</p></div>
                <div><p class="text-xs text-gray-500">Kelas</p><p class="text-gray-800">{{ $user->kelas ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">Role</p><p class="text-gray-800">{{ ucfirst($user->role) }}</p></div>
            </div>
            <p class="text-xs text-gray-500">Email, kelas, dan role tidak bisa diubah di sini. Role hanya diatur lewat Kelola User.</p>

            <div class="text-right">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Simpan Profil</button>
            </div>
        </form>

        {{-- ===== TAMPILAN ===== --}}
        <form action="{{ route('pengaturan.tampilan') }}" method="POST" enctype="multipart/form-data"
              class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4 mb-6" data-aos="fade-up">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-bold text-gray-800">Tampilan</h2>

            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Palet warna wallpaper <span class="font-normal text-gray-400">(langsung terlihat sebagai pratinjau)</span></p>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    @foreach($palets as $key => $p)
                        <label class="cursor-pointer">
                            <input type="radio" name="palet" value="{{ $key }}" class="peer sr-only" {{ $paletSaya === $key ? 'checked' : '' }}
                                   onchange="document.documentElement.dataset.palet = this.value">
                            <div class="rounded-xl border-2 border-transparent peer-checked:border-indigo-500 p-2 bg-gray-50 text-center transition">
                                <div class="h-12 rounded-lg" style="background:linear-gradient(135deg,{{ $p[1] }},{{ $p[2] }},{{ $p[3] }})"></div>
                                <p class="text-xs font-semibold text-gray-700 mt-2">{{ $p[0] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-gray-700 mb-1">Wallpaper foto sendiri <span class="font-normal text-gray-400">(opsional)</span></p>
                <input type="file" name="wallpaper" id="input-wallpaper" accept="image/png,image/jpeg,image/webp" class="text-xs">
                <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau WEBP. Maksimal 4 MB. Gambar diberi lapisan lembut supaya teks tetap terbaca.</p>
                @if($user->wallpaper)
                    <button type="submit" form="form-hapus-wallpaper" onclick="return confirm('Hapus wallpaper foto?')" class="text-xs text-red-600 hover:underline mt-1">Hapus wallpaper foto</button>
                @endif
            </div>

            <div class="text-right">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Simpan Tampilan</button>
            </div>
        </form>

        {{-- ===== KEAMANAN ===== --}}
        <form action="{{ route('pengaturan.password') }}" method="POST"
              class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4 mb-6" data-aos="fade-up">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-bold text-gray-800">Ganti Password</h2>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Password saat ini</label>
                <input type="password" name="current_password" required autocomplete="current-password" class="w-full border p-2 rounded-lg mt-1">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Password baru</label>
                    <input type="password" name="password" required minlength="10" autocomplete="new-password" class="w-full border p-2 rounded-lg mt-1">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Ulangi password baru</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full border p-2 rounded-lg mt-1">
                </div>
            </div>
            @include('partials.password-hint')
            <div class="text-right">
                <button type="submit" class="bg-gray-800 text-white px-5 py-2 rounded-lg font-semibold hover:bg-gray-900">Ganti Password</button>
            </div>
        </form>
    </div>

    {{-- Form hapus (dipisah karena form tidak boleh bersarang) --}}
    <form id="form-hapus-foto" action="{{ route('pengaturan.foto.hapus') }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
    <form id="form-hapus-wallpaper" action="{{ route('pengaturan.wallpaper.hapus') }}" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        // Pratinjau wallpaper langsung saat file dipilih (belum tersimpan sebelum klik Simpan Tampilan)
        document.getElementById('input-wallpaper').addEventListener('change', function () {
            var f = this.files[0];
            if (!f) return;
            document.documentElement.style.setProperty('--wall', "url('" + URL.createObjectURL(f) + "')");
            document.documentElement.classList.add('has-wall');
        });
    </script>
</body>
</html>