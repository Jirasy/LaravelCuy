@php
    $hariDipilih = old('hari', array_keys($awal));
    $jamLama     = old('jadwal', $awal);
@endphp

<div>
    <label class="block text-sm font-semibold text-gray-700">
        Hari &amp; Jam Pelaksanaan
        <span class="text-gray-400 font-normal">(boleh lebih dari satu hari, jam bisa berbeda tiap hari)</span>
    </label>

    <div class="flex flex-wrap gap-2 mt-2">
        @foreach(\App\Enums\Hari::cases() as $h)
            <label class="inline-flex items-center gap-2 px-3 py-1.5 border rounded-lg text-sm cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="hari[]" value="{{ $h->value }}" class="cek-hari" {{ in_array($h->value, $hariDipilih) ? 'checked' : '' }}>
                {{ $h->label() }}
            </label>
        @endforeach
    </div>

    <div class="mt-3 space-y-2">
        @foreach(\App\Enums\Hari::cases() as $h)
            @php $aktif = in_array($h->value, $hariDipilih); @endphp
            <div class="baris-jam grid grid-cols-3 gap-3 items-end bg-gray-50 border rounded-lg p-3 {{ $aktif ? '' : 'hidden' }}" data-hari="{{ $h->value }}">
                <div class="text-sm font-semibold text-gray-700">{{ $h->label() }}</div>
                <div>
                    <label class="block text-xs text-gray-500">Jam Mulai</label>
                    <input type="time" name="jadwal[{{ $h->value }}][jam_mulai]" value="{{ $jamLama[$h->value]['jam_mulai'] ?? '' }}"
                           {{ $aktif ? 'required' : 'disabled' }} class="w-full border p-2 rounded-lg bg-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Jam Selesai</label>
                    <input type="time" name="jadwal[{{ $h->value }}][jam_selesai]" value="{{ $jamLama[$h->value]['jam_selesai'] ?? '' }}"
                           {{ $aktif ? 'required' : 'disabled' }} class="w-full border p-2 rounded-lg bg-white">
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    document.querySelectorAll('.cek-hari').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const baris = document.querySelector('.baris-jam[data-hari="' + cb.value + '"]');
            const inputs = baris.querySelectorAll('input');

            baris.classList.toggle('hidden', !cb.checked);
            inputs.forEach(function (i) { i.disabled = !cb.checked; i.required = cb.checked; });

            // Hari baru dicentang & jamnya masih kosong: salin jam dari hari lain yang sudah terisi
            if (cb.checked && !inputs[0].value && !inputs[1].value) {
                const sumber = Array.from(document.querySelectorAll('.baris-jam:not(.hidden)'))
                    .find(function (b) {
                        if (b === baris) return false;
                        const x = b.querySelectorAll('input');
                        return x[0].value && x[1].value;
                    });
                if (sumber) {
                    const x = sumber.querySelectorAll('input');
                    inputs[0].value = x[0].value;
                    inputs[1].value = x[1].value;
                }
            }
        });
    });
</script>