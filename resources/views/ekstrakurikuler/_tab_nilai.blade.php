@php
    $warnaPredikat = ['A' => 'bg-green-100 text-green-800', 'B' => 'bg-blue-100 text-blue-800', 'C' => 'bg-amber-100 text-amber-800', 'D' => 'bg-orange-100 text-orange-800', 'E' => 'bg-red-100 text-red-800'];
@endphp

@if($staff)
    @include('ekstrakurikuler._laporan_tombol', ['jenis' => 'nilai', 'daftarPeriode' => $daftarPeriode])

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto" data-aos="fade-up">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="p-3 w-10">#</th>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Kehadiran</th>
                    <th class="p-3">Nilai terakhir</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($anggotaNilai as $a)
                    @php $terakhir = $a->nilais->sortByDesc('updated_at')->first(); @endphp
                    <tr class="border-t align-top">
                        <td class="p-3 text-gray-500">{{ $loop->iteration }}</td>
                        <td class="p-3 font-semibold">{{ $a->nama_pendaftar }}<span class="block text-xs font-normal text-gray-500">{{ $a->kelas ?? '-' }}</span></td>
                        <td class="p-3">
                            @if(isset($persenHadir[$a->id]))
                                {{ $persenHadir[$a->id] }}%
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($terakhir)
                                <span class="font-semibold">{{ number_format($terakhir->rata_rata, 1) }}</span>
                                <span class="ml-1 px-2 py-0.5 text-xs font-semibold rounded-full {{ $warnaPredikat[$terakhir->predikat] }}">{{ $terakhir->predikat }}</span>
                                <span class="block text-xs text-gray-500">{{ $terakhir->periode }}</span>
                            @else
                                <span class="text-gray-400">Belum dinilai</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <a href="{{ route('nilai.edit', $a->id) }}" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-indigo-700">{{ $terakhir ? 'Beri / ubah nilai' : 'Beri nilai' }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-gray-500">Belum ada anggota aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-500 mt-3">Persentase kehadiran dihitung dari absensi yang sudah tercatat. Siswa hanya bisa melihat nilainya sendiri.</p>
@else
    <div class="space-y-4">
        @forelse($nilaiSaya as $n)
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div>
                        <h3 class="font-bold text-gray-900">{{ $n->periode }}</h3>
                        <p class="text-xs text-gray-500">Dinilai oleh {{ $n->penilai->name ?? 'pembina' }} &middot; {{ $n->updated_at->format('d-m-Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-gray-900 leading-none">{{ number_format($n->rata_rata, 1) }}</p>
                        <span class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $warnaPredikat[$n->predikat] }}">Predikat {{ $n->predikat }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    @foreach(\App\Models\EkskulNilai::ASPEK as $kolom => $label)
                        <div>
                            <div class="flex justify-between text-xs text-gray-600"><span>{{ $label }}</span><span class="font-semibold">{{ $n->{$kolom} }}</span></div>
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden mt-1">
                                <div class="h-2 bg-indigo-500 rounded-full" style="width: {{ $n->{$kolom} }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($n->saran)
                    <div class="mt-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                        <p class="text-xs font-semibold text-green-800 uppercase mb-1">Saran</p>
                        <p class="text-sm text-green-900 whitespace-pre-line">{{ $n->saran }}</p>
                    </div>
                @endif
                @if($n->kritik)
                    <div class="mt-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                        <p class="text-xs font-semibold text-amber-800 uppercase mb-1">Kritik membangun</p>
                        <p class="text-sm text-amber-900 whitespace-pre-line">{{ $n->kritik }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Kamu belum mendapat nilai di ekskul ini.</div>
        @endforelse
    </div>
@endif