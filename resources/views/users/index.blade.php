<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-6xl mx-auto">
        @include('partials.navbar')

        {{-- Banner --}}
        <div class="relative overflow-hidden rounded-2xl mb-6 shadow-lg hero-glass" data-aos="fade-down">
            <div class="relative px-8 py-8 flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Kelola User</h1>
                    <p class="opacity-80 text-sm mt-1">Atur role, aktifkan atau nonaktifkan akun, dan edit data pengguna.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('import-siswa.create') }}" class="bg-white/90 text-indigo-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-white">📥 Import siswa</a>
                    <input type="search" id="cari-user" placeholder="Cari nama, email, kelas..." class="field-glass !w-64 !mt-0">
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @php
                $stat = [
                    ['Total user',      $users->count(),                                          '👥'],
                    ['Admin & Pembina', $users->whereIn('role', ['admin', 'pembina'])->count(),  '🛡️'],
                    ['Siswa',           $users->where('role', 'siswa')->count(),                  '🎓'],
                    ['Nonaktif',        $users->where('is_active', false)->count(),               '⏸️'],
                ];
            @endphp
            @foreach($stat as $i => $s)
                <div data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                    <div class="card-hover h-full bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
                        <span class="text-2xl">{{ $s[2] }}</span>
                        <div>
                            <p class="text-2xl font-bold text-gray-800 leading-none">{{ $s[1] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $s[0] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto" data-aos="fade-up">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3">Pengguna</th>
                        <th class="p-3">Kelas / NISN</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-user">
                    @foreach($users as $u)
                        @php $saya = $u->id === auth()->id(); @endphp
                        <tr class="border-t align-middle {{ $u->is_active ? '' : 'opacity-60' }}"
                            data-cari="{{ mb_strtolower($u->name . ' ' . $u->email . ' ' . $u->kelas . ' ' . $u->nisn) }}">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    @include('partials.avatar', ['user' => $u, 'ukuran' => 40])
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $u->name }}@if($saya) <span class="text-xs text-gray-400">(kamu)</span>@endif</p>
                                        <p class="text-xs text-gray-500">{{ $u->email }}</p>
                                        @if($u->status_sekolah)<span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full {{ $u->is_alumni ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800' }}">🎓 {{ $u->status_sekolah }}</span>@endif
                                        @if($infoOsis['ada'] ?? false)
                                            @if(in_array($u->id, $infoOsis['pengurus'], true))
                                                <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800">🏛️ Pengurus OSIS</span>
                                            @elseif(isset($infoOsis['casis'][$u->id]))
                                                <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800">🗳️ CaSis: {{ \App\Models\CasisPendaftaran::STATUS[$infoOsis['casis'][$u->id]] }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">{{ $u->kelas ?? '-' }}<br><span class="text-xs text-gray-500">{{ $u->nisn }}</span></td>

                            <td class="p-3">
                                @cannot('ubahRole', $u)
                                    <span class="text-xs">{{ ucfirst($u->role) }}</span>
                                @else
                                    <form action="{{ route('users.updateRole', $u->id) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="border p-1 rounded text-xs">
                                            <option value="siswa"   {{ $u->role === 'siswa' ? 'selected' : '' }}>Siswa</option>
                                            <option value="pembina" {{ $u->role === 'pembina' ? 'selected' : '' }}>Pembina</option>
                                            <option value="admin"   {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded text-xs hover:bg-gray-900">Simpan</button>
                                    </form>
                                @endcannot
                            </td>

                            <td class="p-3">
                                @include('partials.status-badge', [
                                    'status' => $u->is_active ? 'diterima' : 'ditolak',
                                    'label'  => $u->is_active ? 'Aktif' : 'Nonaktif',
                                ])
                            </td>

                            <td class="p-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('riwayat.show', $u->id) }}" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-indigo-700">Riwayat</a>

                                    @if($u->is_active)
                                        <details class="w-full">
                                            <summary class="cursor-pointer inline-block bg-teal-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-teal-700 list-none">Beri task</summary>
                                            <div class="mt-2 w-64">
                                                @include('tugas._form', ['mode' => 'user', 'userTarget' => $u])
                                            </div>
                                        </details>
                                    @endif

                                    @if(($infoOsis['ada'] ?? false) && ($infoOsis['boleh'] ?? false) && $u->role === 'siswa' && $u->is_active)
                                        @if(!in_array($u->id, $infoOsis['pengurus'], true) && $infoOsis['periode'] && !isset($infoOsis['casis'][$u->id]))
                                            <form action="{{ route('casis.masukkan', $u->id) }}" method="POST" onsubmit="return confirm('Masukkan {{ e($u->name) }} ke jalur CaSis OSIS? Ia akan diminta melengkapi formulir pendaftaran.')">
                                                @csrf
                                                <button type="submit" class="bg-purple-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-purple-700">Masukkan ke jalur CaSis</button>
                                            </form>
                                        @endif
                                        @include('users._osis_susulan', ['u' => $u, 'infoOsis' => $infoOsis])
                                    @endif
                                    @can('kelola', $u)
                                        <a href="{{ route('users.edit', $u->id) }}" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-yellow-600">Edit</a>
                                    @endcan

                                    @can('kelola', $u)
                                        @unless($saya)
                                        <form action="{{ route('users.toggle', $u->id) }}" method="POST"
                                              onsubmit="return confirm('{{ $u->is_active ? 'Nonaktifkan akun ini? User tidak bisa login sampai diaktifkan lagi.' : 'Aktifkan kembali akun ini?' }}')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="{{ $u->is_active ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }} text-white px-3 py-1 rounded text-xs font-semibold">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST"
                                              onsubmit="return confirm('Hapus akun {{ e($u->name) }} secara permanen? Riwayat pendaftarannya tetap tersimpan tanpa akun.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Hapus</button>
                                        </form>
                                    @endunless
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('cari-user').addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            document.querySelectorAll('#tabel-user tr').forEach(function (tr) {
                tr.style.display = tr.dataset.cari.indexOf(q) === -1 ? 'none' : '';
            });
        });
    </script>
    @if(($infoOsis['ada'] ?? false) && ($infoOsis['boleh'] ?? false))
        <datalist id="angkatan-osis">
            @foreach($infoOsis['angkatan'] as $angkatan)
                <option value="{{ $angkatan }}"></option>
            @endforeach
        </datalist>
    @endif
</body>
</html>