@php
    $me = auth()->user();
    // Halaman utama: navbar tampil penuh. Halaman lain: navbar tenggelam ke atas.
    $utama = request()->routeIs('ekstrakurikuler.index', 'pendaftaran.index', 'users.index');
    $gayaNav = in_array($me->navbar_gaya, \App\Models\User::NAVBAR_GAYA, true) ? $me->navbar_gaya : 'melayang';
    $profilAktif = request()->routeIs('profil.*') || request()->routeIs('riwayat.*');
    $dataNav = \App\Support\NavbarData::untuk($me);
    // Kelas satu tab menu; tab yang sedang dibuka diberi garis bawah aksen (gaya di partials/head.blade.php)
    $tab = fn (bool $aktif): string => 'nav-tab'.($aktif ? ' is-active' : '');
@endphp
<div class="nav-wrap {{ $utama ? '' : 'nav-sunk' }}">
    <div class="nav-bar nav-{{ $gayaNav }} nav-grid {{ $utama ? 'mb-6' : '' }}"
         @if($utama) data-aos="fade-down" @endif>

        {{-- Hanya foto profil (tanpa nama) supaya hemat tempat; nama & peran muncul sebagai tooltip --}}
        <a href="{{ route('profil.show') }}" class="nav-avatar {{ $profilAktif ? 'is-active' : '' }}"
           title="{{ $me->name }} · {{ ucfirst($me->role) }}" aria-label="Profil saya ({{ $me->name }})"
           @if($profilAktif) aria-current="page" @endif>
            @include('partials.avatar', ['user' => $me, 'ukuran' => $gayaNav === 'ringkas' ? 28 : 36])
        </a>

        {{-- Menu: satu baris yang bisa digeser; panah muncul otomatis kalau menunya melebihi lebar layar --}}
        <div class="nav-links" data-nav-links>
            <button type="button" class="nav-arrow nav-arrow-kiri" data-nav-arrow="-1" tabindex="-1" aria-label="Geser menu ke kiri">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </button>

            <nav class="nav-scroll" aria-label="Menu utama">
                <a href="{{ route('ekstrakurikuler.index') }}" class="{{ $tab(request()->routeIs('ekstrakurikuler.*')) }}">Katalog</a>
                <a href="{{ route('pendaftaran.index') }}" class="{{ $tab(request()->routeIs('pendaftaran.*')) }}">Pendaftaran</a>
                @if($dataNav['casisDraft'])
                    <a href="{{ route('casis.create', $dataNav['casisDraftEkskul']) }}" class="nav-tab nav-tab-alert">Lengkapi pendaftaran seleksi</a>
                @endif
                @if($dataNav['casisTugas'] > 0)
                    <a href="{{ route('casis.index', $dataNav['casisTugasEkskul']) }}" class="{{ $tab(request()->routeIs('casis.*')) }}">
                        Seleksi
                        <span class="nav-badge">{{ $dataNav['casisTugas'] }}</span>
                    </a>
                @endif
                @if($me->isStaff())
                    @php $saranBaru = \App\Models\EkskulSaran::untukPengelola($me)->where('status', 'baru')->count(); @endphp
                    <a href="{{ route('saran.masuk') }}" class="{{ $tab(request()->routeIs('saran.*')) }}">
                        Kritik &amp; Saran
                        @if($saranBaru > 0)
                            <span class="nav-badge">{{ $saranBaru }}</span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('saran.index') }}" class="{{ $tab(request()->routeIs('saran.*')) }}">Kritik &amp; Saran</a>
                @endif
                @if($me->isStaff())
                    <a href="{{ route('users.index') }}" class="{{ $tab(request()->routeIs('users.*')) }}">Kelola User</a>
                    <a href="{{ route('import-siswa.create') }}" class="{{ $tab(request()->routeIs('import-siswa.*')) }}">Import Siswa</a>
                    @if($me->isAdmin())
                        <a href="{{ route('kredensial.index') }}" class="{{ $tab(request()->routeIs('kredensial.*')) }}">Kredensial</a>
                        <a href="{{ route('jenis-ekskul.index') }}" class="{{ $tab(request()->routeIs('jenis-ekskul.*')) }}">Tipe Ekskul</a>
                        <a href="{{ route('kategori-ekskul.index') }}" class="{{ $tab(request()->routeIs('kategori-ekskul.*')) }}">Kategori Ekskul</a>
                    @endif
                    @php $menungguKeputusan = \App\Models\EkskulPengunduranDiri::menungguUntuk($me)->count(); @endphp
                    <a href="{{ route('pengunduran.index') }}" class="{{ $tab(request()->routeIs('pengunduran.*')) }}">
                        Pengunduran
                        @if($menungguKeputusan > 0)
                            <span class="nav-badge">{{ $menungguKeputusan }}</span>
                        @endif
                    </a>
                @endif
            </nav>

            <button type="button" class="nav-arrow nav-arrow-kanan" data-nav-arrow="1" tabindex="-1" aria-label="Geser menu ke kanan">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>

        {{-- Aksi cepat: pengaturan, pesan/task, tema, keluar --}}
        <div class="nav-actions">
            <a href="{{ route('pengaturan.edit') }}" title="Pengaturan" aria-label="Pengaturan" class="theme-btn {{ request()->routeIs('pengaturan.*') ? 'is-active' : '' }}"
               @if(request()->routeIs('pengaturan.*')) aria-current="page" @endif>
                <svg class="ico-putar" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
            @include('partials.pesan-task')
            @include('partials.theme-toggle')
            <form action="{{ route('logout') }}" method="POST" class="flex">
                @csrf
                <button type="submit" class="theme-btn nav-logout" title="Keluar" aria-label="Keluar">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    <span class="nav-logout-label">Keluar</span>
                </button>
            </form>
        </div>
    </div>

    @unless($utama)
        <button type="button" class="nav-handle" aria-label="Tampilkan menu" onclick="this.closest('.nav-wrap').classList.toggle('open')"><x-ikon nama="chevron-bawah" :ukuran="14" :tebal="2.5" /></button>
    @endunless
</div>

<script>
    (function () {
        var wadah = document.querySelector('[data-nav-links]');
        if (!wadah) { return; }
        var geser = wadah.querySelector('.nav-scroll');

        function perbarui() {
            var sisa = geser.scrollWidth - geser.clientWidth;
            wadah.classList.toggle('can-l', geser.scrollLeft > 4);
            wadah.classList.toggle('can-r', geser.scrollLeft < sisa - 4);
        }

        geser.addEventListener('scroll', perbarui, { passive: true });
        window.addEventListener('resize', perbarui);
        if (window.ResizeObserver) { new ResizeObserver(perbarui).observe(geser); }

        wadah.querySelectorAll('[data-nav-arrow]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                geser.scrollBy({ left: Number(tombol.dataset.navArrow) * geser.clientWidth * 0.6, behavior: 'smooth' });
            });
        });

        // Bawa menu yang sedang aktif ke tengah supaya langsung kelihatan
        var aktif = geser.querySelector('.is-active');
        if (aktif) { geser.scrollLeft = Math.max(0, aktif.offsetLeft - (geser.clientWidth - aktif.offsetWidth) / 2); }
        perbarui();
    })();
</script>