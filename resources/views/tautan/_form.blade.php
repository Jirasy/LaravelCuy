{{-- Form tambah tautan. Opsional: $ekskul (kalau untuk satu ekskul) --}}
@if(isset($ekskul))
    <input type="hidden" name="ekstrakurikuler_id" value="{{ $ekskul->id }}">
@endif
<div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
    <select name="platform" required class="border p-2 rounded-lg text-sm">
        @foreach(\App\Models\EkskulTautan::PLATFORM as $key => $p)
            <option value="{{ $key }}">{{ $p[0] }} {{ $p[1] }}</option>
        @endforeach
    </select>
    <input type="text" name="label" maxlength="60" placeholder="Nama tampilan (opsional)" class="border p-2 rounded-lg text-sm sm:col-span-2">
</div>
<input type="url" name="url" required maxlength="500" placeholder="https://..." class="w-full border p-2 rounded-lg text-sm">
<p class="text-xs text-gray-500">Harus diawali https:// dan sesuai platform yang dipilih (mis. YouTube hanya youtube.com / youtu.be).</p>
<button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700">Simpan Tautan</button>