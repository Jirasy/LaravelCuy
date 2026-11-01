{{-- Pilihan kategori ekskul (boleh lebih dari satu). Dipakai create & edit untuk admin dan pembina pengelola.
     Kategori nonaktif hanya tampil kalau sedang melekat di ekskul ini. Query langsung (bukan lazy load relasi). --}}
@php
    $ekskulIni  = $ekstrakurikuler ?? null;
    $terpasang  = $ekskulIni?->exists ? $ekskulIni->kategoris()->pluck('kategori_ekskuls.id')->map(fn ($i) => (int) $i)->all() : [];
    // Setelah gagal validasi, pilihan lama dari form dipertahankan (termasuk "tidak ada yang dipilih")
    $dipilih    = collect(old('kategori_form') ? (array) old('kategori_ids', []) : $terpasang)->map(fn ($i) => (int) $i)->all();
    $daftarKategori = \App\Models\KategoriEkskul::urut()
        ->where(fn ($q) => $q->where('aktif', true)->orWhereIn('id', $terpasang))
        ->get();
@endphp
<div>
    <label class="block text-sm font-semibold text-gray-700">Kategori ekskul <span class="font-normal text-gray-500">(boleh lebih dari satu)</span></label>
    <input type="hidden" name="kategori_form" value="1">

    @if($daftarKategori->isEmpty())
        <p class="text-xs text-gray-500 mt-1">
            Belum ada kategori.
            @if(auth()->user()->isAdmin())
                <a href="{{ route('kategori-ekskul.index') }}" class="underline">Tambah kategori</a>
            @endif
        </p>
    @else
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach($daftarKategori as $k)
                <label class="cursor-pointer">
                    <input type="checkbox" name="kategori_ids[]" value="{{ $k->id }}" class="peer sr-only" {{ in_array($k->id, $dipilih, true) ? 'checked' : '' }}>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold border border-gray-300 bg-white text-gray-700 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-focus-visible:ring peer-focus-visible:ring-indigo-200">{{ $k->nama }}{{ $k->aktif ? '' : ' (nonaktif)' }}</span>
                </label>
            @endforeach
        </div>
        <p class="text-xs text-gray-500 mt-1">
            Label topik untuk memudahkan siswa mencari, mis. Silat bisa masuk Bela Diri dan Seni &amp; Olahraga.
            @if(auth()->user()->isAdmin())
                <a href="{{ route('kategori-ekskul.index') }}" class="underline">Kelola kategori</a>
            @endif
        </p>
    @endif
</div>
