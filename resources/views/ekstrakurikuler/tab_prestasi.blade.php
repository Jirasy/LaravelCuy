@if($staff)
    <form action="{{ route('ekskul-prestasi.store', $ekskul->id) }}" method="POST"
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6">
        @csrf
        <h2 class="font-bold text-gray-800">Tambah Prestasi</h2>
        @include('ekstrakurikuler._form_prestasi', ['prestasi' => null, 'anggotaOpsi' => $anggotaOpsi])
        <div class="text-right">
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Simpan Prestasi</button>
        </div>
    </form>
@endif

<div class="space-y-4">
    @forelse($prestasis as $p)
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">🏆 {{ $p->peringkat }}</span>
                        <span class="px-2.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800">{{ $p->tingkat_label }}</span>
                        <span class="text-xs text-gray-500">{{ $p->tahun }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $p->judul }}</h3>
                    @if($p->penyelenggara)<p class="text-xs text-gray-500">Penyelenggara: {{ $p->penyelenggara }}</p>@endif
                </div>
                @if($staff)
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('ekskul-prestasi.edit', $p->id) }}" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-yellow-600">Edit</a>
                        <form action="{{ route('ekskul-prestasi.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus prestasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                        </form>
                    </div>
                @endif
            </div>
            @if($p->deskripsi)<p class="text-gray-800 mt-3 whitespace-pre-line">{{ $p->deskripsi }}</p>@endif
            @if($p->anggota->isNotEmpty())
                <p class="text-sm text-gray-600 mt-3"><span class="font-semibold">Diraih oleh:</span>
                    {{ $p->anggota->pluck('nama_pendaftar')->implode(', ') }}</p>
            @endif
        </div>
    @empty
        <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada prestasi yang tercatat.</div>
    @endforelse
</div>