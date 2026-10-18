<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat {{ $user->name }}</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        {{-- Kartu profil --}}
        <div class="relative overflow-hidden rounded-2xl mb-6 shadow-lg hero-glass" data-aos="fade-down">
            <div class="relative px-8 py-8 flex flex-wrap items-center gap-5">
                @include('partials.avatar', ['user' => $user, 'ukuran' => 80])
                <div>
                    <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-sm">
                        <span class="px-2.5 py-0.5 rounded-full bg-white/60 dark:bg-white/15">{{ ucfirst($user->role) }}</span>
                        @if($user->status_sekolah)<span class="px-2.5 py-0.5 rounded-full bg-white/60 dark:bg-white/15">🎓 {{ $user->status_sekolah }}</span>@endif
                        @if($user->kelas)<span class="px-2.5 py-0.5 rounded-full bg-white/60 dark:bg-white/15">{{ $user->kelas }}</span>@endif
                        @if($user->is_alumni && $user->tahun_lulus)<span class="px-2.5 py-0.5 rounded-full bg-white/60 dark:bg-white/15">Lulus {{ $user->tahun_lulus }}</span>@endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            @foreach([
                ['Ekskul diikuti', $riwayat->where('status', 'diterima')->where('status_anggota', 'aktif')->count(), '🏫'],
                ['Prestasi', $prestasis->count(), '🏆'],
                ['Jabatan dipegang', $riwayat->filter(fn ($r) => $r->status === 'diterima' && $r->jabatanAktif->isNotEmpty())->count(), '⭐'],
            ] as $i => $s)
                <div data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                    <div class="card-hover h-full bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
                        <p class="text-2xl">{{ $s[2] }}</p>
                        <p class="text-2xl font-bold text-gray-800 leading-none mt-1">{{ $s[1] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $s[0] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Riwayat ekskul --}}
        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Riwayat Ekskul</h2>
        <div class="space-y-3 mb-8">
            @forelse($riwayat as $r)
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <div>
                            <a href="{{ route('ekstrakurikuler.show', $r->ekstrakurikuler_id) }}" class="text-lg font-bold text-gray-900 hover:underline">
                                {{ $r->ekstrakurikuler->nama_ekstrakurikuler ?? 'Ekskul dihapus' }}
                            </a>
                            <p class="text-sm text-gray-600 mt-0.5">
                                {{ $r->jabatan_label }}
                                @if($r->angkatan_ekskul) &middot; Angkatan ekskul {{ $r->angkatan_ekskul }} @endif
                                &middot; {{ $r->periode }}
                            </p>
                            @if($r->penetapanJabatans->isNotEmpty())
                                <p class="text-xs text-gray-500 mt-1">
                                    Riwayat jabatan:
                                    @foreach($r->penetapanJabatans->sortBy('periode_mulai') as $t)
                                        {{ $t->jabatan->label_lengkap ?? '-' }} ({{ $t->periode }}){{ $loop->last ? '' : ',' }}
                                    @endforeach
                                </p>
                            @endif
                        </div>
                        @if($r->status === 'diterima' && $r->status_anggota !== 'aktif')
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $r->status_anggota === 'dikeluarkan' ? 'bg-red-100 text-red-800' : 'bg-gray-200 text-gray-700' }}">
                                {{ $r->status_anggota === 'dikeluarkan' ? 'Dikeluarkan' : 'Sudah keluar' }}@if($r->tanggal_keluar) · {{ $r->tanggal_keluar->format('d-m-Y') }}@endif
                            </span>
                        @else
                            @include('partials.status-badge', ['status' => $r->status, 'label' => $r->status_label])
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada riwayat ekskul.</div>
            @endforelse
        </div>

        {{-- Prestasi --}}
        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Prestasi</h2>
        <div class="space-y-3">
            @forelse($prestasis as $p)
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">🏆 {{ $p->peringkat }}</span>
                        <span class="px-2.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800">{{ $p->tingkat_label }}</span>
                        <span class="text-xs text-gray-500">{{ $p->tahun }}</span>
                    </div>
                    <p class="font-bold text-gray-900">{{ $p->judul }}</p>
                    <p class="text-xs text-gray-500">{{ $p->ekstrakurikuler->nama_ekstrakurikuler ?? '' }}@if($p->penyelenggara) &middot; {{ $p->penyelenggara }}@endif</p>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada prestasi yang tercatat.</div>
            @endforelse
        </div>
    </div>
</body>
</html>