{{-- Variabel: $ekskul, $pendaftaranSaya, $pengajuanTerakhir, $sayaPembina --}}
@php
    $anggotaAktif = $pendaftaranSaya && $pendaftaranSaya->status === 'diterima' && $pendaftaranSaya->status_anggota === 'aktif';
@endphp
@if($anggotaAktif || $sayaPembina)
    <div class="mt-4 pt-4 border-t border-gray-200">
        @if($pengajuanTerakhir && $pengajuanTerakhir->status === 'menunggu')
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 text-sm">
                <p class="font-semibold">
                    Pengajuan pengunduran dirimu menunggu persetujuan {{ $pengajuanTerakhir->peran === 'pembina' ? 'admin' : 'pembina atau admin' }}.
                </p>
                <p class="text-xs mt-1 whitespace-pre-line">Alasan: {{ $pengajuanTerakhir->alasan }}</p>
                <form action="{{ route('pengunduran.batal', $pengajuanTerakhir->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Batalkan pengajuan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-600 hover:underline font-semibold">Batalkan pengajuan</button>
                </form>
            </div>
        @else
            @if($pengajuanTerakhir && $pengajuanTerakhir->status === 'ditolak')
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-3">
                    Pengajuan sebelumnya ditolak.
                    @if($pengajuanTerakhir->catatan_keputusan)
                        Catatan: {{ $pengajuanTerakhir->catatan_keputusan }}
                    @endif
                </div>
            @endif

            <details>
                <summary class="cursor-pointer text-sm text-red-600 font-semibold">
                    {{ $sayaPembina ? 'Ajukan pengunduran diri sebagai pembina' : 'Ajukan pengunduran diri dari ekskul' }}
                </summary>
                <form action="{{ $sayaPembina ? route('pengunduran.pembina', $ekskul->id) : route('pengunduran.anggota', $ekskul->id) }}" method="POST"
                      class="mt-3 space-y-2" onsubmit="return confirm('Kirim pengajuan pengunduran diri?')">
                    @csrf
                    <textarea name="alasan" rows="3" required minlength="10" maxlength="1000"
                              placeholder="Tulis alasanmu keluar (minimal 10 karakter)" class="w-full border p-2 rounded-lg text-sm">{{ old('alasan') }}</textarea>
                    <p class="text-xs text-gray-500">
                        Statusmu baru resmi keluar setelah disetujui {{ $sayaPembina ? 'admin' : 'pembina atau admin' }}. Sebelum itu kamu tetap tercatat sebagai {{ $sayaPembina ? 'pembina' : 'anggota' }}.
                    </p>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700">Kirim Pengajuan</button>
                </form>
            </details>
        @endif
    </div>
@endif