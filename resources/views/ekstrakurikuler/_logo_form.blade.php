{{-- Tambah / ganti / hapus logo ekskul. Hanya dipasang untuk yang lolos Gate "ubahLogo" (admin, pembina ekskul ini, penanggung jawab anggota). Variabel: $ekskul --}}
<details class="mt-3" @if($errors->has('logo')) open @endif>
    <summary class="cursor-pointer text-sm text-indigo-600 font-semibold">{{ $ekskul->logo ? 'Ganti logo' : 'Tambah logo' }}</summary>

    <div class="mt-3 p-4 rounded-lg border border-gray-200 bg-white max-w-md space-y-3">
        <form action="{{ route('ekskul-logo.update', $ekskul->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            @method('PATCH')

            <div class="flex items-center gap-3">
                <div data-logo-pratinjau>
                    <x-logo-ekskul :ekskul="$ekskul" :ukuran="56" />
                </div>
                <input type="file" name="logo" required accept="image/png,image/jpeg,image/webp" data-logo-input class="w-full text-xs">
            </div>

            <p class="text-xs text-gray-500">
                PNG, JPG, atau WEBP &middot; maksimal 2 MB &middot; minimal 64&times;64 px. Disarankan 512&times;512 px, persegi, latar transparan,
                dan tetap terbaca saat dikecilkan jadi 40 px. SVG tidak diterima.
            </p>
            @error('logo')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

            <x-tombol jenis="sukses" ukuran="md" type="submit">Simpan logo</x-tombol>
        </form>

        @if($ekskul->logo)
            <form action="{{ route('ekskul-logo.destroy', $ekskul->id) }}" method="POST" onsubmit="return confirm('Hapus logo ekskul ini? Katalog akan memakai monogram.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:underline">Hapus logo (pakai monogram)</button>
            </form>
        @endif
    </div>
</details>

<script>
    // Pratinjau logo yang baru dipilih sebelum disimpan (tidak ada yang terkirim ke server)
    document.querySelectorAll('[data-logo-input]').forEach(function (isian) {
        isian.addEventListener('change', function () {
            var pratinjau = isian.closest('form').querySelector('[data-logo-pratinjau]');
            var berkas = isian.files && isian.files[0];
            if (!pratinjau || !berkas || berkas.type.indexOf('image/') !== 0) return;
            pratinjau.innerHTML = '<span class="rounded-full shrink-0 inline-flex items-center justify-center overflow-hidden" style="width:56px;height:56px;padding:6px;background:#fff;border:1px solid rgba(148,163,184,.55)"><img alt="Pratinjau logo" class="w-full h-full object-contain"></span>';
            pratinjau.querySelector('img').src = URL.createObjectURL(berkas);
        });
    });
</script>
