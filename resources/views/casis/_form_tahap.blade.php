{{-- Isian satu tahap seleksi. Variabel: $t (SeleksiTahap atau null untuk tahap baru). Dipakai di dalam <form>. --}}
@php $baru = ! isset($t) || ! $t; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div class="sm:col-span-2">
        <label class="block text-xs text-gray-500">Nama tahap</label>
        <input type="text" name="nama" maxlength="60" required value="{{ $baru ? '' : $t->nama }}" placeholder="mis. Tes tulis, Wawancara, Praktik" class="w-full border p-2 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-500">Mulai <span class="text-gray-400">(opsional)</span></label>
        <input type="datetime-local" name="mulai_pada" value="{{ $baru || ! $t->mulai_pada ? '' : $t->mulai_pada->format('Y-m-d\TH:i') }}" class="w-full border p-2 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-500">Selesai <span class="text-gray-400">(wajib kalau mulai diisi)</span></label>
        <input type="datetime-local" name="selesai_pada" value="{{ $baru || ! $t->selesai_pada ? '' : $t->selesai_pada->format('Y-m-d\TH:i') }}" class="w-full border p-2 rounded-lg text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-xs text-gray-500">Tempat <span class="text-gray-400">(opsional)</span></label>
        <input type="text" name="tempat" maxlength="120" value="{{ $baru ? '' : $t->tempat }}" placeholder="mis. Aula, Ruang OSIS" class="w-full border p-2 rounded-lg text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-xs text-gray-500">Keterangan <span class="text-gray-400">(opsional: yang perlu dibawa, materi, dll)</span></label>
        <textarea name="keterangan" rows="2" maxlength="1000" class="w-full border p-2 rounded-lg text-sm">{{ $baru ? '' : $t->keterangan }}</textarea>
    </div>
</div>
