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

        {{-- Aksi massal: muncul begitu ada akun yang dicentang. Checkbox di tabel terhubung ke form ini lewat atribut form="form-massal". --}}
        <form id="form-massal" action="{{ route('users.massal') }}" method="POST" style="display:none"
              class="sticky top-3 z-30 mb-3 flex-wrap items-center gap-2 rounded-xl border border-indigo-200 bg-white p-3 shadow-lg">
            @csrf
            <span class="mr-1 text-sm font-semibold text-indigo-700"><span id="jumlah-dipilih">0</span> akun dipilih</span>

            <button type="submit" name="aksi" value="aktifkan" class="rounded bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">Aktifkan</button>
            <button type="submit" name="aksi" value="nonaktifkan" class="rounded bg-orange-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-600">Nonaktifkan</button>

            <span class="flex items-center gap-1">
                <input type="text" name="kelas" id="kelas-massal" maxlength="50" placeholder="Kelas baru, mis. XI IPA 1" class="w-44 rounded border p-1.5 text-xs">
                <button type="submit" name="aksi" value="ubah_kelas" class="rounded bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-600">Ubah kelas</button>
            </span>

            @if(auth()->user()->isAdmin())
                <span class="flex items-center gap-1">
                    <select name="role" id="role-massal" class="rounded border p-1.5 text-xs">
                        <option value="">Role baru…</option>
                        <option value="siswa">Siswa</option>
                        <option value="pembina">Pembina</option>
                        <option value="admin">Admin</option>
                    </select>
                    <button type="submit" name="aksi" value="ubah_role" class="rounded bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-900">Ubah role</button>
                </span>
            @endif

            <button type="submit" name="aksi" value="hapus" class="rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Hapus</button>
            <button type="button" id="batal-pilih" class="ml-auto rounded px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100">Batal pilih</button>
        </form>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto" data-aos="fade-up">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3 w-10">
                            <input type="checkbox" id="pilih-semua" class="h-4 w-4 cursor-pointer" title="Pilih semua yang tampil" aria-label="Pilih semua akun yang tampil">
                        </th>
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
                        @php $bisaPilih = ! $saya && auth()->user()->can('kelola', $u); @endphp
                        <tr class="border-t align-middle {{ $u->is_active ? '' : 'opacity-60' }}"
                            data-cari="{{ mb_strtolower($u->name . ' ' . $u->email . ' ' . $u->kelas . ' ' . $u->nisn) }}">
                            <td class="p-3">
                                <input type="checkbox" name="ids[]" value="{{ $u->id }}" form="form-massal"
                                       class="cek-user h-4 w-4 {{ $bisaPilih ? 'cursor-pointer' : 'cursor-not-allowed opacity-40' }}"
                                       aria-label="Pilih {{ $u->name }}"
                                       title="{{ $saya ? 'Akunmu sendiri tidak bisa dipilih' : ($bisaPilih ? '' : 'Kamu tidak punya izin mengelola akun ini') }}"
                                       @disabled(! $bisaPilih)>
                            </td>
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
        (function () {
            var form = document.getElementById('form-massal');
            var semua = document.getElementById('pilih-semua');
            var jumlah = document.getElementById('jumlah-dipilih');
            var kelas = document.getElementById('kelas-massal');
            var role = document.getElementById('role-massal'); // hanya ada untuk admin
            var cek = Array.prototype.slice.call(document.querySelectorAll('.cek-user'));

            function bisaDipilih(c) {
                return !c.disabled && c.closest('tr').style.display !== 'none';
            }

            function segarkan() {
                var terpilih = cek.filter(function (c) { return c.checked; });
                var kandidat = cek.filter(bisaDipilih);

                cek.forEach(function (c) { c.closest('tr').classList.toggle('bg-indigo-50', c.checked); });
                jumlah.textContent = terpilih.length;
                form.style.display = terpilih.length ? 'flex' : 'none';
                semua.checked = kandidat.length > 0 && kandidat.every(function (c) { return c.checked; });
                semua.indeterminate = !semua.checked && kandidat.some(function (c) { return c.checked; });
            }

            // Pencarian: baris yang tersembunyi otomatis tidak ikut terpilih, supaya aksi massal tidak mengenai akun yang tak terlihat
            document.getElementById('cari-user').addEventListener('input', function () {
                var q = this.value.trim().toLowerCase();
                document.querySelectorAll('#tabel-user tr').forEach(function (tr) {
                    tr.style.display = tr.dataset.cari.indexOf(q) === -1 ? 'none' : '';
                });
                cek.forEach(function (c) {
                    if (c.closest('tr').style.display === 'none') { c.checked = false; }
                });
                segarkan();
            });

            semua.addEventListener('change', function () {
                cek.filter(bisaDipilih).forEach(function (c) { c.checked = semua.checked; });
                segarkan();
            });

            cek.forEach(function (c) { c.addEventListener('change', segarkan); });

            document.getElementById('batal-pilih').addEventListener('click', function () {
                cek.forEach(function (c) { c.checked = false; });
                segarkan();
            });

            // Enter di kolom kelas = "Ubah kelas", bukan tombol submit pertama (Aktifkan)
            kelas.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.querySelector('button[value="ubah_kelas"]').click();
                }
            });

            form.addEventListener('submit', function (e) {
                var aksi = e.submitter ? e.submitter.value : '';
                var n = jumlah.textContent;
                var pesan = {
                    nonaktifkan: 'Nonaktifkan ' + n + ' akun? Mereka tidak bisa login sampai diaktifkan lagi.',
                    hapus: 'Hapus ' + n + ' akun secara PERMANEN? Riwayat pendaftarannya tetap tersimpan tanpa akun. Tindakan ini tidak bisa dibatalkan.',
                    ubah_kelas: 'Ubah kelas ' + n + ' akun menjadi "' + kelas.value.trim() + '"?',
                    ubah_role: role ? 'Ubah role ' + n + ' akun menjadi ' + role.value + '?' : ''
                };

                if (aksi === 'ubah_kelas' && kelas.value.trim() === '') {
                    e.preventDefault();
                    alert('Isi kelas barunya dulu.');
                    kelas.focus();
                    return;
                }

                if (aksi === 'ubah_role' && (!role || role.value === '')) {
                    e.preventDefault();
                    alert('Pilih role barunya dulu.');
                    if (role) { role.focus(); }
                    return;
                }

                if (pesan[aksi] && !confirm(pesan[aksi])) {
                    e.preventDefault();
                }
            });

            segarkan();
        })();
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