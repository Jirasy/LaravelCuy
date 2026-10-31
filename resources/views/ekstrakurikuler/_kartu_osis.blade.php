{{-- Kartu rekrutmen di atas katalog: satu kartu untuk tiap ekskul berseleksi (Ekskul Seleksi & Organisasi) yang aktif.
     Selalu terlihat supaya mudah ditemukan. --}}
@php
    $layanan  = app(\App\Services\OsisService::class);
    $saya     = auth()->user();
    $berseleksi = \App\Models\Ekstrakurikuler::alurCasis()->with('jenisEkskul')->where('status', 'aktif')->orderBy('nama_ekstrakurikuler')->get();
@endphp

@if($berseleksi->isEmpty())
    {{-- Belum ada ekskul berseleksi: hanya admin yang perlu tahu --}}
    @if($saya->isAdmin())
        <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-5" data-aos="fade-up">
            <p class="font-bold text-amber-900">🗳️ Belum ada ekskul berseleksi</p>
            <p class="text-sm text-amber-800 mt-1">
                Pendaftaran lewat seleksi muncul setelah ada ekskul bertipe <strong>Ekskul Seleksi</strong> atau <strong>Organisasi</strong> (OSIS, MPK, dst).
                Buat ekskul baru dengan tipe itu, atau ubah tipe ekskul yang sudah ada lewat <strong>Edit Ekskul → Tipe ekskul</strong>.
            </p>
            <a href="{{ route('ekstrakurikuler.create') }}" class="inline-block mt-3 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700">+ Buat ekskul</a>
        </div>
    @endif
@else
    @foreach($berseleksi as $ekskul)
        @php
            $label          = $ekskul->label_calon;
            $periodeBuka    = $layanan->periodeBuka($ekskul);
            $bolehKelola    = $layanan->bolehMemutuskan($saya, $ekskul);
            $periodeTerbaru = \App\Models\OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)->latest()->first();
            $jadiSenior     = \App\Models\OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)
                ->whereHas('seniors', fn ($q) => $q->where('users.id', $saya->id))
                ->exists();
            $casisSaya      = $saya->role === 'siswa'
                ? \App\Models\CasisPendaftaran::where('user_id', $saya->id)
                    ->whereHas('periode', fn ($q) => $q->where('ekstrakurikuler_id', $ekskul->id))
                    ->latest()->first()
                : null;
            $menunggu       = $periodeTerbaru
                ? \App\Models\CasisPendaftaran::where('osis_periode_id', $periodeTerbaru->id)->where('status', 'diajukan')->count()
                : 0;
        @endphp

        <div class="hero-glass rounded-2xl p-6 mb-6" data-aos="fade-up">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-xl font-bold">🗳️ Rekrutmen {{ $ekskul->nama_ekstrakurikuler }}</h2>

                    @if($saya->role === 'siswa')
                        @if($periodeBuka)
                            <p class="text-sm mt-1 opacity-90">
                                Pendaftaran {{ $label }} periode <strong>{{ $periodeBuka->nama }}</strong> sedang dibuka
                                @if($periodeBuka->tutup_pada) sampai {{ $periodeBuka->tutup_pada->format('d-m-Y') }} @endif.
                            </p>
                            @if($casisSaya && $casisSaya->osis_periode_id === $periodeBuka->id)
                                <p class="text-sm mt-1">Statusmu: <strong>{{ \App\Models\CasisPendaftaran::STATUS[$casisSaya->status] }}</strong></p>
                            @endif
                        @elseif($casisSaya && $casisSaya->status === 'diajukan')
                            <p class="text-sm mt-1 opacity-90">Pendaftaranmu sedang diseleksi. Pantau notifikasi untuk hasilnya.</p>
                        @else
                            <p class="text-sm mt-1 opacity-80">Pendaftaran belum dibuka. Kami akan memberi tahu lewat notifikasi saat dibuka.</p>
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
                        <a href="{{ route('casis.create', $ekskul->id) }}" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">
                            {{ $periodeBuka && !($casisSaya && $casisSaya->osis_periode_id === $periodeBuka->id) ? "Daftar jadi {$label}" : 'Lihat pendaftaranku' }}
                        </a>
                    @endif

                    @if($bolehKelola || $jadiSenior)
                        <a href="{{ route('casis.index', $ekskul->id) }}" class="bg-gray-800 text-white px-5 py-2 rounded-lg font-semibold hover:bg-gray-900">
                            {{ $bolehKelola && !$periodeTerbaru ? 'Buat periode rekrutmen' : "Kelola seleksi {$label}" }}
                        </a>
                    @endif

                    <a href="{{ route('ekstrakurikuler.show', $ekskul->id) }}" class="bg-white/70 text-gray-800 px-5 py-2 rounded-lg font-semibold hover:bg-white">Tentang {{ $ekskul->nama_ekstrakurikuler }}</a>
                </div>
            </div>
        </div>
    @endforeach
@endif
