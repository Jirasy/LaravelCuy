@php
    $badgeStatus = ['draft' => 'pending', 'diajukan' => 'pending', 'diterima' => 'diterima', 'ditolak' => 'ditolak', 'mengundurkan_diri' => 'ditolak'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran {{ $ekskul->label_calon }} {{ $ekskul->nama_ekstrakurikuler }}</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-3xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Pendaftaran {{ $ekskul->label_calon }} {{ $ekskul->nama_ekstrakurikuler }}</h1>
            <p class="text-gray-600 text-sm">Pendaftar diseleksi bertahap oleh pembina, admin, dan senior {{ $ekskul->nama_ekstrakurikuler }}.</p>
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

        @if($sudahPengurus)
            <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-700 mb-6" data-aos="fade-up">
                🏛️ Kamu sudah tercatat sebagai anggota {{ $ekskul->nama_ekstrakurikuler }}.
            </div>
        @elseif(!$periode)
            <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-600 mb-6" data-aos="fade-up">
                Pendaftaran {{ $ekskul->label_calon }} sedang ditutup. Pantau notifikasi untuk pengumuman periode berikutnya.
            </div>
        @else
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6" data-aos="fade-up">
                <div class="flex flex-wrap justify-between items-start gap-2 mb-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Periode</p>
                        <p class="text-xl font-bold text-gray-900">{{ $periode->nama }}</p>
                        @if($periode->tutup_pada)<p class="text-xs text-gray-500">Pendaftaran ditutup {{ $periode->tutup_pada->format('d-m-Y') }}</p>@endif
                    </div>
                    @if($casis)
                        @include('partials.status-badge', ['status' => $badgeStatus[$casis->status], 'label' => \App\Models\CasisPendaftaran::STATUS[$casis->status]])
                    @endif
                </div>

                @if($casis && $casis->didaftarkan_oleh && $casis->status === 'draft')
                    <div class="bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-lg px-4 py-3 text-sm mb-4">
                        Kamu didaftarkan ke jalur seleksi ini oleh admin atau pembina. Lengkapi formulir di bawah ini lalu klik <strong>Ajukan pendaftaran</strong>.
                    </div>
                @endif

                @if(!$casis || $casis->bisa_diubah)
                    <form action="{{ route('casis.simpan', $ekskul->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Motivasi <span class="font-normal text-gray-400">(wajib, minimal 50 karakter)</span></label>
                            <textarea name="motivasi" rows="5" maxlength="3000" class="w-full border p-2 rounded-lg mt-1">{{ old('motivasi', $casis->motivasi ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Pengalaman organisasi <span class="font-normal text-gray-400">(opsional)</span></label>
                            <textarea name="pengalaman" rows="4" maxlength="3000" class="w-full border p-2 rounded-lg mt-1">{{ old('pengalaman', $casis->pengalaman ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Visi &amp; misi <span class="font-normal text-gray-400">(opsional)</span></label>
                            <textarea name="visi_misi" rows="4" maxlength="3000" class="w-full border p-2 rounded-lg mt-1">{{ old('visi_misi', $casis->visi_misi ?? '') }}</textarea>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" name="aksi" value="simpan" class="bg-emerald-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-emerald-700">Simpan draft</button>
                            <button type="submit" name="aksi" value="ajukan" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700"
                                    onclick="return confirm('Ajukan pendaftaran? Setelah masa pendaftaran ditutup, isinya tidak bisa diubah lagi.')">
                                {{ $casis && $casis->status === 'diajukan' ? 'Perbarui pengajuan' : 'Ajukan pendaftaran' }}
                            </button>
                        </div>
                    </form>

                    @if($casis)
                        <form action="{{ route('casis.mundur', $ekskul->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Batalkan pendaftaranmu?')">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:underline">Batalkan pendaftaran</button>
                        </form>
                    @endif
                @else
                    <div class="space-y-3 text-sm text-gray-800">
                        <div><p class="text-xs font-semibold text-gray-500 uppercase">Motivasi</p><p class="whitespace-pre-line">{{ $casis->motivasi }}</p></div>
                        @if($casis->pengalaman)<div><p class="text-xs font-semibold text-gray-500 uppercase">Pengalaman</p><p class="whitespace-pre-line">{{ $casis->pengalaman }}</p></div>@endif
                        @if($casis->visi_misi)<div><p class="text-xs font-semibold text-gray-500 uppercase">Visi &amp; misi</p><p class="whitespace-pre-line">{{ $casis->visi_misi }}</p></div>@endif
                        <p class="text-xs text-gray-500">Pendaftaranmu sudah dikunci dan sedang diproses.</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Progres seleksi: tahap, jadwal, tempat, dan hasilmu di tiap tahap --}}
        @if($progres && $tahaps->isNotEmpty())
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6" data-aos="fade-up">
                <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                    <h2 class="font-bold text-gray-800">Tahap seleksi periode {{ $progres->periode->nama }}</h2>
                    @include('partials.status-badge', ['status' => $badgeStatus[$progres->status], 'label' => \App\Models\CasisPendaftaran::STATUS[$progres->status]])
                </div>
                @php $tahapSekarang = $progres->tahapSekarang(); @endphp
                <ol class="space-y-2">
                    @foreach($tahaps as $t)
                        @php
                            $h = $hasils->get($t->id);
                            $sedang = $tahapSekarang && $tahapSekarang->id === $t->id;
                        @endphp
                        <li class="border rounded-lg p-3 {{ $sedang ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200' }}">
                            <div class="flex flex-wrap justify-between items-center gap-2">
                                <p class="font-semibold text-gray-900">{{ $t->urutan }}. {{ $t->nama }}</p>
                                @if($h)
                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $h->hasil === 'lolos' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ \App\Models\SeleksiHasil::HASIL[$h->hasil] }}</span>
                                @elseif($sedang)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800">Sedang berjalan</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500">Menunggu</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ $t->waktu_teks ?? 'Jadwal belum ditentukan' }}@if($t->tempat) &middot; {{ $t->tempat }}@endif
                            </p>
                            @if($t->keterangan)<p class="text-xs text-gray-600 mt-1 whitespace-pre-line">{{ $t->keterangan }}</p>@endif
                            @if($h && $h->catatan)<p class="text-xs text-gray-700 mt-1 whitespace-pre-line">Catatan: {{ $h->catatan }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        @if($riwayat->isNotEmpty())
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Riwayat pendaftaran</h2>
            <div class="space-y-2">
                @foreach($riwayat as $r)
                    <div class="bg-white px-5 py-4 rounded-xl border border-gray-200">
                        <div class="flex justify-between items-center gap-3">
                            <p class="font-semibold text-gray-900">Periode {{ $r->periode->nama ?? '-' }}</p>
                            @include('partials.status-badge', ['status' => $badgeStatus[$r->status], 'label' => \App\Models\CasisPendaftaran::STATUS[$r->status]])
                        </div>
                        @if($r->catatan_keputusan)
                            <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">Catatan: {{ $r->catatan_keputusan }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>