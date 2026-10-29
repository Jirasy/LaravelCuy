{{-- Variabel: $ekskul, $staff. Hanya untuk ekskul bertipe OSIS. --}}
@if($ekskul->memakaiCasis())
    @php
        $layananOsis = app(\App\Services\OsisService::class);
        $periodeBuka = $layananOsis->periodeBuka();
        $saya        = auth()->user();
        $jadiSenior  = \App\Models\OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)
            ->whereHas('seniors', fn ($q) => $q->where('users.id', $saya->id))
            ->exists();
    @endphp
    <div class="mt-4 pt-4 border-t border-gray-200">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">🗳️ Rekrutmen OSIS</p>

        @if($periodeBuka)
            <p class="text-sm text-gray-800 mb-3">
                Pendaftaran CaSis periode <strong>{{ $periodeBuka->nama }}</strong> sedang dibuka
                @if($periodeBuka->tutup_pada) sampai {{ $periodeBuka->tutup_pada->format('d-m-Y') }} @endif.
            </p>
        @else
            <p class="text-sm text-gray-500 mb-3">Belum ada pendaftaran CaSis yang dibuka.</p>
        @endif

        <div class="flex flex-wrap gap-2">
            @if($saya->role === 'siswa')
                <a href="{{ route('casis.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Pendaftaran CaSis saya</a>
            @endif
            @if($staff || $jadiSenior || $saya->isAdmin())
                <a href="{{ route('casis.index') }}" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800">Kelola seleksi CaSis</a>
            @endif
        </div>
    </div>
@endif