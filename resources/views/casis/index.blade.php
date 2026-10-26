@php
    $badgeStatus = ['draft' => 'pending', 'diajukan' => 'pending', 'diterima' => 'diterima', 'ditolak' => 'ditolak', 'mengundurkan_diri' => 'ditolak'];
    $tahapBerikut = ['rekrutmen' => ['seleksi', 'Tutup pendaftaran & mulai seleksi'], 'seleksi' => ['berjalan', 'Tandai kepengurusan berjalan'], 'berjalan' => ['selesai', 'Akhiri periode']];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleksi CaSis</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-5xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Seleksi CaSis OSIS</h1>
            <p class="text-gray-600 text-sm">Penilaian oleh pembina OSIS, admin, dan senior. Keputusan akhir oleh admin atau pembina OSIS.</p>
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

        @if(!$ekskulOsis)
            <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-600">
                Belum ada ekskul yang ditandai sebagai OSIS. Admin bisa menandainya lewat <strong>Edit Ekskul</strong> (pilihan "Jenis").
            </div>
        @else
            {{-- Pilih periode --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($periodes as $p)
                    <a href="{{ route('casis.index', ['periode' => $p->id]) }}"
                       class="px-3 py-1.5 rounded-full text-sm font-semibold border {{ $periode && $periode->id === $p->id ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200' }}">
                        {{ $p->nama }} <span class="text-xs opacity-75">({{ $p->casis_count }})</span>
                    </a>
                @endforeach
            </div>

            {{-- Buat periode (pengelola) --}}
            @if($bolehKelola)
                <details class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6" {{ $periodes->isEmpty() ? 'open' : '' }}>
                    <summary class="cursor-pointer font-bold text-gray-800">+ Buat periode baru</summary>
                    <form action="{{ route('periode.store') }}" method="POST" class="mt-3 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500">Nama periode</label>
                            <input type="text" name="nama" maxlength="20" required placeholder="2026/2027" class="w-full border p-2 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Buka pada <span class="text-gray-400">(opsional)</span></label>
                            <input type="date" name="buka_pada" class="w-full border p-2 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Tutup pada <span class="text-gray-400">(opsional)</span></label>
                            <input type="date" name="tutup_pada" class="w-full border p-2 rounded-lg text-sm">
                        </div>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Buat &amp; buka pendaftaran</button>
                    </form>
                </details>
            @endif

            @if(!$periode)
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada periode rekrutmen.</div>
            @else
                {{-- Status periode + tahap berikutnya --}}
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6" data-aos="fade-up">
                    <div class="flex flex-wrap justify-between items-center gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Periode {{ $periode->nama }}</p>
                            <p class="text-lg font-bold text-gray-900">{{ \App\Models\OsisPeriode::STATUS[$periode->status] }}</p>
                            @if($tipeSaya)<p class="text-xs text-gray-500">Kamu menilai sebagai: {{ \App\Models\CasisReview::TIPE[$tipeSaya] }}</p>@endif
                        </div>
                        @if($bolehKelola && isset($tahapBerikut[$periode->status]))
                            <form action="{{ route('periode.status', $periode->id) }}" method="POST" onsubmit="return confirm('Lanjut ke tahap berikutnya? Tahap tidak bisa dimundurkan.')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="aksi" value="{{ $tahapBerikut[$periode->status][0] }}">
                                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900">{{ $tahapBerikut[$periode->status][1] }}</button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Senior penilai (pengelola) --}}
                @if($bolehKelola)
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6" data-aos="fade-up">
                        <h2 class="font-bold text-gray-800 mb-1">Senior penilai</h2>
                        <p class="text-xs text-gray-500 mb-3">Pengurus OSIS angkatan sebelumnya yang ikut menilai. Pembina OSIS dan admin otomatis menjadi penilai.</p>

                        <div class="flex flex-wrap gap-2 mb-3">
                            @forelse($periode->seniors as $s)
                                <span class="inline-flex items-center gap-1 px-3 py-1 text-sm rounded-full bg-indigo-100 text-indigo-800">
                                    {{ $s->name }}
                                    <form action="{{ route('periode.senior.destroy', [$periode->id, $s->id]) }}" method="POST" class="inline" onsubmit="return confirm('Cabut {{ e($s->name) }} dari penilai?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600" title="Cabut">✕</button>
                                    </form>
                                </span>
                            @empty
                                <span class="text-sm text-gray-400">Belum ada senior penilai.</span>
                            @endforelse
                        </div>

                        @if($periode->status !== 'selesai')
                            <form action="{{ route('periode.senior.store', $periode->id) }}" method="POST" class="flex gap-2">
                                @csrf
                                <select name="user_id" required class="flex-1 border p-2 rounded-lg text-sm">
                                    <option value="">-- Pilih pengurus OSIS aktif --</option>
                                    @foreach($kandidatSenior as $k)
                                        <option value="{{ $k->user_id }}">{{ $k->nama_pendaftar }} ({{ $k->kelas ?? '-' }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Tunjuk</button>
                            </form>
                        @endif
                    </div>
                @endif

                {{-- Daftar pendaftar --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto" data-aos="fade-up">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="p-3">Pendaftar</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Sudah menilai</th>
                                <th class="p-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendaftar as $c)
                                @if(!$bolehKelola && $c->status === 'draft') @continue @endif
                                @php $sudahTipe = $c->reviews->pluck('tipe')->all(); @endphp
                                <tr class="border-t align-top">
                                    <td class="p-3 font-semibold">{{ $c->user->name ?? 'Pengguna dihapus' }}
                                        <span class="block text-xs font-normal text-gray-500">{{ $c->user->kelas ?? '-' }}</span></td>
                                    <td class="p-3">@include('partials.status-badge', ['status' => $badgeStatus[$c->status], 'label' => \App\Models\CasisPendaftaran::STATUS[$c->status]])</td>
                                    <td class="p-3">
                                        @if($c->status === 'draft')
                                            <span class="text-xs text-gray-400">Belum diajukan</span>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                @foreach(\App\Models\CasisReview::TIPE as $kodeTipe => $labelTipe)
                                                    <span class="px-2 py-0.5 text-xs rounded-full {{ in_array($kodeTipe, $sudahTipe, true) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}"
                                                          title="{{ $labelTipe }}">{{ in_array($kodeTipe, $sudahTipe, true) ? '✓' : '•' }} {{ ucfirst($kodeTipe) }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($tipeSaya && $c->status !== 'draft' && $c->user_id !== auth()->id())
                                            <a href="{{ route('casis.show', $c->id) }}" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-indigo-700">
                                                {{ in_array($c->id, $sudahSaya, true) ? 'Lihat / ubah nilaiku' : 'Buka & nilai' }}
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada pendaftar di periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</body>
</html>