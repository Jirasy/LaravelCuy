@php
    $palets = [
        'aurora' => ['Aurora', '#6366f1', '#a855f7', '#38bdf8'],
        'sunset' => ['Sunset', '#f97316', '#ec4899', '#a855f7'],
        'ocean'  => ['Ocean',  '#0ea5e9', '#14b8a6', '#3b82f6'],
        'sakura' => ['Sakura', '#ec4899', '#f472b6', '#c084fc'],
        'forest' => ['Forest', '#10b981', '#14b8a6', '#84cc16'],
    ];
    $navbarGaya = [
        'melayang' => ['Melayang', 'Kartu putih mengambang (bawaan)'],
        'kaca'     => ['Kaca', 'Bening dan berkilau'],
        'solid'    => ['Solid', 'Bar datar dengan garis warna'],
        'ringkas'  => ['Ringkas', 'Kecil dan padat'],
    ];
    $kartuGaya = [
        'kaca'    => ['Kaca', 'Tembus pandang'],
        'gradasi' => ['Gradasi', 'Warna penuh'],
        'polos'   => ['Polos', 'Rapi dan bersih'],
    ];
    $aksenLabel = ['ikut_palet' => 'Ikut palet', 'indigo' => 'Indigo', 'rose' => 'Rose', 'emerald' => 'Emerald', 'amber' => 'Amber', 'sky' => 'Sky'];

    $paletSaya  = old('palet', $user->palet ?? 'aurora');
    $navbarSaya = old('navbar_gaya', $user->navbar_gaya ?? 'melayang');
    $kartuSaya  = old('kartu_gaya', $user->kartu_gaya ?? 'kaca');
    $aksenSaya  = old('kartu_aksen', $user->kartu_aksen ?? 'ikut_palet');

    // Pratinjau kartu memakai pilihan yang sedang dipilih di form
    $contoh = clone $user;
    $contoh->kartu_gaya  = $kartuSaya;
    $contoh->kartu_aksen = $aksenSaya;
    $contohBadge = collect([['ekskul' => 'Futsal', 'jabatan' => 'Ketua'], ['ekskul' => 'English Club', 'jabatan' => null]]);
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
            <a href="{{ route('profil.show') }}" class="text-gray-600 hover:underline text-sm">← Profilku</a>
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

        <p class="text-sm text-gray-600 mb-4">Foto, nama, nomor HP, dan bio diatur di <a href="{{ route('profil.edit') }}" class="text-indigo-600 font-semibold hover:underline">Edit Profil</a>.</p>

        {{-- ===== TAMPILAN ===== --}}
        <form action="{{ route('pengaturan.tampilan') }}" method="POST" enctype="multipart/form-data"
              class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-6 mb-6" data-aos="fade-up">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-bold text-gray-800">Tampilan</h2>

            {{-- Palet wallpaper --}}
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

            {{-- Wallpaper foto --}}
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-1">Wallpaper foto sendiri <span class="font-normal text-gray-400">(opsional)</span></p>
                <input type="file" name="wallpaper" id="input-wallpaper" accept="image/png,image/jpeg,image/webp" class="text-xs">
                <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau WEBP. Maksimal 4 MB. Gambar diberi lapisan lembut supaya teks tetap terbaca.</p>
                @if($user->wallpaper)
                    <button type="submit" form="form-hapus-wallpaper" onclick="return confirm('Hapus wallpaper foto?')" class="text-xs text-red-600 hover:underline mt-1">Hapus wallpaper foto</button>
                @endif
            </div>

            {{-- Gaya navbar --}}
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Gaya navbar <span class="font-normal text-gray-400">(pratinjau langsung di navbar atas)</span></p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($navbarGaya as $key => $g)
                        <label class="cursor-pointer">
                            <input type="radio" name="navbar_gaya" value="{{ $key }}" class="peer sr-only" {{ $navbarSaya === $key ? 'checked' : '' }}>
                            <div class="rounded-xl border-2 border-transparent peer-checked:border-indigo-500 p-3 bg-gray-50 transition h-full">
                                <p class="text-sm font-semibold text-gray-800">{{ $g[0] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $g[1] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Kartu profil --}}
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Kartu profil</p>
                <div class="grid grid-cols-3 gap-3 mb-3">
                    @foreach($kartuGaya as $key => $g)
                        <label class="cursor-pointer">
                            <input type="radio" name="kartu_gaya" value="{{ $key }}" class="peer sr-only" {{ $kartuSaya === $key ? 'checked' : '' }}>
                            <div class="rounded-xl border-2 border-transparent peer-checked:border-indigo-500 p-3 bg-gray-50 transition h-full">
                                <p class="text-sm font-semibold text-gray-800">{{ $g[0] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $g[1] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                <p class="text-xs text-gray-500 mb-2">Warna aksen</p>
                <div class="flex flex-wrap gap-3 mb-4">
                    @foreach($aksenLabel as $key => $label)
                        <label class="cursor-pointer text-center" title="{{ $label }}">
                            <input type="radio" name="kartu_aksen" value="{{ $key }}" class="peer sr-only" {{ $aksenSaya === $key ? 'checked' : '' }}>
                            <span class="block w-9 h-9 rounded-full ring-2 ring-transparent ring-offset-2 peer-checked:ring-indigo-500"
                                  style="background: {{ $key === 'ikut_palet' ? 'linear-gradient(135deg, rgb(var(--p1-rgb)), rgb(var(--p2-rgb)))' : 'rgb(' . \App\Models\User::AKSEN_RGB[$key] . ')' }}"></span>
                            <span class="block text-[10px] text-gray-500 mt-1">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                @include('partials.kartu-profil', ['user' => $contoh, 'badges' => $contohBadge, 'bisaWa' => false, 'isSelf' => false, 'bisaRiwayat' => false, 'preview' => true])
                <p class="text-xs text-gray-400 mt-2">Pratinjau. Teks dan warna kartu sudah diatur supaya tetap terbaca di semua pilihan.</p>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

    <form id="form-hapus-wallpaper" action="{{ route('pengaturan.wallpaper.hapus') }}" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        (function () {
            // Pratinjau wallpaper foto saat file dipilih (belum tersimpan sebelum klik Simpan Tampilan)
            document.getElementById('input-wallpaper').addEventListener('change', function () {
                var f = this.files[0];
                if (!f) return;
                document.documentElement.style.setProperty('--wall', "url('" + URL.createObjectURL(f) + "')");
                document.documentElement.classList.add('has-wall');
            });

            // Pratinjau langsung kartu profil dan navbar
            var AKSEN = @json(array_merge(['ikut_palet' => 'var(--p1-rgb)'], \App\Models\User::AKSEN_RGB));
            var kartu = document.getElementById('kartu-preview');
            var nav = document.querySelector('.nav-bar');

            document.querySelectorAll('input[name=kartu_gaya]').forEach(function (r) {
                r.addEventListener('change', function () {
                    kartu.className = kartu.className.replace(/pk-(kaca|gradasi|polos)/, 'pk-' + this.value);
                });
            });
            document.querySelectorAll('input[name=kartu_aksen]').forEach(function (r) {
                r.addEventListener('change', function () { kartu.style.setProperty('--pk-rgb', AKSEN[this.value]); });
            });
            document.querySelectorAll('input[name=navbar_gaya]').forEach(function (r) {
                r.addEventListener('change', function () {
                    if (nav) nav.className = nav.className.replace(/nav-(melayang|kaca|solid|ringkas)/, 'nav-' + this.value);
                });
            });
        })();
    </script>
</body>
</html>