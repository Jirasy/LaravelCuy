{{-- Variabel: $ekskul, $staff, $bisaUnggah, $pendaftaranSaya, $fotos (paginator), $fotoMenunggu, $fotoSaya, $eventOpsi, $kategoriDipilih --}}

{{-- ===== Pengunjung (bukan anggota): ajakan bergabung ===== --}}
@unless($bisaUnggah)
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6 flex flex-wrap items-center justify-between gap-3 text-sm">
        <p class="text-gray-700">📸 Dokumentasi kegiatan {{ $ekskul->nama_ekstrakurikuler }}. Foto baru bisa diunggah oleh anggota ekskul ini.</p>
        @if(!$pendaftaranSaya && $ekskul->pendaftaran_terbuka && !auth()->user()->isStaff())
            <a href="{{ $ekskul->memakaiCasis() ? route('casis.create', $ekskul->id) : route('pendaftaran.create', ['ekstrakurikuler_id' => $ekskul->id]) }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 whitespace-nowrap">Tertarik? Daftar Sekarang</a>
        @endif
    </div>
@endunless

{{-- ===== Unggah (anggota aktif dan pengelola) ===== --}}
@if($bisaUnggah)
<form action="{{ route('galeri.store', $ekskul->id) }}" method="POST" enctype="multipart/form-data"
      class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6">
    @csrf
    <h2 class="font-bold text-gray-800">📷 Unggah foto kegiatan</h2>

    <div>
        <input type="file" name="foto[]" multiple required accept="image/jpeg,image/png,image/webp" class="w-full text-xs">
        <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau WEBP. Maksimal 10 foto sekali unggah, tiap foto maksimal 8 MB.
            @unless($staff) Fotomu tampil di galeri setelah disetujui pembina atau admin. @endunless</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs text-gray-500">Kategori</label>
            <select name="kategori" required class="w-full border p-2 rounded-lg text-sm">
                @foreach(\App\Models\EkskulFoto::KATEGORI as $kode => $label)
                    <option value="{{ $kode }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">Tanggal foto</label>
            <input type="date" name="diambil_pada" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="w-full border p-2 rounded-lg text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs text-gray-500">Event terkait <span class="text-gray-400">(opsional)</span></label>
            <select name="event_id" class="w-full border p-2 rounded-lg text-sm">
                <option value="">-- Tidak ada --</option>
                @foreach($eventOpsi as $ev)
                    <option value="{{ $ev->id }}">{{ $ev->judul }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <input type="text" name="caption" maxlength="200" placeholder="Keterangan foto (opsional, berlaku untuk semua foto yang diunggah)" class="w-full border p-2 rounded-lg text-sm">

    <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-emerald-700">Unggah</button>
</form>
@endif

{{-- ===== Menunggu persetujuan (pengelola) ===== --}}
@if($staff && $fotoMenunggu->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
            <h2 class="font-bold text-amber-900">Menunggu persetujuan ({{ $fotoMenunggu->count() }})</h2>
            <form action="{{ route('galeri.setujui-semua', $ekskul->id) }}" method="POST" onsubmit="return confirm('Setujui semua foto yang menunggu?')">
                @csrf
                <button type="submit" class="bg-emerald-600 text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-emerald-700">Setujui semua</button>
            </form>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($fotoMenunggu as $f)
                <div class="bg-white rounded-lg overflow-hidden border border-amber-200">
                    <img src="{{ route('galeri.berkas', ['foto' => $f->id, 'thumb' => 1]) }}" alt="Foto menunggu persetujuan" loading="lazy" class="w-full h-32 object-cover">
                    <div class="p-2 text-xs">
                        <p class="text-gray-600 truncate">{{ $f->pengunggah->name ?? 'Pengguna dihapus' }}</p>
                        <div class="flex gap-1 mt-1">
                            <form action="{{ route('galeri.setujui', $f->id) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-emerald-600 text-white py-1 rounded font-semibold hover:bg-emerald-700">Setujui</button>
                            </form>
                            <form action="{{ route('galeri.destroy', $f->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Tolak dan hapus foto ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 text-white py-1 rounded font-semibold hover:bg-red-700">Tolak</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- ===== Foto siswa yang masih menunggu ===== --}}
@if(!$staff && $fotoSaya->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <p class="text-sm font-semibold text-amber-900 mb-2">Fotomu yang menunggu persetujuan ({{ $fotoSaya->count() }})</p>
        <div class="flex flex-wrap gap-2">
            @foreach($fotoSaya as $f)
                <div class="relative">
                    <img src="{{ route('galeri.berkas', ['foto' => $f->id, 'thumb' => 1]) }}" alt="Foto menunggu persetujuan" loading="lazy" class="w-20 h-20 object-cover rounded-lg border border-amber-200">
                    <form action="{{ route('galeri.destroy', $f->id) }}" method="POST" class="absolute -top-1 -right-1" onsubmit="return confirm('Hapus foto ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-5 h-5 text-[10px] leading-none rounded-full bg-red-600 text-white" title="Hapus">✕</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- ===== Filter kategori ===== --}}
<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'galeri']) }}"
       class="px-3 py-1.5 rounded-full text-sm font-semibold border {{ !$kategoriDipilih ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200' }}">Semua</a>
    @foreach(\App\Models\EkskulFoto::KATEGORI as $kode => $label)
        <a href="{{ route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'galeri', 'kategori' => $kode]) }}"
           class="px-3 py-1.5 rounded-full text-sm font-semibold border {{ $kategoriDipilih === $kode ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- ===== Galeri ===== --}}
@if($fotos->isEmpty())
    <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada foto di galeri{{ $kategoriDipilih ? ' untuk kategori ini' : '' }}.</div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        @foreach($fotos as $f)
            <div class="relative group" data-aos="fade-up">
                <button type="button" data-lb data-full="{{ route('galeri.berkas', $f->id) }}"
                        data-caption="{{ $f->caption }}"
                        data-meta="{{ \App\Models\EkskulFoto::KATEGORI[$f->kategori] }} · {{ optional($f->diambil_pada)->format('d-m-Y') }}{{ $f->pengunggah ? ' · oleh ' . $f->pengunggah->name : '' }}{{ $f->event ? ' · ' . $f->event->judul : '' }}"
                        class="block w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                    <img src="{{ route('galeri.berkas', ['foto' => $f->id, 'thumb' => 1]) }}" alt="{{ $f->caption ?? 'Foto kegiatan' }}" loading="lazy"
                         class="w-full h-40 object-cover transition duration-300 group-hover:scale-105">
                </button>
                <span class="absolute left-2 bottom-2 px-2 py-0.5 text-[10px] font-semibold rounded-full bg-black/60 text-white pointer-events-none">{{ \App\Models\EkskulFoto::KATEGORI[$f->kategori] }}</span>

                @if($staff || $f->diunggah_oleh === auth()->id())
                    <form action="{{ route('galeri.destroy', $f->id) }}" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition" onsubmit="return confirm('Hapus foto ini dari galeri?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-6 h-6 text-xs leading-none rounded-full bg-red-600 text-white" title="Hapus foto">✕</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $fotos->links() }}</div>
@endif

{{-- ===== Lightbox ===== --}}
<div id="lightbox" class="hidden fixed inset-0 z-[60] bg-black/90 flex-col items-center justify-center p-4">
    <button type="button" id="lb-tutup" class="absolute top-4 right-5 text-white text-3xl leading-none" aria-label="Tutup">&times;</button>
    <button type="button" id="lb-sebelum" class="absolute left-3 top-1/2 -translate-y-1/2 text-white text-4xl px-2" aria-label="Sebelumnya">‹</button>
    <button type="button" id="lb-sesudah" class="absolute right-3 top-1/2 -translate-y-1/2 text-white text-4xl px-2" aria-label="Berikutnya">›</button>
    <img id="lb-gambar" src="" alt="" class="max-h-[78vh] max-w-full rounded-lg">
    <p id="lb-caption" class="text-white text-sm mt-3 text-center"></p>
    <p id="lb-meta" class="text-white/70 text-xs mt-1 text-center"></p>
</div>

<script>
    (function () {
        var kotak = document.getElementById('lightbox');
        var gambar = document.getElementById('lb-gambar');
        var item = Array.prototype.slice.call(document.querySelectorAll('[data-lb]'));
        var sekarang = 0;

        function tampil(i) {
            if (!item.length) return;
            sekarang = (i + item.length) % item.length;
            var el = item[sekarang];
            gambar.src = el.dataset.full;
            document.getElementById('lb-caption').textContent = el.dataset.caption || '';
            document.getElementById('lb-meta').textContent = el.dataset.meta || '';
        }
        function buka(i) { tampil(i); kotak.classList.remove('hidden'); kotak.classList.add('flex'); }
        function tutup() { kotak.classList.add('hidden'); kotak.classList.remove('flex'); gambar.src = ''; }

        item.forEach(function (el, i) { el.addEventListener('click', function () { buka(i); }); });
        document.getElementById('lb-tutup').addEventListener('click', tutup);
        document.getElementById('lb-sebelum').addEventListener('click', function () { tampil(sekarang - 1); });
        document.getElementById('lb-sesudah').addEventListener('click', function () { tampil(sekarang + 1); });
        kotak.addEventListener('click', function (e) { if (e.target === kotak) tutup(); });
        document.addEventListener('keydown', function (e) {
            if (kotak.classList.contains('hidden')) return;
            if (e.key === 'Escape') tutup();
            if (e.key === 'ArrowLeft') tampil(sekarang - 1);
            if (e.key === 'ArrowRight') tampil(sekarang + 1);
        });
    })();
</script>