@php
    $badgeStatus = ['belum' => 'pending', 'dikerjakan' => 'pending', 'selesai' => 'diterima'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Task</h1>
            <p class="text-gray-600 text-sm">Daftar tugas untukmu dari pembina atau admin, ditambah task pribadimu.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Task terbuka --}}
        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Belum selesai ({{ $terbuka->count() }})</h2>
        <div class="space-y-3 mb-8">
            @forelse($terbuka as $p)
                <div class="bg-white p-5 rounded-xl shadow-sm border {{ $p->terlambat ? 'border-red-300' : 'border-gray-200' }}" data-aos="fade-up">
                    <div class="flex flex-wrap justify-between items-start gap-3">
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900">{{ $p->tugas->judul }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($p->tugas->pembuat && $p->tugas->dibuat_oleh !== auth()->id())
                                    Dari {{ $p->tugas->pembuat->name }}
                                @else
                                    Task pribadi
                                @endif
                                @if($p->tugas->ekstrakurikuler) &middot; {{ $p->tugas->ekstrakurikuler->nama_ekstrakurikuler }} @endif
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            @include('partials.status-badge', ['status' => $badgeStatus[$p->status], 'label' => \App\Models\EkskulTugasPenerima::STATUS[$p->status]])
                            @if($p->tugas->tenggat)
                                <p class="text-xs mt-1 {{ $p->terlambat ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                    {{ $p->terlambat ? 'Lewat tenggat: ' : 'Tenggat: ' }}{{ $p->tugas->tenggat->format('d-m-Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @if($p->tugas->deskripsi)
                        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $p->tugas->deskripsi }}</p>
                    @endif

                    <div class="flex gap-2 mt-3">
                        @if($p->status === 'belum')
                            <form action="{{ route('tugas.status', $p->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="dikerjakan">
                                <button type="submit" class="bg-amber-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-amber-600">Mulai dikerjakan</button>
                            </form>
                        @endif
                        <form action="{{ route('tugas.status', $p->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="selesai">
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-green-700">Tandai selesai</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Tidak ada task yang menunggu. 🎉</div>
            @endforelse
        </div>

        @if($selesai->isNotEmpty())
            <details class="mb-8">
                <summary class="cursor-pointer text-sm font-semibold text-gray-500 uppercase mb-2">Selesai ({{ $selesai->count() }})</summary>
                <div class="space-y-2 mt-2">
                    @foreach($selesai as $p)
                        <div class="bg-white px-4 py-3 rounded-lg border border-gray-200 flex justify-between items-center gap-3 opacity-80">
                            <span class="text-sm text-gray-700 line-through">{{ $p->tugas->judul }}</span>
                            <form action="{{ route('tugas.status', $p->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="belum">
                                <button type="submit" class="text-xs text-indigo-600 hover:underline">Buka lagi</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif

        {{-- Tambah task pribadi --}}
        <div id="buat" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8" data-aos="fade-up">
            <h2 class="font-bold text-gray-800 mb-1">Tambah task pribadi</h2>
            @if(auth()->user()->isStaff())
                <p class="text-xs text-gray-500 mb-3">Untuk memberi task ke anggota, buka halaman ekskul (tab Informasi) atau Kelola User.</p>
            @endif
            @include('tugas._form', ['mode' => 'pribadi'])
        </div>

        {{-- Task yang kuberikan ke orang lain --}}
        @php
            $untukOrangLain = $diberikan->filter(fn ($t) => $t->penerimas->contains(fn ($p) => $p->user_id !== auth()->id()));
        @endphp
        @if($untukOrangLain->isNotEmpty())
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Task yang kuberikan</h2>
            <div class="space-y-3 mb-8">
                @foreach($untukOrangLain as $t)
                    @php $selesaiN = $t->penerimas->where('status', 'selesai')->count(); $total = $t->penerimas->count(); @endphp
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $t->judul }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $selesaiN }} dari {{ $total }} selesai
                                    @if($t->ekstrakurikuler) &middot; {{ $t->ekstrakurikuler->nama_ekstrakurikuler }} @endif
                                    @if($t->tenggat) &middot; tenggat {{ $t->tenggat->format('d-m-Y') }} @endif
                                </p>
                            </div>
                            <form action="{{ route('tugas.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus task ini untuk semua penerima?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                            </form>
                        </div>
                        <div class="flex flex-wrap gap-1 mt-3">
                            @foreach($t->penerimas as $pn)
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $pn->status === 'selesai' ? 'bg-green-100 text-green-800' : ($pn->status === 'dikerjakan' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $pn->user->name ?? 'Pengguna dihapus' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>