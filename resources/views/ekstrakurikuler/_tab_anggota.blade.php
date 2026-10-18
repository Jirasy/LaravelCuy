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
                        <div class="flex flex-wrap gap-1">
                            @forelse($a->jabatanAktif as $jb)
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">{{ $jb->label_lengkap }}</span>
                            @empty
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Anggota</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="p-3">{{ $a->angkatan_ekskul ?? '-' }}</td>
                    <td class="p-3">{{ $a->periode }}</td>
                    @if($staff)
                        <td class="p-3 text-gray-500">{{ $a->nisn ?? '-' }}</td>
                        <td class="p-3">
                            <details>
                                <summary class="cursor-pointer text-xs text-indigo-600">Atur</summary>
                                <div class="mt-2 space-y-3 min-w-[16rem]">

                                    {{-- Jabatan yang sedang dipegang --}}
                                    @foreach($a->jabatanAktif as $jb)
                                        <div class="flex items-center justify-between gap-2 text-xs">
                                            <span>{{ $jb->label_lengkap }} <span class="text-gray-400">sejak {{ $jb->pivot->periode_mulai }}</span></span>
                                            <form action="{{ route('anggota-jabatan.destroy', $jb->pivot->id) }}" method="POST" onsubmit="return confirm('Cabut jabatan {{ e($jb->label_lengkap) }} dari {{ e($a->nama_pendaftar) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Cabut</button>
                                            </form>
                                        </div>
                                    @endforeach

                                    {{-- Beri jabatan baru --}}
                                    @if($jabatans->isEmpty())
                                        <p class="text-xs text-gray-500">Belum ada jabatan. Buat dulu di tab <strong>Struktur</strong>.</p>
                                    @else
                                        <form action="{{ route('anggota-jabatan.store', $a->id) }}" method="POST" class="space-y-1">
                                            @csrf
                                            <select name="jabatan_id" required class="border p-1 rounded text-xs w-full">
                                                <option value="">-- Beri jabatan --</option>
                                                @foreach($jabatans->whereNotIn('id', $a->jabatanAktif->pluck('id')) as $opsi)
                                                    <option value="{{ $opsi->id }}" {{ $opsi->kuota_penuh ? 'disabled' : '' }}>
                                                        {{ $opsi->label_lengkap }}{{ $opsi->kuota_penuh ? ' (penuh)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="flex gap-2">
                                                <input type="number" name="periode_mulai" value="{{ date('Y') }}" min="1990" max="{{ date('Y') + 1 }}" title="Tahun mulai menjabat" class="border p-1 rounded text-xs w-24">
                                                <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Beri</button>
                                            </div>
                                        </form>
                                    @endif

                                    {{-- Angkatan & periode keanggotaan --}}
                                    <form action="{{ route('keanggotaan.update', $a->id) }}" method="POST" class="space-y-1 border-t pt-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="angkatan_ekskul" value="{{ $a->angkatan_ekskul }}" placeholder="Angkatan ekskul" class="border p-1 rounded text-xs w-full">
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="number" name="tahun_mulai" value="{{ $a->tahun_mulai }}" placeholder="Mulai" class="border p-1 rounded text-xs">
                                            <input type="number" name="tahun_selesai" value="{{ $a->tahun_selesai }}" placeholder="Selesai" class="border p-1 rounded text-xs">
                                        </div>
                                        <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded text-xs hover:bg-gray-900">Simpan</button>
                                    </form>

                                    {{-- Keluarkan dari ekskul (alasan wajib) --}}
                                    @if($a->user_id !== auth()->id())
                                        <form action="{{ route('pengunduran.keluarkan', $a->id) }}" method="POST" class="space-y-1 border-t pt-2"
                                              onsubmit="return confirm('Keluarkan {{ e($a->nama_pendaftar) }} dari ekskul ini? Jabatannya ikut diakhiri.')">
                                            @csrf
                                            <textarea name="alasan" rows="2" required minlength="10" maxlength="1000" placeholder="Alasan mengeluarkan (wajib, min. 10 karakter)" class="border p-1 rounded text-xs w-full"></textarea>
                                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Keluarkan</button>
                                        </form>
                                    @endif
                                </div>
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
    <p class="text-xs text-gray-500 mt-3">Setujui atau tolak pendaftar di halaman <a href="{{ route('pendaftaran.index') }}" class="underline">Data Pendaftaran</a>. Tahun "Selesai" hanya catatan periode. Untuk mengeluarkan anggota, pakai tombol <strong>Keluarkan</strong> di menu Atur.</p>
@endif