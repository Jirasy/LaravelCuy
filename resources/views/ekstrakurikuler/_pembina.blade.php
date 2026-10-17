@php $adalahAdmin = auth()->user()->isAdmin(); @endphp
<div class="max-w-2xl mx-auto mt-6 bg-white p-8 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
    <h2 class="text-lg font-bold text-gray-800 mb-1">Pembina Ekskul</h2>
    <p class="text-xs text-gray-500 mb-4">
        Hanya pembina yang ditugaskan di sini (dan admin) yang bisa mengelola isi ekskul ini.
        @unless($adalahAdmin) Penugasan pembina hanya bisa diubah admin. @endunless
    </p>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-lg mb-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-lg mb-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="space-y-2 mb-4">
        @forelse($ekstrakurikuler->pembinaAktif as $p)
            <div class="flex items-center justify-between gap-3 border rounded-lg px-3 py-2">
                <div class="flex items-center gap-3">
                    @include('partials.avatar', ['user' => $p, 'ukuran' => 32])
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $p->name }}</p>
                        <p class="text-xs text-gray-500">Sejak {{ \Carbon\Carbon::parse($p->pivot->mulai)->format('d-m-Y') }}</p>
                    </div>
                </div>
                @if($adalahAdmin)
                    <form action="{{ route('ekskul-pembina.destroy', [$ekstrakurikuler->id, $p->id]) }}" method="POST"
                          onsubmit="return confirm('Lepas {{ e($p->name) }} dari ekskul ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-red-700">Lepas</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-3 py-2 text-sm">
                Ekskul ini belum punya pembina yang ditugaskan.
            </div>
        @endforelse
    </div>

    @if($adalahAdmin)
        <form action="{{ route('ekskul-pembina.store', $ekstrakurikuler->id) }}" method="POST" class="flex gap-2">
            @csrf
            <select name="user_id" required class="flex-1 border p-2 rounded-lg text-sm">
                <option value="">-- Pilih pembina --</option>
                @foreach($calonPembina as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                @endforeach
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Tugaskan</button>
        </form>
        @if($calonPembina->isEmpty())
            <p class="text-xs text-gray-500 mt-2">Belum ada user ber-role pembina yang tersedia. Ubah role di Kelola User terlebih dahulu.</p>
        @endif
    @endif
</div>