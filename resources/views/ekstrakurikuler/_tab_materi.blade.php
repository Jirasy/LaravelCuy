{{-- Variabel: $ekskul, $staff, $bolehMateri, $materis, $prs, $anggotaAkses, $pengumpulanSaya, $pendaftaranSaya --}}
@if(!$bolehMateri)
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 text-center">
        <p class="font-semibold text-gray-800 mb-1">🔒 Materi &amp; PR belum bisa kamu buka</p>
        <p class="text-sm text-gray-600 mb-4">Akses materi diberikan oleh pembina atau admin. Kamu bisa meminta izin.</p>
        <form action="{{ route('materi.minta-akses', $ekskul->id) }}" method="POST">
            @csrf
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Minta akses ke pembina</button>
        </form>
    </div>
@else
    @if($staff)
        {{-- ===== Akses anggota ===== --}}
        <details class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6">
            <summary class="cursor-pointer font-bold text-gray-800">
                🔑 Akses anggota ke Materi &amp; PR
                <span class="text-sm font-normal text-gray-500">({{ $anggotaAkses->where('akses_materi', true)->count() }} dari {{ $anggotaAkses->count() }} anggota diizinkan)</span>
            </summary>

            <div class="flex flex-wrap gap-2 mt-3">
                <form action="{{ route('materi.akses-semua', $ekskul->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="aksi" value="izinkan">
                    <button type="submit" class="bg-green-600 text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-green-700">Izinkan semua anggota aktif</button>
                </form>
                <form action="{{ route('materi.akses-semua', $ekskul->id) }}" method="POST" onsubmit="return confirm('Cabut akses materi dari semua anggota?')">
                    @csrf
                    <input type="hidden" name="aksi" value="cabut">
                    <button type="submit" class="bg-gray-600 text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-gray-700">Cabut semua</button>
                </form>
            </div>

            <div class="mt-3 space-y-1">
                @forelse($anggotaAkses as $a)
                    <div class="flex items-center justify-between gap-3 border border-gray-100 rounded-lg px-3 py-2">
                        <span class="text-sm text-gray-800">{{ $a->nama_pendaftar }}
                            <span class="text-xs text-gray-400">{{ $a->kelas }}</span></span>
                        <form action="{{ route('materi.akses', $a->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            @if($a->akses_materi)
                                <button type="submit" class="text-xs px-3 py-1 rounded-full bg-green-100 text-green-800 font-semibold hover:bg-green-200" title="Klik untuk mencabut">✓ Diizinkan</button>
                            @else
                                <button type="submit" class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200" title="Klik untuk mengizinkan">Izinkan</button>
                            @endif
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Belum ada anggota aktif.</p>
                @endforelse
            </div>
        </details>

        {{-- ===== Tambah materi ===== --}}
        <form action="{{ route('materi.store', $ekskul->id) }}" method="POST" enctype="multipart/form-data"
              class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6" id="form-materi">
            @csrf
            <h2 class="font-bold text-gray-800">Tambah Materi</h2>
            <input type="text" name="judul" required maxlength="200" placeholder="Judul materi" class="w-full border p-2 rounded-lg text-sm">
            <textarea name="deskripsi" rows="2" maxlength="2000" placeholder="Keterangan (opsional)" class="w-full border p-2 rounded-lg text-sm"></textarea>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <select name="jenis" id="jenis-materi" required class="border p-2 rounded-lg text-sm">
                    @foreach(\App\Models\EkskulMateri::JENIS as $kode => $j)
                        <option value="{{ $kode }}">{{ $j[0] }} {{ $j[1] }}</option>
                    @endforeach
                </select>
                <div class="sm:col-span-2">
                    <div id="blok-berkas">
                        <input type="file" name="berkas" id="input-berkas" class="w-full text-xs">
                        <p id="petunjuk-berkas" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    <div id="blok-url" class="hidden">
                        <input type="url" name="url" placeholder="https://youtube.com/watch?v=..." class="w-full border p-2 rounded-lg text-sm">
                        <p class="text-xs text-gray-500 mt-1">Tautan YouTube akan diputar langsung di halaman ini. Tautan lain tampil sebagai tombol.</p>
                    </div>
                </div>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Simpan Materi</button>
        </form>

        {{-- ===== Buat PR ===== --}}
        <form action="{{ route('pr.store', $ekskul->id) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-8">
            @csrf
            <h2 class="font-bold text-gray-800">Buat PR / Tugas</h2>
            <input type="text" name="judul" required maxlength="200" placeholder="Judul PR" class="w-full border p-2 rounded-lg text-sm">
            <textarea name="deskripsi" rows="3" maxlength="5000" placeholder="Petunjuk pengerjaan" class="w-full border p-2 rounded-lg text-sm"></textarea>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500">Tenggat (opsional)</label>
                    <input type="date" name="tenggat" min="{{ date('Y-m-d') }}" class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Nilai maksimal</label>
                    <input type="number" name="nilai_maks" min="10" max="100" value="100" class="w-full border p-2 rounded-lg text-sm">
                </div>
            </div>
            <button type="submit" class="bg-teal-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-teal-700">Buat PR</button>
        </form>
    @endif

    {{-- ===== Daftar PR ===== --}}
    <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">PR &amp; Tugas ({{ $prs->count() }})</h2>
    <div class="space-y-3 mb-8">
        @forelse($prs as $pr)
            @php
                $p = $pengumpulanSaya->get($pr->id);
                $lewat = $pr->tenggat && $pr->tenggat->lt(today());
            @endphp
            <div class="bg-white p-5 rounded-xl shadow-sm border {{ !$staff && !$p && $lewat ? 'border-red-300' : 'border-gray-200' }}" data-aos="fade-up">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-900">📝 {{ $pr->judul }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Nilai maks {{ $pr->nilai_maks }}
                            @if($pr->tenggat) &middot; tenggat {{ $pr->tenggat->format('d-m-Y') }} @endif
                            @if($pr->pembuat) &middot; oleh {{ $pr->pembuat->name }} @endif
                        </p>
                    </div>
                    @if($staff)
                        <div class="flex gap-2 shrink-0">
                            <a href="{{ route('pr.show', $pr->id) }}" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-indigo-700">Periksa &amp; nilai ({{ $pr->pengumpulans_count }})</a>
                            <form action="{{ route('pr.destroy', $pr->id) }}" method="POST" onsubmit="return confirm('Hapus PR ini beserta semua pengumpulannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                            </form>
                        </div>
                    @endif
                </div>

                @if($pr->deskripsi)
                    <p class="text-sm text-gray-800 mt-3 whitespace-pre-line">{{ $pr->deskripsi }}</p>
                @endif

                @unless($staff)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        @if($p && $p->sudah_dinilai)
                            <p class="text-sm"><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Dinilai</span>
                                <strong class="ml-1 text-gray-900">{{ $p->nilai }}</strong> <span class="text-gray-500">dari {{ $pr->nilai_maks }}</span></p>
                            @if($p->komentar)
                                <p class="mt-2 text-sm bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-amber-900 whitespace-pre-line">{{ $p->komentar }}</p>
                            @endif
                        @elseif($p)
                            <p class="text-sm">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $p->terlambat ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">{{ $p->terlambat ? 'Dikumpulkan terlambat' : 'Sudah dikumpulkan' }}</span>
                                <span class="text-xs text-gray-500 ml-1">{{ $p->dikumpulkan_pada->format('d-m-Y H:i') }}, menunggu penilaian</span>
                            </p>
                        @else
                            <p class="text-sm"><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $lewat ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">{{ $lewat ? 'Belum dikumpulkan, lewat tenggat' : 'Belum dikumpulkan' }}</span></p>
                        @endif

                        @if($p && $p->berkas)
                            <p class="text-xs mt-2">Berkasmu: <a href="{{ route('pr.berkas', $p->id) }}" class="text-indigo-600 hover:underline">{{ $p->nama_berkas }}</a></p>
                        @endif

                        @if(!$p || !$p->sudah_dinilai)
                            <details class="mt-3">
                                <summary class="cursor-pointer text-sm text-indigo-600 font-semibold">{{ $p ? 'Perbarui pengumpulan' : 'Kumpulkan PR' }}</summary>
                                <form action="{{ route('pr.kumpul', $pr->id) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-2">
                                    @csrf
                                    <textarea name="jawaban" rows="4" maxlength="5000" placeholder="Tulis jawabanmu (opsional kalau mengunggah berkas)" class="w-full border p-2 rounded-lg text-sm">{{ $p->jawaban ?? '' }}</textarea>
                                    <input type="file" name="berkas" class="w-full text-xs">
                                    <p class="text-xs text-gray-500">PDF, Word, PowerPoint, Excel, TXT, JPG, PNG, atau ZIP. Maksimal 20 MB.
                                        @if($p && $p->berkas) Pilih berkas baru hanya kalau ingin mengganti berkas lama. @endif</p>
                                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-teal-700">{{ $p ? 'Perbarui' : 'Kumpulkan' }}</button>
                                </form>
                            </details>
                        @endif
                    </div>
                @endunless
            </div>
        @empty
            <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada PR.</div>
        @endforelse
    </div>

    {{-- ===== Daftar materi ===== --}}
    <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Materi ({{ $materis->count() }})</h2>
    <div class="space-y-4">
        @forelse($materis as $m)
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-900">{{ \App\Models\EkskulMateri::JENIS[$m->jenis][0] }} {{ $m->judul }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ \App\Models\EkskulMateri::JENIS[$m->jenis][1] }} &middot; {{ $m->created_at->format('d-m-Y') }}
                            @if($m->pengunggah) &middot; {{ $m->pengunggah->name }} @endif
                        </p>
                    </div>
                    @if($staff)
                        <form action="{{ route('materi.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                        </form>
                    @endif
                </div>

                @if($m->deskripsi)
                    <p class="text-sm text-gray-800 mt-2 whitespace-pre-line">{{ $m->deskripsi }}</p>
                @endif

                @if($m->jenis === 'video')
                    <video controls preload="metadata" class="w-full rounded-lg mt-3 bg-black max-h-96">
                        <source src="{{ route('materi.berkas', $m->id) }}" type="{{ $m->mime }}">
                        Browser kamu tidak bisa memutar video ini.
                    </video>
                    <a href="{{ route('materi.berkas', ['materi' => $m->id, 'unduh' => 1]) }}" class="inline-block text-xs text-indigo-600 hover:underline mt-1">Unduh video</a>
                @elseif($m->jenis === 'foto')
                    <img src="{{ route('materi.berkas', $m->id) }}" alt="{{ $m->judul }}" loading="lazy" class="rounded-lg mt-3 max-h-96">
                @elseif($m->jenis === 'dokumen')
                    <div class="flex flex-wrap gap-2 mt-3">
                        @if($m->mime === 'application/pdf')
                            <a href="{{ route('materi.berkas', $m->id) }}" target="_blank" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm font-semibold hover:bg-indigo-700">Buka PDF</a>
                        @endif
                        <a href="{{ route('materi.berkas', ['materi' => $m->id, 'unduh' => 1]) }}" class="bg-gray-700 text-white px-3 py-1.5 rounded text-sm font-semibold hover:bg-gray-800">Unduh {{ $m->nama_berkas }}</a>
                    </div>
                @else
                    @if($m->youtube_id)
                        <iframe src="https://www.youtube-nocookie.com/embed/{{ $m->youtube_id }}" class="w-full aspect-video rounded-lg mt-3"
                                allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    @endif
                    <a href="{{ $m->url }}" target="_blank" rel="noopener noreferrer" class="inline-block text-sm text-indigo-600 hover:underline mt-2 break-all">🔗 {{ $m->url }}</a>
                @endif
            </div>
        @empty
            <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada materi.</div>
        @endforelse
    </div>
@endif

@if($staff && $bolehMateri)
    <script>
        (function () {
            var jenis = document.getElementById('jenis-materi'), berkas = document.getElementById('input-berkas');
            var blokBerkas = document.getElementById('blok-berkas'), blokUrl = document.getElementById('blok-url');
            var petunjuk = document.getElementById('petunjuk-berkas');
            var aturan = {
                video:   { accept: 'video/mp4,video/webm,video/quicktime', teks: 'MP4, WEBM, atau MOV. Maksimal 100 MB.' },
                foto:    { accept: 'image/jpeg,image/png,image/webp', teks: 'JPG, PNG, atau WEBP. Maksimal 5 MB.' },
                dokumen: { accept: '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt', teks: 'PDF, Word, PowerPoint, Excel, atau TXT. Maksimal 20 MB.' }
            };
            function atur() {
                var link = jenis.value === 'link';
                blokBerkas.classList.toggle('hidden', link);
                blokUrl.classList.toggle('hidden', !link);
                if (!link) { berkas.accept = aturan[jenis.value].accept; petunjuk.textContent = aturan[jenis.value].teks; }
            }
            jenis.addEventListener('change', atur);
            atur();
        })();
    </script>
@endif