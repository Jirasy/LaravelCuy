{{--
  Form task. Variabel:
  $mode: 'pribadi' (untuk diri sendiri) | 'ekskul' (ke anggota ekskul, butuh $ekskul & $anggotaOpsi) | 'user' (ke satu orang, butuh $userTarget)
--}}
@php
    $mode = $mode ?? 'pribadi';
    $uid  = 'f' . uniqid();
@endphp
<form action="{{ route('tugas.store') }}" method="POST" class="space-y-2">
    @csrf

    @if($mode === 'ekskul')
        <input type="hidden" name="ekstrakurikuler_id" value="{{ $ekskul->id }}">
    @endif

    @if($mode === 'user')
        <input type="hidden" name="penerima[]" value="{{ $userTarget->id }}">
        <input type="hidden" name="target" value="pilih">
        <p class="text-xs text-gray-500">Untuk: <strong class="text-gray-800">{{ $userTarget->name }}</strong>.
            Pembina hanya bisa memberi task ke anggota aktif ekskul yang ia bina.</p>
    @endif

    <input type="text" name="judul" required maxlength="200" placeholder="Judul task" class="w-full border p-2 rounded-lg text-sm">
    <textarea name="deskripsi" rows="2" maxlength="2000" placeholder="Keterangan (opsional)" class="w-full border p-2 rounded-lg text-sm"></textarea>
    <div>
        <label class="block text-xs text-gray-500">Tenggat (opsional)</label>
        <input type="date" name="tenggat" min="{{ date('Y-m-d') }}" class="w-full border p-2 rounded-lg text-sm">
    </div>

    @if($mode === 'ekskul')
        <div class="space-y-1 text-sm text-gray-700">
            <label class="flex items-center gap-2"><input type="radio" name="target" value="semua_anggota" checked> Semua anggota aktif ({{ $anggotaOpsi->count() }})</label>
            <label class="flex items-center gap-2"><input type="radio" name="target" value="pilih"> Pilih anggota tertentu</label>
            <div id="pilih-{{ $uid }}" class="hidden flex-wrap gap-2 pt-1">
                @foreach($anggotaOpsi as $a)
                    <label class="inline-flex items-center gap-1 px-2 py-1 border rounded-lg text-xs cursor-pointer">
                        <input type="checkbox" name="penerima[]" value="{{ $a->user_id }}"> {{ $a->nama_pendaftar }}
                    </label>
                @endforeach
            </div>
        </div>
        <script>
            (function () {
                var kotak = document.getElementById('pilih-{{ $uid }}');
                document.currentScript.parentElement.querySelectorAll('input[name=target]').forEach(function (r) {
                    r.addEventListener('change', function () {
                        kotak.classList.toggle('hidden', this.value !== 'pilih');
                        kotak.classList.toggle('flex', this.value === 'pilih');
                    });
                });
            })();
        </script>
    @endif

    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">
        {{ $mode === 'pribadi' ? 'Tambah Task' : 'Beri Task' }}
    </button>
</form>