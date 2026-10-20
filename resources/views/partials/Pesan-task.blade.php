@php
    $nav   = \App\Support\NavbarData::untuk(auth()->user());
    $total = $nav['jumlahNotif'] + $nav['jumlahTugas'];
@endphp
<div class="relative" id="pesan-task">
    <button type="button" class="theme-btn relative" aria-label="Pesan dan task" title="Pesan, task, dan tautan" onclick="bukaPesanTask(event)">
        📥
        @if($total > 0)
            <span class="absolute -top-1 -right-1 min-w-[1.1rem] h-[1.1rem] px-1 flex items-center justify-center text-[10px] font-bold rounded-full bg-red-600 text-white">{{ $total > 99 ? '99+' : $total }}</span>
        @endif
    </button>

    <div id="pesan-task-panel" class="hidden absolute right-0 mt-2 w-80 max-w-[90vw] bg-white border border-gray-200 rounded-xl shadow-xl z-50 text-sm text-left">
        <div class="flex border-b border-gray-200">
            <button type="button" data-pt-tab="notif" class="flex-1 px-3 py-2 font-semibold text-indigo-600 border-b-2 border-indigo-500">Notifikasi @if($nav['jumlahNotif'] > 0)({{ $nav['jumlahNotif'] }})@endif</button>
            <button type="button" data-pt-tab="task" class="flex-1 px-3 py-2 font-semibold text-gray-500 border-b-2 border-transparent">Task @if($nav['jumlahTugas'] > 0)({{ $nav['jumlahTugas'] }})@endif</button>
            <button type="button" data-pt-tab="tautan" class="flex-1 px-3 py-2 font-semibold text-gray-500 border-b-2 border-transparent">Tautan</button>
        </div>

        {{-- Notifikasi --}}
        <div data-pt-panel="notif" class="max-h-80 overflow-y-auto">
            @forelse($nav['notifikasi'] as $n)
                <a href="{{ route('notifikasi.buka', $n->id) }}" class="block px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                    <p class="font-semibold text-gray-800">{{ $n->data['ikon'] ?? '🔔' }} {{ $n->data['judul'] ?? 'Pemberitahuan' }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">{{ \Illuminate\Support\Str::limit($n->data['pesan'] ?? '', 90) }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $n->created_at->format('d-m H:i') }}</p>
                </a>
            @empty
                <p class="px-4 py-6 text-center text-gray-400">Tidak ada notifikasi baru.</p>
            @endforelse
            <div class="flex justify-between items-center px-4 py-2 bg-gray-50">
                <a href="{{ route('notifikasi.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Lihat semua</a>
                @if($nav['jumlahNotif'] > 0)
                    <form action="{{ route('notifikasi.bacaSemua') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs text-gray-500 hover:underline">Tandai semua dibaca</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Task --}}
        <div data-pt-panel="task" class="hidden max-h-80 overflow-y-auto">
            @forelse($nav['tugas'] as $p)
                <a href="{{ route('tugas.index') }}" class="block px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                    <p class="font-semibold text-gray-800">{{ $p->tugas->judul }}</p>
                    <p class="text-xs mt-0.5 {{ $p->terlambat ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                        @if($p->tugas->tenggat)
                            {{ $p->terlambat ? 'Lewat tenggat' : 'Tenggat' }} {{ $p->tugas->tenggat->format('d-m-Y') }}
                        @else
                            Tanpa tenggat
                        @endif
                    </p>
                </a>
            @empty
                <p class="px-4 py-6 text-center text-gray-400">Tidak ada task yang menunggu.</p>
            @endforelse
            <div class="flex justify-between items-center px-4 py-2 bg-gray-50">
                <a href="{{ route('tugas.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Buka semua task</a>
                <a href="{{ route('tugas.index') }}#buat" class="text-xs text-gray-600 hover:underline">+ Tambah task</a>
            </div>
        </div>

        {{-- Tautan --}}
        <div data-pt-panel="tautan" class="hidden max-h-80 overflow-y-auto p-3 space-y-2">
            @forelse($nav['tautan'] as $t)
                <a href="{{ $t->url }}" target="_blank" rel="noopener noreferrer" class="block px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 font-semibold text-gray-800">{{ $t->ikon }} {{ $t->label }}</a>
            @empty
                <p class="py-4 text-center text-gray-400">Belum ada tautan.</p>
            @endforelse
            @if(auth()->user()->isAdmin())
                <a href="{{ route('tautan.index') }}" class="block text-center text-xs text-indigo-600 font-semibold hover:underline pt-1">Kelola tautan sekolah</a>
            @endif
        </div>
    </div>
</div>
<script>
    function bukaPesanTask(e) {
        e.stopPropagation();
        document.getElementById('pesan-task-panel').classList.toggle('hidden');
    }
    (function () {
        var wadah = document.getElementById('pesan-task');
        var panel = document.getElementById('pesan-task-panel');

        wadah.querySelectorAll('[data-pt-tab]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                var nama = tombol.dataset.ptTab;
                wadah.querySelectorAll('[data-pt-panel]').forEach(function (p) { p.classList.toggle('hidden', p.dataset.ptPanel !== nama); });
                wadah.querySelectorAll('[data-pt-tab]').forEach(function (t) {
                    var aktif = t.dataset.ptTab === nama;
                    t.classList.toggle('text-indigo-600', aktif);
                    t.classList.toggle('border-indigo-500', aktif);
                    t.classList.toggle('text-gray-500', !aktif);
                    t.classList.toggle('border-transparent', !aktif);
                });
            });
        });

        document.addEventListener('click', function (e) { if (!wadah.contains(e.target)) panel.classList.add('hidden'); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') panel.classList.add('hidden'); });
    })();
</script>