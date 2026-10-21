@php
    $me = auth()->user();
    // Halaman utama: navbar tampil penuh. Halaman lain: navbar tenggelam ke atas.
    $utama = request()->routeIs('ekstrakurikuler.index', 'pendaftaran.index', 'users.index');
    $aktif = 'font-semibold text-indigo-600 underline underline-offset-4';
    $gayaNav = in_array($me->navbar_gaya, \App\Models\User::NAVBAR_GAYA, true) ? $me->navbar_gaya : 'melayang';
@endphp
<div class="nav-wrap {{ $utama ? '' : 'nav-sunk' }}">
    <div class="nav-bar nav-{{ $gayaNav }} flex flex-wrap justify-between items-center gap-3 {{ $utama ? 'mb-6' : '' }}"
         @if($utama) data-aos="fade-down" @endif>

        <a href="{{ route('profil.show') }}" class="flex items-center gap-3" title="Profilku">
            @include('partials.avatar', ['user' => $me, 'ukuran' => $gayaNav === 'ringkas' ? 28 : 36])
            <span>
                <strong>{{ $me->name }}</strong>
                <span class="nav-role ml-1 px-2 py-0.5 text-xs rounded-full
                    {{ $me->isAdmin() ? 'bg-purple-100 text-purple-800' : ($me->isPembina() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($me->role) }}
                </span>
            </span>
        </a>

        <nav class="flex flex-wrap items-center gap-4">
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-700 hover:underline {{ request()->routeIs('ekstrakurikuler.*') ? $aktif : '' }}">Katalog</a>
            <a href="{{ route('pendaftaran.index') }}" class="text-gray-700 hover:underline {{ request()->routeIs('pendaftaran.*') ? $aktif : '' }}">Pendaftaran</a>
            @if($me->isStaff())
                @php $saranBaru = \App\Models\EkskulSaran::untukPengelola($me)->where('status', 'baru')->count(); @endphp
                <a href="{{ route('saran.masuk') }}" class="text-gray-700 hover:underline {{ request()->routeIs('saran.*') ? $aktif : '' }}">
                    Kritik &amp; Saran
                    @if($saranBaru > 0)
                        <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-red-600 text-white">{{ $saranBaru }}</span>
                    @endif
                </a>
            @else
                <a href="{{ route('saran.index') }}" class="text-gray-700 hover:underline {{ request()->routeIs('saran.*') ? $aktif : '' }}">Kritik &amp; Saran</a>
            @endif
            @if($me->isStaff())
                <a href="{{ route('users.index') }}" class="text-gray-700 hover:underline {{ request()->routeIs('users.*') ? $aktif : '' }}">Kelola User</a>
                @php $menungguKeputusan = \App\Models\EkskulPengunduranDiri::menungguUntuk($me)->count(); @endphp
                <a href="{{ route('pengunduran.index') }}" class="text-gray-700 hover:underline {{ request()->routeIs('pengunduran.*') ? $aktif : '' }}">
                    Pengunduran
                    @if($menungguKeputusan > 0)
                        <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-red-600 text-white">{{ $menungguKeputusan }}</span>
                    @endif
                </a>
            @endif
            <a href="{{ route('profil.show') }}" class="text-gray-700 hover:underline {{ request()->routeIs('profil.*') || request()->routeIs('riwayat.*') ? $aktif : '' }}">Profil</a>
            <a href="{{ route('pengaturan.edit') }}" title="Pengaturan" aria-label="Pengaturan" class="theme-btn">⚙️</a>
            @include('partials.pesan-task')
            @include('partials.theme-toggle')
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-red-600 hover:underline font-semibold">Keluar</button>
            </form>
        </nav>
    </div>

    @unless($utama)
        <button type="button" class="nav-handle" aria-label="Tampilkan menu" onclick="this.closest('.nav-wrap').classList.toggle('open')">⌄</button>
    @endunless
</div>