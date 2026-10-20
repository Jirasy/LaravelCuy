@if($staff)
    <form action="{{ route('pertemuan.store', $ekskul->id) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-3 mb-6">
        @csrf
        <h2 class="font-bold text-gray-800">Catat Pertemuan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs text-gray-500">Tanggal</label>
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="w-full border p-2 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Jam mulai</label>
                <input type="time" name="jam_mulai" class="w-full border p-2 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Jam selesai</label>
                <input type="time" name="jam_selesai" class="w-full border p-2 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Topik</label>
                <input type="text" name="topik" maxlength="200" placeholder="opsional" class="w-full border p-2 rounded-lg text-sm">
            </div>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Buat &amp; isi absensi</button>
    </form>

    @include('ekstrakurikuler._laporan_tombol', ['jenis' => 'absensi'])

    <div class="space-y-3">
        @forelse($pertemuans as $p)
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-wrap items-center justify-between gap-3" data-aos="fade-up">
                <div>
                    <p class="font-bold text-gray-900">{{ $p->tanggal_lengkap }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        @if($p->jam) {{ $p->jam }} WIB &middot; @endif
                        @if($p->topik) {{ $p->topik }} &middot; @endif
                        @if($p->total_count > 0)
                            Hadir {{ $p->hadir_count }} dari {{ $p->total_count }}
                        @else
                            <span class="text-amber-600 font-semibold">Belum diisi</span>
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('absensi.edit', $p->id) }}" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-yellow-600">{{ $p->total_count > 0 ? 'Ubah absensi' : 'Isi absensi' }}</a>
                    <form action="{{ route('pertemuan.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus pertemuan ini? Catatan kehadirannya ikut terhapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada pertemuan yang dicatat.</div>
        @endforelse
    </div>
@else
    @php
        $tercatat = $kehadiranSaya->count();
        $hadirSaya = $kehadiranSaya->where('status', 'hadir')->count();
        $persen = $tercatat ? round($hadirSaya / $tercatat * 100) : 0;
    @endphp

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6" data-aos="fade-up">
        <p class="text-xs font-semibold text-gray-500 uppercase">Kehadiranmu</p>
        <p class="text-4xl font-bold text-gray-900 mt-1">{{ $persen }}%</p>
        <p class="text-sm text-gray-600">{{ $hadirSaya }} hadir dari {{ $tercatat }} pertemuan yang tercatat</p>
        <div class="flex flex-wrap gap-2 mt-3">
            @foreach(\App\Models\EkskulKehadiran::STATUS as $kode => $label)
                <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">{{ $label[0] }}: {{ $kehadiranSaya->where('status', $kode)->count() }}</span>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto" data-aos="fade-up">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-700">
                <tr><th class="p-3">Tanggal</th><th class="p-3">Topik</th><th class="p-3">Status</th><th class="p-3">Catatan</th></tr>
            </thead>
            <tbody>
                @forelse($kehadiranSaya as $k)
                    <tr class="border-t">
                        <td class="p-3">{{ $k->pertemuan->tanggal->format('d-m-Y') }}</td>
                        <td class="p-3">{{ $k->pertemuan->topik ?? '-' }}</td>
                        <td class="p-3">
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $k->status === 'hadir' ? 'bg-green-100 text-green-800' : ($k->status === 'alpa' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ \App\Models\EkskulKehadiran::STATUS[$k->status][0] }}
                            </span>
                        </td>
                        <td class="p-3 text-gray-600">{{ $k->catatan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada absensi yang tercatat untukmu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif