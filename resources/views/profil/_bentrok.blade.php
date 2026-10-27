{{--
  Peringatan jadwal ekskul bentrok (hanya untuk pemilik profil berperan siswa).
  Variabel: $bentrok (koleksi ['hari','a','b','pilihan']), $user
--}}
@php
    $label = [
        'ekskul_a' => fn ($b) => 'Hadir di ' . $b['a']['ekskul'],
        'ekskul_b' => fn ($b) => 'Hadir di ' . $b['b']['ekskul'],
        'bagi_waktu' => fn ($b) => 'Bagi waktu',
    ];
@endphp
<div class="bg-amber-50 border border-amber-300 text-amber-900 p-5 rounded-xl shadow-sm mb-6 text-sm" id="bentrok-jadwal" role="alert" data-aos="fade-up">
    <h2 class="text-base font-bold mb-1">⚠️ Ada {{ $bentrok->count() }} jadwal ekskul yang bentrok</h2>
    <p class="mb-4">Jamnya tumpang tindih di hari yang sama. Pilih ekskul yang kamu hadiri atau atur pembagian waktunya, supaya pembina tahu rencanamu.</p>

    <div class="space-y-3">
        @foreach($bentrok as $b)
            @php
                $pilihan = $b['pilihan'];
                $hariLabel = \App\Enums\Hari::from($b['hari'])->label();
            @endphp
            <div class="bg-white border border-amber-200 rounded-lg p-4 text-gray-800">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="font-semibold">
                        {{ $hariLabel }}:
                        {{ $b['a']['ekskul'] }} <span class="font-normal text-gray-500">({{ $b['a']['mulai'] }}–{{ $b['a']['selesai'] }})</span>
                        <span class="text-amber-600">↔</span>
                        {{ $b['b']['ekskul'] }} <span class="font-normal text-gray-500">({{ $b['b']['mulai'] }}–{{ $b['b']['selesai'] }})</span>
                    </p>
                    @if($pilihan)
                        <span class="badge-pill badge-diterima"><span class="dot"></span>{{ $label[$pilihan->pilihan]($b) }}</span>
                    @else
                        <span class="badge-pill badge-pending"><span class="dot"></span>Belum dipilih</span>
                    @endif
                </div>

                @if($pilihan && $pilihan->catatan)
                    <p class="mt-2 text-xs text-gray-600">Catatan: {{ $pilihan->catatan }}</p>
                @endif

                <details class="mt-3" @if(!$pilihan) open @endif>
                    <summary class="cursor-pointer text-xs font-semibold text-indigo-600">{{ $pilihan ? 'Ubah pilihan' : 'Pilih sekarang' }}</summary>

                    <form method="POST" action="{{ route('bentrok.simpan') }}" class="mt-3 space-y-3">
                        @csrf
                        <input type="hidden" name="hari" value="{{ $b['hari'] }}">
                        <input type="hidden" name="ekskul_a_id" value="{{ $b['a']['ekskul_id'] }}">
                        <input type="hidden" name="ekskul_b_id" value="{{ $b['b']['ekskul_id'] }}">

                        <div class="flex flex-wrap gap-2">
                            @foreach($label as $nilai => $buat)
                                <label class="cursor-pointer">
                                    <input type="radio" name="pilihan" value="{{ $nilai }}" class="peer sr-only" required
                                           @checked(($pilihan->pilihan ?? null) === $nilai)>
                                    <span class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold border border-gray-300 bg-gray-50 text-gray-700
                                                 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600
                                                 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-400">
                                        {{ $buat($b) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1" for="catatan-{{ $loop->index }}">Catatan pembagian waktu <span class="text-gray-400">(wajib kalau memilih "Bagi waktu")</span></label>
                            <input type="text" id="catatan-{{ $loop->index }}" name="catatan" maxlength="200"
                                   value="{{ old('catatan', $pilihan->catatan ?? '') }}"
                                   placeholder="Contoh: {{ $b['a']['ekskul'] }} sampai 09:00, lalu pindah ke {{ $b['b']['ekskul'] }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>

                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-indigo-700">Simpan pilihan</button>
                    </form>

                    @if($pilihan)
                        <form method="POST" action="{{ route('bentrok.hapus', $pilihan) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus pilihan</button>
                        </form>
                    @endif
                </details>
            </div>
        @endforeach
    </div>

    @if($errors->any())
        <p class="mt-3 text-xs font-semibold text-red-700">{{ $errors->first() }}</p>
    @endif
</div>
