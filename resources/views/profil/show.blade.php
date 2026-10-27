<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil {{ $user->name }}</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="mb-6" data-aos="fade-down">
            @include('partials.kartu-profil', [
                'user' => $user, 'badges' => $badges, 'bisaWa' => $bisaWa,
                'isSelf' => $isSelf, 'bisaRiwayat' => $bisaRiwayat, 'tags' => $tags,
            ])
        </div>

        @if($isSelf || $bisaModerasi || $bisaBeriTag)
            @include('profil._tag', [
                'user' => $user, 'tags' => $tags, 'isSelf' => $isSelf,
                'bisaModerasi' => $bisaModerasi, 'bisaBeriTag' => $bisaBeriTag,
            ])
        @endif

        @if($lihatDataAkun)
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6 text-sm" data-aos="fade-up">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Data akun <span class="normal-case font-normal text-gray-400">(hanya terlihat olehmu dan admin/pembina)</span></p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div><p class="text-xs text-gray-500">Email</p><p class="text-gray-800 break-all">{{ $user->email }}</p></div>
                    <div><p class="text-xs text-gray-500">NISN</p><p class="text-gray-800">{{ $user->nisn ?? '-' }}</p></div>
                    <div>
                        <p class="text-xs text-gray-500">Nomor HP</p>
                        <p class="text-gray-800">{{ $user->no_hp_tampilan ?? '-' }}</p>
                        @if($user->no_hp)<p class="text-xs text-gray-400">Terlihat oleh: {{ \App\Models\User::NO_HP_TAMPIL[$user->no_hp_tampil] ?? '-' }}</p>@endif
                    </div>
                </div>
            </div>
        @endif

        @if($bentrok->isNotEmpty())
            @include('profil._bentrok', ['bentrok' => $bentrok, 'user' => $user])
        @endif

        {{-- Jadwal kegiatan ekskul per hari --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
            <h2 class="text-lg font-bold text-gray-800 mb-3">Jadwal Kegiatan Ekskul</h2>

            <div class="flex flex-wrap gap-2 mb-4">
                @foreach(\App\Enums\Hari::cases() as $h)
                    @php $jumlah = $perHari[$h->value]->count(); $aktif = $hariDipilih === $h->value; @endphp
                    <a href="{{ route('profil.show', ['user' => $user->id, 'hari' => $h->value]) }}"
                       class="px-3 py-1.5 rounded-full text-sm font-semibold border transition
                              {{ $aktif ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                        {{ $h->label() }}
                        @if($jumlah > 0)
                            <span class="ml-1 text-xs {{ $aktif ? 'opacity-90' : 'text-indigo-600' }}">({{ $jumlah }})</span>
                        @endif
                        @if($hariIni === $h->value)
                            <span class="ml-1 text-xs {{ $aktif ? 'opacity-90' : 'text-emerald-600' }}">• hari ini</span>
                        @endif
                    </a>
                @endforeach
            </div>

            @php $daftar = $perHari[$hariDipilih]; @endphp
            @if($daftar->isEmpty())
                <p class="text-sm text-gray-500">Tidak ada kegiatan ekskul pada hari {{ \App\Enums\Hari::from($hariDipilih)->label() }}.</p>
            @else
                <div class="space-y-2">
                    @foreach($daftar as $j)
                        @php $bentrokBaris = in_array($j['ekskul_id'], $idBentrokPerHari[$hariDipilih] ?? [], true); @endphp
                        <div class="flex items-center justify-between gap-3 border rounded-lg px-4 py-3 {{ $bentrokBaris ? 'border-amber-400' : 'border-gray-200' }}">
                            <div>
                                <a href="{{ route('ekstrakurikuler.show', $j['ekskul_id']) }}" class="font-semibold text-gray-800 hover:underline">{{ $j['ekskul'] }}</a>
                                <p class="text-xs text-gray-500">
                                    {{ $j['peran'] }}
                                    @if($bentrokBaris)
                                        <a href="#bentrok-jadwal" class="ml-1 font-semibold text-amber-600 hover:underline">⚠️ Bentrok</a>
                                    @endif
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-indigo-600 whitespace-nowrap">{{ $j['mulai'] }} – {{ $j['selesai'] }} WIB</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</body>
</html>