{{--
  Kartu profil. Variabel:
  $user, $badges (koleksi ['ekskul','jabatan']), $bisaWa, $isSelf, $bisaRiwayat, $preview (opsional),
  $tags (opsional: koleksi ProfilTag; hanya yang berstatus 'tampil' yang digambar di kartu)
--}}
@php
    $preview = $preview ?? false;
    // Kelas dan style kartu (preset, warna polos, gradasi, atau gambar/pola). Nilainya sudah divalidasi dan dijaga kontrasnya.
    $kartu   = \App\Support\TampilanKustom::kartu($user);
@endphp
<div class="pk {{ $kartu['kelas'] }}" id="{{ $preview ? 'kartu-preview' : 'kartu-profil' }}" style="{{ $kartu['style'] }}">
    <div class="px-6 py-6 md:px-8 md:py-8">
        <div class="flex flex-wrap items-center gap-5">
            @include('partials.avatar', ['user' => $user, 'ukuran' => 88])

            <div class="min-w-0 flex-1">
                <h1 class="text-2xl md:text-3xl font-bold break-words">{{ $user->name }}</h1>
                <div class="flex flex-wrap gap-2 mt-2">
                    <span class="pk-chip">{{ ucfirst($user->role) }}</span>
                    @if($user->status_sekolah)
                        <span class="pk-chip">🎓 {{ $user->status_sekolah }}</span>
                    @endif
                    @if($user->kelas)
                        <span class="pk-chip">{{ $user->kelas }}</span>
                    @endif
                </div>
                @if($user->bio)
                    <p class="mt-3 text-sm pk-muted">“{{ $user->bio }}”</p>
                @endif
            </div>
        </div>

        @if($badges->isNotEmpty())
            <div class="mt-5">
                <p class="text-xs uppercase tracking-wide pk-muted mb-2">Ekskul</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($badges as $b)
                        <span class="pk-chip cursor-default select-none">
                            {{ $b['ekskul'] }}
                            @if($b['jabatan'])
                                <span class="opacity-60">·</span> {{ $b['jabatan'] }}
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @php
            $daftarPanggilan = $user->panggilan;
            $tagTampil = ($tags ?? collect())->where('status', 'tampil');
        @endphp

        @if($daftarPanggilan->isNotEmpty())
            <div class="mt-5">
                <p class="text-xs uppercase tracking-wide pk-muted mb-2">Nama panggilan</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($daftarPanggilan as $p)
                        <span class="pk-chip cursor-default select-none">
                            {{ $p->nama }}
                            @if($p->utama && $isSelf)
                                <span title="Dipakai untuk sapaan di dashboard">★</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @if($tagTampil->isNotEmpty())
            <div class="mt-5">
                <p class="text-xs uppercase tracking-wide pk-muted mb-2">Tag</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($tagTampil as $t)
                        <span class="pk-chip cursor-default select-none">{{ $t->teks }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @unless($preview)
            <div class="flex flex-wrap gap-2 mt-6">
                @if($bisaWa)
                    <a href="{{ $user->wa_link }}" target="_blank" rel="noopener noreferrer"
                       class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700">
                        💬 WhatsApp {{ $user->no_hp_tampilan }}
                    </a>
                @endif
                @if($isSelf)
                    <a href="{{ route('profil.edit') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Edit Profil</a>
                    <a href="{{ route('pengaturan.edit') }}" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800">⚙️ Pengaturan</a>
                @endif
                @if($bisaRiwayat)
                    <a href="{{ route('riwayat.show', $user->id) }}" class="bg-sky-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-sky-700">Riwayat &amp; Prestasi</a>
                @endif
            </div>
        @endunless
    </div>
</div>