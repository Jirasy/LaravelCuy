{{-- Kartu Rekrutmen OSIS di atas katalog. Selalu terlihat supaya mudah ditemukan. --}}
@php
    $layanan = app(\App\Services\OsisService::class);
    $osis    = $layanan->osis();
    $saya    = auth()->user();
@endphp

@if(!$osis)
    {{-- Ekskul OSIS belum disiapkan: hanya admin yang perlu tahu --}}
    @if($saya->isAdmin())
        <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-5" data-aos="fade-up">
            <p class="font-bold text-amber-900">🗳️ Ekskul OSIS belum disiapkan</p>
            <p class="text-sm text-amber-800 mt-1">
                Pendaftaran CaSis baru muncul setelah ada ekskul yang berjenis <strong>OSIS</strong>.
                Buat ekskul baru dengan jenis OSIS, atau tandai ekskul yang sudah ada lewat <strong>Edit Ekskul → Jenis ekskul</strong>.
            </p>
            <a href="{{ route('ekstrakurikuler.create') }}" class="inline-block mt-3 bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-700">+ Buat ekskul OSIS</a>
        </div>
    @endif
@else
    @php
        $periodeBuka    = $layanan->periodeBuka();
        $bolehKelola    = $layanan->bolehMemutuskan($saya);
        $periodeTerbaru = \App\Models\OsisPeriode::where('ekstrakurikuler_id', $osis->id)->latest()->first();
        $jadiSenior     = \App\Models\OsisPeriode::where('ekstrakurikuler_id', $osis->id)
            ->whereHas('seniors', fn ($q) => $q->where('users.id', $saya->id))
            ->exists();
        $casisSaya      = $saya->role === 'siswa'
            ? \App\Models\CasisPendaftaran::where('user_id', $saya->id)->latest()->first()
            : null;
        $menunggu       = $periodeTerbaru
            ? \App\Models\CasisPendaftaran::where('osis_periode_id', $periodeTerbaru->id)->where('status', 'diajukan')->count()
            : 0;
    @endphp

    <div class="hero-glass rounded-2xl p-6 mb-6" data-aos="fade-up">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-xl font-bold">🗳️ Rekrutmen OSIS</h2>

                @if($saya->role === 'siswa')
                    @if($periodeBuka)
                        <p class="text-sm mt-1 opacity-90">
                            Pendaftaran CaSis periode <strong>{{ $periodeBuka->nama }}</strong> sedang dibuka
                            @if($periodeBuka->tutup_pada) sampai {{ $periodeBuka->tutup_pada->format('d-m-Y') }} @endif.
                        </p>
                        @if($casisSaya && $casisSaya->osis_periode_id === $periodeBuka->id)
                            <p class="text-sm mt-1">Statusmu: <strong>{{ \App\Models\CasisPendaftaran::STATUS[$casisSaya->status] }}</strong></p>
                        @endif
                    @elseif($casisSaya && $casisSaya->status === 'diajukan')
                        <p class="text-sm mt-1 opacity-90">Pendaftaran CaSis-mu sedang diseleksi. Pantau notifikasi untuk hasilnya.</p>
                    @else
                        <p class="text-sm mt-1 opacity-80">Pendaftaran CaSis belum dibuka. Kami akan memberi tahu lewat notifikasi saat dibuka.</p>
                    @endif
                @endif

                @if($saya->role !== 'siswa')
                    <p class="text-sm mt-1 opacity-90">
                        @if($periodeTerbaru)
                            Periode <strong>{{ $periodeTerbaru->nama }}</strong>: {{ \App\Models\OsisPeriode::STATUS[$periodeTerbaru->status] }}
                            &middot; {{ $menunggu }} pendaftar menunggu keputusan
                        @else
                            Belum ada periode rekrutmen.
                        @endif
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @if($saya->role === 'siswa' && ($periodeBuka || $casisSaya))
                    <a href="{{ route('casis.create') }}" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">
                        {{ $periodeBuka && !($casisSaya && $casisSaya->osis_periode_id === $periodeBuka->id) ? 'Daftar jadi CaSis' : 'Lihat pendaftaranku' }}
                    </a>
                @endif

                @if($bolehKelola || $jadiSenior)
                    <a href="{{ route('casis.index') }}" class="bg-gray-800 text-white px-5 py-2 rounded-lg font-semibold hover:bg-gray-900">
                        {{ $bolehKelola && !$periodeTerbaru ? 'Buat periode rekrutmen' : 'Kelola seleksi CaSis' }}
                    </a>
                @endif

                <a href="{{ route('ekstrakurikuler.show', $osis->id) }}" class="bg-white/70 text-gray-800 px-5 py-2 rounded-lg font-semibold hover:bg-white">Tentang OSIS</a>
            </div>
        </div>
    </div>
@endif