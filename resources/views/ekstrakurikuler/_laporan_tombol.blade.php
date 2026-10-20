{{-- Variabel: $ekskul, $jenis ('anggota' | 'absensi' | 'nilai'), opsional $daftarPeriode --}}
<form method="GET" class="flex flex-wrap items-end gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3 mb-6">
    <p class="w-full text-xs font-semibold text-gray-600 uppercase">Laporan {{ $jenis }}</p>

    @if($jenis === 'absensi')
        <div>
            <label class="block text-xs text-gray-500">Dari</label>
            <input type="date" name="dari" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="border p-1.5 rounded text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500">Sampai</label>
            <input type="date" name="sampai" value="{{ now()->format('Y-m-d') }}" class="border p-1.5 rounded text-sm">
        </div>
    @endif

    @if($jenis === 'nilai')
        <div>
            <label class="block text-xs text-gray-500">Periode <span class="text-gray-400">(kosong = terbaru)</span></label>
            <input type="text" name="periode" list="daftar-periode-laporan" maxlength="60" class="border p-1.5 rounded text-sm">
            <datalist id="daftar-periode-laporan">
                @foreach(($daftarPeriode ?? collect()) as $per)<option value="{{ $per }}">@endforeach
            </datalist>
        </div>
    @endif

    <button type="submit" formaction="{{ route('laporan.unduh', [$ekskul->id, $jenis, 'cetak']) }}" formtarget="_blank"
            class="bg-gray-700 text-white px-3 py-1.5 rounded text-sm font-semibold hover:bg-gray-800">🖨️ Cetak</button>
    <button type="submit" formaction="{{ route('laporan.unduh', [$ekskul->id, $jenis, 'pdf']) }}"
            class="bg-red-600 text-white px-3 py-1.5 rounded text-sm font-semibold hover:bg-red-700">PDF</button>
    <button type="submit" formaction="{{ route('laporan.unduh', [$ekskul->id, $jenis, 'excel']) }}"
            class="bg-green-600 text-white px-3 py-1.5 rounded text-sm font-semibold hover:bg-green-700">Excel</button>
</form>