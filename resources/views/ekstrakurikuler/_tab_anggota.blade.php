<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-700">
            <tr>
                <th class="p-3 w-12">#</th>
                <th class="p-3">Nama</th>
                <th class="p-3">Kelas</th>
                <th class="p-3">Jabatan</th>
                <th class="p-3">Angkatan Ekskul</th>
                <th class="p-3">Periode</th>
                @if($staff)<th class="p-3">NISN</th><th class="p-3">Atur</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($anggota as $a)
                <tr class="border-t align-top">
                    <td class="p-3 text-gray-500">{{ $loop->iteration }}</td>
                    <td class="p-3">
                        @if($a->user_id)
                            <a href="{{ route('profil.show', $a->user_id) }}" class="font-semibold hover:underline" title="Lihat profil">{{ $a->nama_pendaftar }}</a>
                        @else
                            <span class="font-semibold">{{ $a->nama_pendaftar }}</span>
                        @endif
                        <span class="ml-1 px-2 py-0.5 text-xs rounded-full
                            {{ $a->peran === 'admin' ? 'bg-purple-100 text-purple-800' : ($a->peran === 'pembina' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($a->peran) }}
                        </span>
                    </td>
                    <td class="p-3">{{ $a->kelas ?? '-' }}</td>
                    <td class="p-3">
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $a->jabatan === 'anggota' ? 'bg-gray-100 text-gray-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $a->jabatan_label }}
                        </span>
                    </td>
                    <td class="p-3">{{ $a->angkatan_ekskul ?? '-' }}</td>
                    <td class="p-3">{{ $a->periode }}</td>
                    @if($staff)
                        <td class="p-3 text-gray-500">{{ $a->nisn ?? '-' }}</td>
                        <td class="p-3">
                            <details>
                                <summary class="cursor-pointer text-xs text-indigo-600">Atur</summary>
                                <form action="{{ route('keanggotaan.update', $a->id) }}" method="POST" class="mt-2 space-y-2 min-w-[14rem]">
                                    @csrf
                                    @method('PATCH')
                                    <select name="jabatan" class="border p-1 rounded text-xs w-full">
                                        @foreach(\App\Models\PendaftaranEkskul::JABATAN as $k => $label)
                                            <option value="{{ $k }}" {{ $a->jabatan === $k ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="angkatan_ekskul" value="{{ $a->angkatan_ekskul }}" placeholder="Angkatan ekskul" class="border p-1 rounded text-xs w-full">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="number" name="tahun_mulai" value="{{ $a->tahun_mulai }}" placeholder="Mulai" class="border p-1 rounded text-xs">
                                        <input type="number" name="tahun_selesai" value="{{ $a->tahun_selesai }}" placeholder="Selesai" class="border p-1 rounded text-xs">
                                    </div>
                                    <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded text-xs hover:bg-gray-900">Simpan</button>
                                </form>
                            </details>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $staff ? 8 : 6 }}" class="p-6 text-center text-gray-500">Belum ada anggota yang disetujui.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($staff)
    <p class="text-xs text-gray-500 mt-3">Setujui atau tolak pendaftar di halaman <a href="{{ route('pendaftaran.index') }}" class="underline">Data Pendaftaran</a>. Kosongkan "Selesai" kalau anggota masih aktif.</p>
@endif