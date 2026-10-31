{{-- Kepengurusan OSIS siswa: koreksi riwayat yang ada + catat angkatan baru tanpa lewat seleksi CaSis.
     Variabel: $u (siswa), $infoOsis. Saran angkatan memakai <datalist id="angkatan-osis"> di users/index. --}}
@php
    $baris = $infoOsis['riwayat'][$u->id] ?? collect();
    $tahunIni = now()->year;
@endphp
<details class="w-full">
    <summary class="cursor-pointer inline-block bg-slate-700 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-slate-800 list-none">
        Kepengurusan OSIS @if($baris->isNotEmpty())({{ $baris->count() }})@endif
    </summary>

    <div class="mt-2 w-80 space-y-3 text-xs">
        @foreach($baris as $b)
            <form action="{{ route('casis.pengurus.koreksi', $b->id) }}" method="POST" class="border rounded-lg p-2 bg-gray-50 space-y-1">
                @csrf
                @method('PATCH')
                <p class="font-semibold text-gray-700">
                    Koreksi: {{ $b->angkatan_ekskul ?: 'tanpa angkatan' }} &middot; {{ \App\Services\OsisService::STATUS_PENGURUS[$b->status_anggota] ?? $b->status_anggota }}
                </p>
                <div class="grid grid-cols-2 gap-1">
                    <input type="text" name="angkatan" list="angkatan-osis" value="{{ $b->angkatan_ekskul }}" placeholder="Angkatan, mis. 2024/2025" maxlength="20" required class="border p-1 rounded col-span-2">
                    <input type="number" name="tahun_mulai" value="{{ $b->tahun_mulai ?? $tahunIni }}" placeholder="Tahun mulai" required class="border p-1 rounded">
                    <input type="number" name="tahun_selesai" value="{{ $b->tahun_selesai }}" placeholder="Tahun selesai" class="border p-1 rounded">
                    <select name="status" class="border p-1 rounded col-span-2">
                        @foreach(\App\Services\OsisService::STATUS_PENGURUS as $kode => $label)
                            <option value="{{ $kode }}" {{ $b->status_anggota === $kode ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded font-semibold hover:bg-indigo-700">Simpan koreksi</button>
            </form>
        @endforeach

        <form action="{{ route('casis.pengurus', $u->id) }}" method="POST" class="border rounded-lg p-2 space-y-1"
              onsubmit="return confirm('Catat {{ e($u->name) }} sebagai pengurus OSIS secara langsung, tanpa seleksi CaSis?')">
            @csrf
            <p class="font-semibold text-gray-700">Catat angkatan (tanpa seleksi CaSis)</p>
            <div class="grid grid-cols-2 gap-1">
                <input type="text" name="angkatan" list="angkatan-osis" placeholder="Angkatan, mis. 2024/2025" maxlength="20" required class="border p-1 rounded col-span-2">
                <input type="number" name="tahun_mulai" value="{{ $tahunIni }}" placeholder="Tahun mulai" required class="border p-1 rounded">
                <input type="number" name="tahun_selesai" placeholder="Tahun selesai" class="border p-1 rounded">
                <select name="status" class="border p-1 rounded col-span-2">
                    @foreach(\App\Services\OsisService::STATUS_PENGURUS as $kode => $label)
                        <option value="{{ $kode }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="catatan" placeholder="Catatan (opsional)" maxlength="200" class="border p-1 rounded col-span-2">
            </div>
            <p class="text-gray-500">Purna tugas wajib diisi tahun selesainya.</p>
            <button type="submit" class="bg-slate-700 text-white px-3 py-1 rounded font-semibold hover:bg-slate-800">Catat pengurus OSIS</button>
        </form>
    </div>
</details>
