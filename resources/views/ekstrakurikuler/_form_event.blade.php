<input type="text" name="judul" value="{{ old('judul', $event->judul ?? '') }}" placeholder="Nama event / lomba" required class="w-full border p-2 rounded-lg">

<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <select name="jenis" required class="border p-2 rounded-lg">
        @foreach(\App\Models\EkskulEvent::JENIS as $k => $label)
            <option value="{{ $k }}" {{ old('jenis', $event->jenis ?? 'lomba') === $k ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <div>
        <label class="block text-xs text-gray-500">Tanggal mulai</label>
        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($event) ? $event->tanggal_mulai->format('Y-m-d') : '') }}" required class="w-full border p-2 rounded-lg">
    </div>
    <div>
        <label class="block text-xs text-gray-500">Tanggal selesai <span class="text-gray-400">(opsional)</span></label>
        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($event) && $event->tanggal_selesai ? $event->tanggal_selesai->format('Y-m-d') : '') }}" class="w-full border p-2 rounded-lg">
    </div>
</div>

<input type="text" name="lokasi" value="{{ old('lokasi', $event->lokasi ?? '') }}" placeholder="Lokasi (opsional)" class="w-full border p-2 rounded-lg">
<textarea name="deskripsi" rows="2" placeholder="Keterangan (opsional)" class="w-full border p-2 rounded-lg">{{ old('deskripsi', $event->deskripsi ?? '') }}</textarea>