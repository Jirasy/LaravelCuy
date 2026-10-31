@php
    $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $akanDatang = $events->filter(fn ($e) => $e->akan_datang)->values();
    $selesai    = $events->reject(fn ($e) => $e->akan_datang)->sortByDesc('tanggal_mulai')->values();
@endphp

@if($staff)
    <form action="{{ route('ekskul-event.store', $ekskul->id) }}" method="POST"
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6">
        @csrf
        <h2 class="font-bold text-gray-800">Tambah Event / Acara</h2>
        @include('ekstrakurikuler._form_event', ['event' => null])
        <div class="text-right">
            <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-emerald-700">Simpan Event</button>
        </div>
    </form>
@endif

@foreach([['Akan datang', $akanDatang], ['Sudah selesai', $selesai]] as [$judulBagian, $daftar])
    @if($daftar->isNotEmpty())
        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2 {{ $loop->first ? '' : 'mt-6' }}">{{ $judulBagian }}</h2>
        <div class="space-y-3">
            @foreach($daftar as $e)
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex gap-4 {{ $e->akan_datang ? '' : 'opacity-70' }}" data-aos="fade-up">
                    <div class="shrink-0 w-16 text-center rounded-xl py-2 text-white" style="background:linear-gradient(135deg, rgb(var(--p1-rgb)), rgb(var(--p2-rgb)))">
                        <p class="text-2xl font-bold leading-none">{{ $e->tanggal_mulai->format('d') }}</p>
                        <p class="text-xs uppercase mt-1">{{ $bln[$e->tanggal_mulai->month - 1] }}</p>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <span class="px-2.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800">{{ $e->jenis_label }}</span>
                                <h3 class="text-lg font-bold text-gray-900 mt-1">{{ $e->judul }}</h3>
                            </div>
                            @if($staff)
                                <div class="flex gap-2 shrink-0">
                                    <a href="{{ route('ekskul-event.edit', $e->id) }}" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-indigo-700">Edit</a>
                                    <form action="{{ route('ekskul-event.destroy', $e->id) }}" method="POST" onsubmit="return confirm('Hapus event ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $e->tanggal_mulai->format('d') }} {{ $bln[$e->tanggal_mulai->month - 1] }} {{ $e->tanggal_mulai->format('Y') }}
                            @if($e->tanggal_selesai && !$e->tanggal_selesai->isSameDay($e->tanggal_mulai))
                                – {{ $e->tanggal_selesai->format('d') }} {{ $bln[$e->tanggal_selesai->month - 1] }} {{ $e->tanggal_selesai->format('Y') }}
                            @endif
                            @if($e->lokasi) &middot; 📍 {{ $e->lokasi }} @endif
                        </p>
                        @if($e->deskripsi)<p class="text-gray-800 text-sm mt-2 whitespace-pre-line">{{ $e->deskripsi }}</p>@endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endforeach

@if($events->isEmpty())
    <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada event atau acara.</div>
@endif