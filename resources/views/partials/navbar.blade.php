@php
    $me = auth()->user();
    // Halaman utama: navbar tampil penuh. Halaman lain: navbar tenggelam ke atas.
    $utama = request()->routeIs('ekstrakurikuler.index', 'pendaftaran.index', 'users.index');
    $aktif = 'font-semibold text-indigo-600 underline underline-offset-4';
@endphp
<div class="nav-wrap {{ $utama ? '' : 'nav-sunk' }}">
    <div class="nav-bar flex flex-wrap justify-between items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm shadow-sm {{ $utama ? 'mb-6' : '' }}"
         @if($utama) data-aos="fade-down" @endif>

        <a href="{{ route('pengaturan.edit') }}" class="flex items-center gap-3" title="Pengaturan akun">
            @include('partials.avatar', ['user' => $me, 'ukuran' => 36])
            <span>
                <strong>{{ $me->name }}</strong>
                <span class="ml-1 px-2 py-0.5 text-xs rounded-full
                    {{ $me->isAdmin() ? 'bg-purple-100 text-purple-800' : ($me->isPembina() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($me->role) }}
                </span>
            </span>
        </a>

        <nav class="flex flex-wrap items-center gap-4">
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-700 hover:underline {{ request()->routeIs('ekstrakurikuler.*') ? $aktif : '' }}">Katalog</a>
            <a href="{{ route('pendaftaran.index') }}" class="text-gray-700 hover:underline {{ request()->routeIs('pendaftaran.*') ? $aktif : '' }}">Pendaftaran</a>
            <a href="{{ route('riwayat.show') }}" class="text-gray-700 hover:underline {{ request()->routeIs('riwayat.*') ? $aktif : '' }}">Riwayatku</a>
            @if($me->isAdmin())
                <a href="{{ route('users.index') }}" class="text-gray-700 hover:underline {{ request()->routeIs('users.*') ? $aktif : '' }}">Kelola User</a>
            @endif
            <a href="{{ route('pengaturan.edit') }}" title="Pengaturan" aria-label="Pengaturan" class="theme-btn">⚙️</a>
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