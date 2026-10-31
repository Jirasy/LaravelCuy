@php
    $dipilih = old('anggota', $prestasi ? $prestasi->anggota->pluck('id')->all() : []);
@endphp
<input type="text" name="judul" value="{{ old('judul', $prestasi->judul ?? '') }}" placeholder="Nama lomba / prestasi" required class="w-full border p-2 rounded-lg">

<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <select name="tingkat" required class="border p-2 rounded-lg">
        @foreach(\App\Models\EkskulPrestasi::TINGKAT as $k => $label)
            <option value="{{ $k }}" {{ old('tingkat', $prestasi->tingkat ?? 'sekolah') === $k ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <input type="text" name="peringkat" value="{{ old('peringkat', $prestasi->peringkat ?? '') }}" placeholder="Peringkat, mis. Juara 1" required class="border p-2 rounded-lg">
    <input type="number" name="tahun" value="{{ old('tahun', $prestasi->tahun ?? date('Y')) }}" min="1990" max="{{ date('Y') + 1 }}" required class="border p-2 rounded-lg">
</div>

<input type="text" name="penyelenggara" value="{{ old('penyelenggara', $prestasi->penyelenggara ?? '') }}" placeholder="Penyelenggara (opsional)" class="w-full border p-2 rounded-lg">
<textarea name="deskripsi" rows="2" placeholder="Keterangan (opsional)" class="w-full border p-2 rounded-lg">{{ old('deskripsi', $prestasi->deskripsi ?? '') }}</textarea>

<div>
    <p class="text-sm font-semibold text-gray-700 mb-1">Diraih oleh <span class="font-normal text-gray-400">(anggota ekskul yang disetujui; muncul di riwayat siswa)</span></p>
    <div class="flex flex-wrap gap-2">
        @forelse($anggotaOpsi as $a)
            <label class="inline-flex items-center gap-2 px-3 py-1.5 border rounded-lg text-sm cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="anggota[]" value="{{ $a->id }}" {{ in_array($a->id, $dipilih) ? 'checked' : '' }}>
                {{ $a->nama_tampil }}
            </label>
        @empty
            <span class="text-xs text-gray-500">Belum ada anggota yang disetujui.</span>
        @endforelse
    </div>
</div>