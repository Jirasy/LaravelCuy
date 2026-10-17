@if($staff)
    <form action="{{ route('ekskul-jabatan.store', $ekskul->id) }}" method="POST"
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6">
        @csrf
        <h2 class="font-bold text-gray-800">Tambah Jabatan</h2>
        <p class="text-xs text-gray-500">Struktur tiap ekskul bisa berbeda. Buat hanya jabatan yang memang ada di ekskul ini, lalu berikan ke anggota lewat tab <strong>Anggota</strong>.</p>
        @include('ekstrakurikuler._form_jabatan', ['jabatan' => null])
        <div class="text-right">
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Simpan Jabatan</button>
        </div>
    </form>
@endif

<div class="space-y-4">
    @forelse($jabatans as $j)
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $j->label_lengkap }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        @if($j->kuota)
                            {{ $j->pemegangAktif->count() }} dari {{ $j->kuota }} kursi terisi
                        @else
                            {{ $j->pemegangAktif->count() }} pemegang &middot; kuota bebas
                        @endif
                    </p>
                </div>
                @if($staff)
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('ekskul-jabatan.edit', $j->id) }}" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-yellow-600">Edit</a>
                        <form action="{{ route('ekskul-jabatan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jabatan {{ e($j->label_lengkap) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                        </form>
                    </div>
                @endif
            </div>

            @if($j->deskripsi)
                <p class="text-gray-800 text-sm mt-3 whitespace-pre-line">{{ $j->deskripsi }}</p>
            @endif

            @if($j->tugas || $j->tanggung_jawab)
                <details class="mt-3">
                    <summary class="cursor-pointer text-sm text-indigo-600 font-semibold">Tugas &amp; tanggung jawab</summary>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3 text-sm">
                        @if($j->tugas)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Tugas</p>
                                <p class="text-gray-800 whitespace-pre-line">{{ $j->tugas }}</p>
                            </div>
                        @endif
                        @if($j->tanggung_jawab)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Tanggung jawab</p>
                                <p class="text-gray-800 whitespace-pre-line">{{ $j->tanggung_jawab }}</p>
                            </div>
                        @endif
                    </div>
                </details>
            @endif

            <div class="mt-4 pt-3 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Dipegang oleh</p>
                @if($j->pemegangAktif->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada pemegang.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($j->pemegangAktif as $pm)
                            @if($pm->user_id)
                                <a href="{{ route('profil.show', $pm->user_id) }}" class="px-3 py-1 text-sm rounded-full bg-amber-100 text-amber-800 hover:bg-amber-200">{{ $pm->nama_pendaftar }}</a>
                            @else
                                <span class="px-3 py-1 text-sm rounded-full bg-amber-100 text-amber-800">{{ $pm->nama_pendaftar }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">
            Belum ada struktur jabatan di ekskul ini.
            @if($staff) Tambahkan jabatan pertama lewat form di atas. @endif
        </div>
    @endforelse
</div>