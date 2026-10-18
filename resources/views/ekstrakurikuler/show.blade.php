<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ekskul->nama_ekstrakurikuler }}</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        {{-- Header --}}
        <div class="flex justify-between items-start mb-4">
            <div>
                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full {{ $ekskul->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                    {{ ucfirst($ekskul->status) }}
                </span>
                <h1 class="text-3xl font-bold text-gray-800 mt-2">{{ $ekskul->nama_ekstrakurikuler }}</h1>
                <p class="text-gray-600 text-sm">Pembina: <strong>{{ $ekskul->pembina_tampil }}</strong></p>
            </div>
            <a href="{{ route('ekstrakurikuler.index') }}" class="text-gray-600 hover:underline text-sm">← Katalog</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Tab --}}
        @php $tabs = ['info' => 'Informasi', 'struktur' => 'Struktur', 'prestasi' => 'Prestasi', 'event' => 'Event', 'pengumuman' => 'Pengumuman', 'anggota' => 'Anggota']; @endphp
        <div class="flex gap-1 border-b border-gray-200 mb-6 overflow-x-auto">
            @foreach($tabs as $key => $label)
                <a href="{{ route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => $key]) }}"
                   class="px-4 py-2 text-sm font-semibold border-b-2 whitespace-nowrap {{ $tab === $key ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}@if($key === 'anggota') ({{ $ekskul->anggota_count }})@endif
                </a>
            @endforeach
        </div>

        {{-- ========== TAB INFORMASI ========== --}}
        @if($tab === 'info')
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-5" data-aos="fade-up">
                <div>
                    <h2 class="text-sm font-semibold text-gray-500 uppercase">Tentang</h2>
                    <p class="text-gray-800 mt-1">{{ $ekskul->deskripsi ?? 'Belum ada deskripsi.' }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-500 uppercase">Jadwal</h2>
                    <div class="mt-1 text-gray-800">
                        @forelse($ekskul->jadwal as $j)
                            <p>{{ ucfirst($j->hari) }}, {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB</p>
                        @empty
                            <p class="text-gray-400">Belum ada jadwal.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-500 uppercase">Pembina</h2>
                        <p class="text-gray-800 mt-1">{{ $ekskul->pembina_tampil }}</p>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-500 uppercase">Jumlah Anggota</h2>
                        <p class="text-gray-800 mt-1">{{ $ekskul->anggota_count }} orang</p>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    @if($pendaftaranSaya && $pendaftaranSaya->status_anggota === 'aktif')
                        <p class="text-sm">
                            Status pendaftaranmu:
                            @include('partials.status-badge', ['status' => $pendaftaranSaya->status, 'label' => $pendaftaranSaya->status_label])
                        </p>
                    @elseif($pendaftaranSaya && $pendaftaranSaya->status_anggota === 'dikeluarkan')
                        <p class="text-sm text-red-600">
                            Kamu dikeluarkan dari ekskul ini
                            @if($pendaftaranSaya->tanggal_keluar) pada {{ $pendaftaranSaya->tanggal_keluar->format('d-m-Y') }} @endif
                        </p>
                    @elseif($pendaftaranSaya)
                        <p class="text-sm text-gray-600 mb-3">
                            Kamu sudah keluar dari ekskul ini
                            @if($pendaftaranSaya->tanggal_keluar) pada {{ $pendaftaranSaya->tanggal_keluar->format('d-m-Y') }} @endif
                        </p>
                        @if($ekskul->status === 'aktif')
                            <a href="{{ route('pendaftaran.create', ['ekstrakurikuler_id' => $ekskul->id]) }}"
                               class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Daftar Lagi</a>
                        @endif
                    @elseif($ekskul->status === 'aktif')
                        <a href="{{ route('pendaftaran.create', ['ekstrakurikuler_id' => $ekskul->id]) }}"
                           class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Daftar Sekarang</a>
                    @else
                        <p class="text-sm text-gray-500">Ekskul ini sedang nonaktif, pendaftaran ditutup.</p>
                    @endif

                    @include('ekstrakurikuler._keluar')
                </div>
            </div>

        {{-- ========== TAB PRESTASI & EVENT: terbuka untuk semua user ========== --}}
        @elseif($tab === 'prestasi')
            @include('ekstrakurikuler._tab_prestasi')

        @elseif($tab === 'event')
            @include('ekstrakurikuler._tab_event')

        @elseif($tab === 'struktur')
            @include('ekstrakurikuler._tab_struktur')

        {{-- ========== TAB PENGUMUMAN & ANGGOTA: khusus anggota / pengelola ========== --}}
        @elseif(!$bolehLihat)
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 text-center text-gray-600">
                <p class="font-semibold text-gray-800 mb-1">Khusus anggota ekskul ini</p>
                @if(!$pendaftaranSaya)
                    <p class="text-sm mb-4">Daftar dan tunggu persetujuan untuk melihat {{ $tab === 'pengumuman' ? 'pengumuman' : 'daftar anggota' }}.</p>
                    @if($ekskul->status === 'aktif')
                        <a href="{{ route('pendaftaran.create', ['ekstrakurikuler_id' => $ekskul->id]) }}"
                           class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Daftar Sekarang</a>
                    @endif
                @elseif($pendaftaranSaya->status === 'pending')
                    <p class="text-sm">Pendaftaranmu masih menunggu persetujuan.</p>
                @else
                    <p class="text-sm">Pendaftaranmu tidak disetujui.@if($pendaftaranSaya->alasan) Catatan: {{ $pendaftaranSaya->alasan }}@endif</p>
                @endif
            </div>

        {{-- ========== TAB PENGUMUMAN ========== --}}
        @elseif($tab === 'pengumuman')
            @if($staff)
                <form action="{{ route('ekskul-pengumuman.store', $ekskul->id) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6">
                    @csrf
                    <h2 class="font-bold text-gray-800">Buat Pengumuman</h2>
                    <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Judul pengumuman" required class="w-full border p-2 rounded-lg">
                    <textarea name="isi" rows="3" placeholder="Isi pengumuman..." required class="w-full border p-2 rounded-lg">{{ old('isi') }}</textarea>
                    <div class="text-right">
                        <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Posting</button>
                    </div>
                </form>
            @endif

            <div class="space-y-4">
                @forelse($pengumumans as $p)
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $p->judul }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $p->created_at->format('d M Y, H:i') }}
                                    &middot; oleh {{ $p->penulis->name ?? 'Pengelola' }}
                                </p>
                            </div>
                            @if($staff)
                                <div class="flex gap-2 shrink-0">
                                    <a href="{{ route('ekskul-pengumuman.edit', $p->id) }}" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-yellow-600">Edit</a>
                                    <form action="{{ route('ekskul-pengumuman.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <p class="text-gray-800 mt-3 whitespace-pre-line">{{ $p->isi }}</p>
                    </div>
                @empty
                    <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada pengumuman.</div>
                @endforelse
            </div>

        {{-- ========== TAB ANGGOTA ========== --}}
        @elseif($tab === 'anggota')
            @include('ekstrakurikuler._tab_anggota')
        @endif
    </div>
</body>
</html>