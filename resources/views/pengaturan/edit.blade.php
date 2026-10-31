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

    $tipeWallpaper = ['default' => 'Default (palet)', 'polos' => 'Warna polos', 'gradasi' => 'Gradasi', 'foto' => 'Foto / GIF', 'url' => 'Link gambar'];
    $tipeAksen     = ['preset' => 'Bawaan', 'polos' => 'Warna polos', 'gradasi' => 'Gradasi', 'foto' => 'Gambar', 'url' => 'Link gambar'];
    $modeLabel     = ['tutupi' => ['Tutupi layar', 'Gambar memenuhi seluruh area'], 'ulang' => ['Ulang seperti pola', 'Cocok untuk motif batik atau pola kecil']];

    $paletSaya  = old('palet', $user->palet ?? 'aurora');
    $navbarSaya = old('navbar_gaya', $user->navbar_gaya ?? 'melayang');
    $kartuSaya  = old('kartu_gaya', $user->kartu_gaya ?? 'kaca');
    $aksenSaya  = old('kartu_aksen', $user->kartu_aksen ?? 'ikut_palet');

    $wpTipe = old('wallpaper_tipe', $user->wallpaper_tipe ?? 'default');
    $wpW1   = old('wallpaper_warna1', $user->wallpaper_warna1 ?? '#6366f1');
    $wpW2   = old('wallpaper_warna2', $user->wallpaper_warna2 ?? '#ec4899');
    $wpUrl  = old('wallpaper_url', $user->wallpaper_url);
    $wpMode = old('wallpaper_mode', $user->wallpaper_mode ?? 'tutupi');

    $akTipe = old('aksen_tipe', $user->aksen_tipe ?? 'preset');
    $akW1   = old('aksen_warna1', $user->aksen_warna1 ?? '#6366f1');
    $akW2   = old('aksen_warna2', $user->aksen_warna2 ?? '#ec4899');
    $akUrl  = old('aksen_url', $user->aksen_url);
    $akMode = old('aksen_mode', $user->aksen_mode ?? 'tutupi');

    // Pratinjau kartu pake pilihan yang lagi dipilih di form (nilainya divalidasi lagi saat digambar)
    $contoh = clone $user;
    $contoh->kartu_gaya   = $kartuSaya;
    $contoh->kartu_aksen  = $aksenSaya;
    $contoh->aksen_tipe   = $akTipe;
    $contoh->aksen_warna1 = $akW1;
    $contoh->aksen_warna2 = $akW2;
    $contoh->aksen_url    = $akUrl;
    $contoh->aksen_mode   = $akMode;
    $contohBadge = collect([['ekskul' => 'Futsal', 'jabatan' => 'Ketua'], ['ekskul' => 'English Club', 'jabatan' => null]]);

    $kelasPil  = 'inline-block px-3 py-1.5 rounded-full text-xs font-semibold border border-gray-300 bg-gray-50 text-gray-700 transition '
               . 'peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-400';
    $kelasWarna = 'h-10 w-16 p-1 border border-gray-300 rounded-lg cursor-pointer';
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
        <form action="{{ route('pengaturan.tampilan') }}" method="POST" enctype="multipart/form-data" id="form-tampilan"
              class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-8 mb-6" data-aos="fade-up">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-bold text-gray-800">Tampilan</h2>

            {{-- ---------- Wallpaper ---------- --}}
            <section>
                <p class="text-sm font-semibold text-gray-700 mb-2">Wallpaper <span class="font-normal text-gray-400">(langsung terlihat sebagai pratinjau)</span></p>

                <div class="flex flex-wrap gap-2 mb-4" role="radiogroup" aria-label="Jenis wallpaper">
                    @foreach($tipeWallpaper as $kode => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="wallpaper_tipe" value="{{ $kode }}" data-grup="wp" class="peer sr-only" @checked($wpTipe === $kode)>
                            <span class="{{ $kelasPil }}">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Default: palet --}}
                <div data-tampil-wp="default" class="{{ $wpTipe === 'default' ? '' : 'hidden' }}">
                    <p class="text-xs text-gray-500 mb-2">Palet warna</p>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        @foreach($palets as $key => $p)
                            <label class="cursor-pointer">
                                <input type="radio" name="palet" value="{{ $key }}" class="peer sr-only" @checked($paletSaya === $key)
                                       onchange="document.documentElement.dataset.palet = this.value">
                                <div class="rounded-xl border-2 border-transparent peer-checked:border-indigo-500 p-2 bg-gray-50 text-center transition">
                                    <div class="h-12 rounded-lg" style="background:linear-gradient(135deg,{{ $p[1] }},{{ $p[2] }},{{ $p[3] }})"></div>
                                    <p class="text-xs font-semibold text-gray-700 mt-2">{{ $p[0] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Warna polos / gradasi --}}
                <div data-tampil-wp="polos gradasi" class="flex flex-wrap items-end gap-4 {{ in_array($wpTipe, ['polos', 'gradasi'], true) ? '' : 'hidden' }}">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1" for="wallpaper_warna1">Warna</label>
                        <input type="color" id="wallpaper_warna1" name="wallpaper_warna1" value="{{ $wpW1 }}" class="{{ $kelasWarna }}">
                    </div>
                    <div data-tampil-wp="gradasi" class="{{ $wpTipe === 'gradasi' ? '' : 'hidden' }}">
                        <label class="block text-xs text-gray-500 mb-1" for="wallpaper_warna2">Warna kedua</label>
                        <input type="color" id="wallpaper_warna2" name="wallpaper_warna2" value="{{ $wpW2 }}" class="{{ $kelasWarna }}">
                    </div>
                    <p class="text-xs text-gray-500 basis-full">Warna terlalu terang atau gelap disesuaikan otomatis supaya teks tetap terbaca.</p>
                </div>

                {{-- Foto / GIF dari perangkat --}}
                <div data-tampil-wp="foto" class="{{ $wpTipe === 'foto' ? '' : 'hidden' }}">
                    <input type="file" name="wallpaper" id="input-wallpaper" accept="image/png,image/jpeg,image/webp,image/gif" class="text-xs">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, WEBP, atau GIF animasi. Maksimal 4 MB. Gambar diberi lapisan lembut supaya teks tetap terbaca. GIF diam saat perangkatmu memakai mode "kurangi gerakan".</p>
                    @if($user->wallpaper)
                        <p class="text-xs text-gray-600 mt-1">Sudah ada berkas tersimpan. Pilih berkas baru untuk menggantinya.
                            <button type="submit" form="form-hapus-wallpaper" onclick="return confirm('Hapus wallpaper foto?')" class="text-red-600 hover:underline">Hapus berkas</button></p>
                    @endif
                </div>

                {{-- Link gambar --}}
                <div data-tampil-wp="url" class="{{ $wpTipe === 'url' ? '' : 'hidden' }}">
                    <label class="block text-xs text-gray-500 mb-1" for="wallpaper_url">Link gambar atau GIF</label>
                    <input type="url" id="wallpaper_url" name="wallpaper_url" value="{{ $wpUrl }}" maxlength="500" placeholder="https://contoh.com/gambar.jpg"
                           pattern="https://.*" title="Link harus diawali https://" class="w-full border p-2 rounded-lg text-sm">
                    <p class="text-xs text-gray-500 mt-1">Harus diawali https://. Gambar dimuat langsung dari situs asalnya, jadi pemilik situs itu bisa melihat alamat IP-mu. Kalau gambar tidak muncul, situs asalnya mungkin melarang dipakai di tempat lain.</p>
                </div>

                {{-- Mode: tutupi atau ulang (batik) --}}
                <div data-tampil-wp="foto url" class="mt-4 {{ in_array($wpTipe, ['foto', 'url'], true) ? '' : 'hidden' }}">
                    <p class="text-xs text-gray-500 mb-2">Cara menampilkan</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($modeLabel as $kode => $m)
                            <label class="cursor-pointer">
                                <input type="radio" name="wallpaper_mode" value="{{ $kode }}" class="peer sr-only" @checked($wpMode === $kode)>
                                <div class="rounded-xl border-2 border-transparent peer-checked:border-indigo-500 p-3 bg-gray-50 transition h-full">
                                    <p class="text-sm font-semibold text-gray-800">{{ $m[0] }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $m[1] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ---------- Gaya navbar ---------- --}}
            <section>
                <p class="text-sm font-semibold text-gray-700 mb-2">Gaya navbar <span class="font-normal text-gray-400">(pratinjau langsung di navbar atas)</span></p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($navbarGaya as $key => $g)
                        <label class="cursor-pointer">
                            <input type="radio" name="navbar_gaya" value="{{ $key }}" class="peer sr-only" @checked($navbarSaya === $key)>
                            <div class="rounded-xl border-2 border-transparent peer-checked:border-indigo-500 p-3 bg-gray-50 transition h-full">
                                <p class="text-sm font-semibold text-gray-800">{{ $g[0] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $g[1] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- ---------- Kartu profil ---------- --}}
            <section>
                <p class="text-sm font-semibold text-gray-700 mb-2">Kartu profil</p>
                <div class="grid grid-cols-3 gap-3 mb-4">
                    @foreach($kartuGaya as $key => $g)
                        <label class="cursor-pointer">
                            <input type="radio" name="kartu_gaya" value="{{ $key }}" class="peer sr-only" @checked($kartuSaya === $key)>
                            <div class="rounded-xl border-2 border-transparent peer-checked:border-indigo-500 p-3 bg-gray-50 transition h-full">
                                <p class="text-sm font-semibold text-gray-800">{{ $g[0] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $g[1] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                <p class="text-xs text-gray-500 mb-1">Warna aksen</p>
                <p class="text-xs text-gray-500 mb-2">Warna polos, gradasi, dan gambar dipakai di seluruh situs: sapaan, kartu rekrutmen OSIS, navbar, tombol, dan kartu profil. Pilihan warna bawaan hanya mewarnai kartu profil.</p>
                <div class="flex flex-wrap gap-2 mb-4" role="radiogroup" aria-label="Jenis aksen">
                    @foreach($tipeAksen as $kode => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="aksen_tipe" value="{{ $kode }}" data-grup="ak" class="peer sr-only" @checked($akTipe === $kode)>
                            <span class="{{ $kelasPil }}">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Pilihan warna bawaan --}}
                <div data-tampil-ak="preset" class="flex flex-wrap gap-3 mb-4 {{ $akTipe === 'preset' ? '' : 'hidden' }}">
                    @foreach($aksenLabel as $key => $label)
                        <label class="cursor-pointer text-center" title="{{ $label }}">
                            <input type="radio" name="kartu_aksen" value="{{ $key }}" class="peer sr-only" @checked($aksenSaya === $key)>
                            <span class="block w-9 h-9 rounded-full ring-2 ring-transparent ring-offset-2 peer-checked:ring-indigo-500"
                                  style="background: {{ $key === 'ikut_palet' ? 'linear-gradient(135deg, rgb(var(--p1-rgb)), rgb(var(--p2-rgb)))' : 'rgb(' . \App\Models\User::AKSEN_RGB[$key] . ')' }}"></span>
                            <span class="block text-[10px] text-gray-500 mt-1">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Warna polos / gradasi --}}
                <div data-tampil-ak="polos gradasi" class="flex flex-wrap items-end gap-4 mb-4 {{ in_array($akTipe, ['polos', 'gradasi'], true) ? '' : 'hidden' }}">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1" for="aksen_warna1">Warna</label>
                        <input type="color" id="aksen_warna1" name="aksen_warna1" value="{{ $akW1 }}" class="{{ $kelasWarna }}">
                    </div>
                    <div data-tampil-ak="gradasi" class="{{ $akTipe === 'gradasi' ? '' : 'hidden' }}">
                        <label class="block text-xs text-gray-500 mb-1" for="aksen_warna2">Warna kedua</label>
                        <input type="color" id="aksen_warna2" name="aksen_warna2" value="{{ $akW2 }}" class="{{ $kelasWarna }}">
                    </div>
                    <p class="text-xs text-gray-500 basis-full">Warna terlalu terang disesuaikan otomatis supaya teks putih (tombol dan kartu) tetap terbaca.</p>
                </div>

                {{-- Gambar dari perangkat --}}
                <div data-tampil-ak="foto" class="mb-4 {{ $akTipe === 'foto' ? '' : 'hidden' }}">
                    <input type="file" name="aksen_gambar" id="input-aksen" accept="image/png,image/jpeg,image/webp,image/gif" class="text-xs">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, WEBP, atau GIF. Maksimal 4 MB. Sapaan, kartu OSIS, dan kartu profil diberi lapisan gelap tipis supaya teks putihnya terbaca. Di navbar, motifnya tampil sebagai pita di tepi bawah.</p>
                    @if($user->aksen_gambar)
                        <p class="text-xs text-gray-600 mt-1">Sudah ada gambar tersimpan. Pilih gambar baru untuk menggantinya.
                            <button type="submit" form="form-hapus-aksen" onclick="return confirm('Hapus gambar aksen?')" class="text-red-600 hover:underline">Hapus gambar</button></p>
                    @endif
                </div>

                {{-- Link gambar --}}
                <div data-tampil-ak="url" class="mb-4 {{ $akTipe === 'url' ? '' : 'hidden' }}">
                    <label class="block text-xs text-gray-500 mb-1" for="aksen_url">Link gambar</label>
                    <input type="url" id="aksen_url" name="aksen_url" value="{{ $akUrl }}" maxlength="500" placeholder="https://contoh.com/motif-batik.png"
                           pattern="https://.*" title="Link harus diawali https://" class="w-full border p-2 rounded-lg text-sm">
                    <p class="text-xs text-gray-500 mt-1">Harus diawali https://. Gambar dimuat dari situs asalnya, jadi pemilik situs itu bisa melihat alamat IP pengunjung profilmu.</p>
                </div>

                {{-- Mode: tutupi atau ulang (batik) --}}
                <div data-tampil-ak="foto url" class="mb-4 {{ in_array($akTipe, ['foto', 'url'], true) ? '' : 'hidden' }}">
                    <p class="text-xs text-gray-500 mb-2">Cara menampilkan</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($modeLabel as $kode => $m)
                            <label class="cursor-pointer">
                                <input type="radio" name="aksen_mode" value="{{ $kode }}" class="peer sr-only" @checked($akMode === $kode)>
                                <div class="rounded-xl border-2 border-transparent peer-checked:border-indigo-500 p-3 bg-gray-50 transition h-full">
                                    <p class="text-sm font-semibold text-gray-800">{{ $m[0] }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $m[1] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="hero-glass rounded-2xl px-5 py-4 mb-3" id="sapaan-preview">
                    <p class="text-lg font-bold">Halo, {{ $user->sapaan }}! 👋</p>
                    <p class="text-sm opacity-80">Siap untuk jadwal ekskul hari ini?</p>
                </div>
                @include('partials.kartu-profil', ['user' => $contoh, 'badges' => $contohBadge, 'bisaWa' => false, 'isSelf' => false, 'bisaRiwayat' => false, 'preview' => true])
                <p class="text-xs text-gray-400 mt-2">Contoh pratinjau sapaan dan kartu profil. Navbar di atas juga ikut berubah. Teks dan warna sudah diatur supaya tetap terbaca di semua pilihan.</p>
            </section>

            <div class="text-right">
                <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-emerald-700">Simpan Tampilan</button>
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
    <form id="form-hapus-aksen" action="{{ route('pengaturan.aksen.hapus') }}" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        (function () {
            var form = document.getElementById('form-tampilan');
            var root = document.documentElement;
            var kartu = document.getElementById('kartu-preview');
            var nav = document.querySelector('.nav-bar');
            var inputWp = document.getElementById('input-wallpaper');
            var inputAk = document.getElementById('input-aksen');

            var ENDPOINT = @json(route('pengaturan.pratinjau'));
            var ALAMAT_PRATINJAU = /https:\/\/pratinjau\.invalid\/berkas(\.gif)?/g;
            var NAMA_TIPE = { wp: 'wallpaper_tipe', ak: 'aksen_tipe' };
            var KELAS_WALL = ['has-wall', 'has-wall-warna', 'wall-ulang', 'wall-anim', 'has-aksen-warna', 'has-aksen-gambar'];

            // Alamat sementara (blob:) untuk berkas yang baru dipilih tapi belum disimpan
            var blob = { wp: null, ak: null };
            var tunda = null, urutan = 0;

            // Panel yang tampil mengikuti jenis yang dipilih
            function tampilkanPanel(grup) {
                var dipilih = form.querySelector('input[name="' + NAMA_TIPE[grup] + '"]:checked');
                var nilai = dipilih ? dipilih.value : '';
                form.querySelectorAll('[data-tampil-' + grup + ']').forEach(function (el) {
                    el.classList.toggle('hidden', el.getAttribute('data-tampil-' + grup).split(' ').indexOf(nilai) === -1);
                });
            }

            function jenisBerkas(input) {
                var f = input && input.files && input.files[0];
                if (!f) return '';
                return (f.type === 'image/gif' || /\.gif$/i.test(f.name)) ? 'gif' : 'gambar';
            }

            function ganti(teks, blob) {
                return blob ? teks.replace(ALAMAT_PRATINJAU, blob) : teks;
            }

            function terapkan(h) {
                KELAS_WALL.forEach(function (c) { root.classList.remove(c); });
                h.wallpaper.kelas.concat(h.aksen.kelas).forEach(function (c) { root.classList.add(c); });
                root.setAttribute('style', (ganti(h.wallpaper.css, blob.wp) + ' ' + ganti(h.aksen.css, blob.ak)).trim());

                kartu.className = 'pk ' + h.kartu.kelas;
                kartu.setAttribute('style', ganti(h.kartu.style, blob.ak));
            }

            // Pratinjau memakai logika server yang sama dengan tampilan sebenarnya (kontras warna ikut disesuaikan)
            function pratinjau() {
                clearTimeout(tunda);
                tunda = setTimeout(function () {
                    var data = new FormData(form);
                    data.delete('_method');
                    data.delete('wallpaper');
                    data.delete('aksen_gambar');
                    if (jenisBerkas(inputWp)) data.append('wallpaper_berkas', jenisBerkas(inputWp));
                    if (jenisBerkas(inputAk)) data.append('aksen_berkas', jenisBerkas(inputAk));

                    var saya = ++urutan;
                    fetch(ENDPOINT, {
                        method: 'POST', body: data, credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(function (r) { return r.ok ? r.json() : null; })
                        .then(function (h) { if (h && saya === urutan) terapkan(h); })
                        .catch(function () { /* pratinjau gagal: tampilan lama dibiarkan */ });
                }, 200);
            }

            // Berkas yang dipilih dipratinjau lewat blob URL lokal; blob lama dilepas supaya tidak menumpuk di memori
            function pegangBerkas(input, kunci) {
                if (!input) return;
                input.addEventListener('change', function () {
                    if (blob[kunci]) URL.revokeObjectURL(blob[kunci]);
                    blob[kunci] = input.files[0] ? URL.createObjectURL(input.files[0]) : null;
                });
            }
            pegangBerkas(inputWp, 'wp');
            pegangBerkas(inputAk, 'ak');

            form.addEventListener('change', function (e) {
                if (e.target.dataset && e.target.dataset.grup) tampilkanPanel(e.target.dataset.grup);
                pratinjau();
            });
            form.addEventListener('input', pratinjau);

            // Navbar: pratinjau langsung tanpa ke server
            document.querySelectorAll('input[name=navbar_gaya]').forEach(function (r) {
                r.addEventListener('change', function () {
                    if (nav) nav.className = nav.className.replace(/nav-(melayang|kaca|solid|ringkas)/, 'nav-' + this.value);
                });
            });

            @if(session()->hasOldInput())
                pratinjau();   // setelah gagal validasi, tampilkan lagi apa yang tadi dipilih
            @endif
        })();
    </script>
</body>
</html>
