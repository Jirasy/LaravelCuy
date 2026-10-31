{{-- Variabel: $ekskul. Banner komunitas + tombol Resmikan (admin). Hanya tampil untuk komunitas yang belum resmi. --}}
@if($ekskul->isKomunitas())
    @php
        $pilihanTipe = \App\Models\JenisEkskul::urut()->aktif()->resmi()->get();
    @endphp
    <div class="mt-4 pt-4 border-t border-gray-200">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">🌱 Komunitas · belum resmi</p>
        <p class="text-sm text-gray-700 mb-3">
            Ekskul ini masih komunitas{{ $ekskul->pembuat ? ', dibuat oleh '.$ekskul->pembuat->name : '' }}. Setelah diresmikan admin, ia menjadi ekskul resmi.
        </p>

        @if(auth()->user()->isAdmin())
            <form action="{{ route('komunitas.resmikan', $ekskul->id) }}" method="POST" class="flex flex-wrap items-end gap-2"
                  onsubmit="return confirm('Resmikan komunitas ini jadi ekskul?')">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500">Tipe setelah diresmikan</label>
                    <select name="jenis_ekskul_id" required class="border p-2 rounded-lg text-sm">
                        @foreach($pilihanTipe as $t)
                            <option value="{{ $t->id }}" {{ $t->kode === 'ekskul' ? 'selected' : '' }}>{{ $t->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700">Resmikan</button>
            </form>
        @endif
    </div>
@endif
