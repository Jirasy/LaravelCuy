{{-- Kontak & sosmed ekskul, khusus anggota aktif + admin/pembina ekskul ini.
     Variabel: $kontak (controller hanya mengisinya untuk yang berhak, kosong untuk non-anggota), $staff, $ekskul --}}
@if(count($kontak) > 0 || $staff)
    <div class="mt-4 pt-4 border-t border-gray-200" id="kontak-anggota">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Kontak &amp; Sosial Media <span class="normal-case font-normal">(khusus anggota aktif)</span></p>

        @if(count($kontak) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($kontak as $k)
                    <a href="{{ $k['url'] }}" target="_blank" rel="noopener noreferrer"
                       class="px-3 py-1 text-sm rounded-full bg-emerald-100 text-emerald-800 hover:bg-emerald-200"><x-ikon :nama="$k['ikon']" :ukuran="14" /> {{ $k['nama'] }}@if($k['keterangan']) <span class="text-xs">({{ $k['keterangan'] }})</span>@endif</a>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400">Belum diisi. Atur lewat <a href="{{ route('ekstrakurikuler.edit', $ekskul->id) }}" class="underline">Edit Ekskul</a>.</p>
        @endif

        @if($staff)
            <p class="text-xs text-gray-500 mt-2">Hanya terlihat oleh anggota aktif, admin, dan pembina ekskul ini.</p>
        @endif
    </div>
@endif
