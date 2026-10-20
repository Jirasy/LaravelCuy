{{-- Khusus pengelola. Variabel: $ekskul --}}
@php
    $tutupPada = $ekskul->pendaftaran_tutup_pada && $ekskul->pendaftaran_tutup_pada->isFuture()
        ? $ekskul->pendaftaran_tutup_pada->format('Y-m-d\TH:i')
        : '';
@endphp
<div class="mt-4 pt-4 border-t border-gray-200">
    <div class="flex flex-wrap items-center gap-2 mb-1">
        <p class="text-xs font-semibold text-gray-500 uppercase">Pendaftaran</p>
        @if($ekskul->pendaftaran_terbuka)
            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Dibuka</span>
        @else
            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $ekskul->label_pendaftaran }}</span>
        @endif
        @if($ekskul->kuota)
            <span class="text-xs text-gray-500">{{ $ekskul->anggota_count }} dari {{ $ekskul->kuota }} kursi terisi</span>
        @endif
    </div>

    <details>
        <summary class="cursor-pointer text-sm text-indigo-600 font-semibold">Atur buka/tutup pendaftaran</summary>
        <form action="{{ route('pendaftaran-kontrol.update', $ekskul->id) }}" method="POST" class="mt-3 space-y-3">
            @csrf
            @method('PATCH')

            <input type="hidden" name="pendaftaran_dibuka" value="0">
            <label class="flex items-center gap-2 text-sm text-gray-800">
                <input type="checkbox" name="pendaftaran_dibuka" value="1" {{ $ekskul->pendaftaran_dibuka ? 'checked' : '' }}>
                Pendaftaran dibuka
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500">Kuota anggota <span class="text-gray-400">(kosong = tanpa batas)</span></label>
                    <input type="number" name="kuota" min="1" max="1000" value="{{ $ekskul->kuota }}" class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Tutup otomatis pada <span class="text-gray-400">(opsional)</span></label>
                    <input type="datetime-local" name="pendaftaran_tutup_pada" value="{{ $tutupPada }}" class="w-full border p-2 rounded-lg text-sm">
                </div>
            </div>

            <p class="text-xs text-gray-500">Saat ditutup, tombol daftar di sisi siswa berubah menjadi "Pendaftaran Ditutup". Admin dan pembina tetap bisa mendaftarkan anggota secara manual.</p>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900">Simpan</button>
        </form>
    </details>
</div>