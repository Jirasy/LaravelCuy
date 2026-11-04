<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div class="sm:col-span-2">
        <label class="block text-xs text-gray-500">Nama jabatan</label>
        <input type="text" name="nama" list="saran-jabatan" value="{{ old('nama', $jabatan->nama ?? '') }}" required maxlength="100"
               placeholder="mis. Sekretaris, Seksi Humas" class="w-full border p-2 rounded-lg">
        <datalist id="saran-jabatan">
            @foreach(['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Koordinator', 'Pelatih', 'Pembina Pendamping', 'Seksi Humas', 'Seksi Dokumentasi', 'Seksi Acara', 'Seksi Perlengkapan'] as $saran)
                <option value="{{ $saran }}">
            @endforeach
        </datalist>
    </div>
    <div>
        <label class="block text-xs text-gray-500">Variasi <span class="text-gray-400">(opsional)</span></label>
        <input type="text" name="varian" value="{{ old('varian', $jabatan->varian ?? '') }}" maxlength="20"
               placeholder="mis. 1, 2, Putra" class="w-full border p-2 rounded-lg">
    </div>
</div>

<div>
    <label class="block text-xs text-gray-500">Deskripsi singkat</label>
    <textarea name="deskripsi" rows="2" class="w-full border p-2 rounded-lg">{{ old('deskripsi', $jabatan->deskripsi ?? '') }}</textarea>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label class="block text-xs text-gray-500">Tugas</label>
        <textarea name="tugas" rows="3" placeholder="Satu tugas per baris" class="w-full border p-2 rounded-lg">{{ old('tugas', $jabatan->tugas ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-xs text-gray-500">Tanggung jawab</label>
        <textarea name="tanggung_jawab" rows="3" placeholder="Satu tanggung jawab per baris" class="w-full border p-2 rounded-lg">{{ old('tanggung_jawab', $jabatan->tanggung_jawab ?? '') }}</textarea>
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs text-gray-500">Kuota pemegang <span class="text-gray-400">(kosong = bebas)</span></label>
        <input type="number" name="kuota" min="1" max="50" value="{{ old('kuota', $jabatan->kuota ?? '') }}" class="w-full border p-2 rounded-lg">
    </div>
    <div>
        <label class="block text-xs text-gray-500">Urutan tampil <span class="text-gray-400">(kecil = paling atas)</span></label>
        <input type="number" name="urutan" min="0" max="255" value="{{ old('urutan', $jabatan->urutan ?? 0) }}" class="w-full border p-2 rounded-lg">
    </div>
</div>

<label class="flex items-start gap-2 text-sm text-gray-800 bg-amber-50 border border-amber-200 rounded-lg p-3">
    <input type="checkbox" name="boleh_kelola_anggota" value="1" class="mt-0.5"
           {{ old('boleh_kelola_anggota', $jabatan->boleh_kelola_anggota ?? false) ? 'checked' : '' }}>
    <span>
        <span class="font-semibold">Penanggung jawab anggota</span>
        <span class="block text-xs text-gray-600">Pemegang jabatan ini (selama masih anggota aktif) ikut mengatur kuota, buka/tutup pendaftaran, dan menyetujui atau menolak pendaftar ekskul ini. Jabatan, keanggotaan, dan pengeluaran anggota tetap hanya hak admin dan pembina.</span>
    </span>
</label>