@php
    $badgeStatus = ['menunggu' => 'pending', 'disetujui' => 'diterima', 'ditolak' => 'ditolak'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengunduran Diri</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-5xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Pengunduran Diri</h1>
            <p class="text-gray-600 text-sm">Setujui atau tolak pengajuan keluar dari ekskul.
                @if(auth()->user()->isAdmin()) Pengajuan pembina hanya bisa diputuskan admin. @endif</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Tugas admin: ekskul tanpa pembina --}}
        @if(auth()->user()->isAdmin() && $tanpaPembina->isNotEmpty())
            <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-6" data-aos="fade-up">
                <h2 class="font-bold text-red-800">Ekskul yang butuh pembina pengganti ({{ $tanpaPembina->count() }})</h2>
                <p class="text-xs text-red-700 mb-3">Ekskul ini tidak punya pembina yang bertugas. Tugaskan pembina lewat tombol di samping nama ekskul.</p>
                <div class="space-y-2">
                    @foreach($tanpaPembina as $e)
                        <div class="flex items-center justify-between gap-3 bg-white border border-red-100 rounded-lg px-4 py-2">
                            <span class="font-semibold text-gray-800">{{ $e->nama_ekstrakurikuler }}</span>
                            <a href="{{ route('ekstrakurikuler.edit', $e->id) }}" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-indigo-700">Tugaskan pembina</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Menunggu keputusan --}}
        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Menunggu keputusanmu ({{ $menunggu->count() }})</h2>
        <div class="space-y-4 mb-8">
            @forelse($menunggu as $r)
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <div>
                            <p class="font-bold text-gray-900">{{ $r->user->name ?? 'Pengguna dihapus' }}
                                <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $r->peran === 'pembina' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($r->peran) }}</span>
                            </p>
                            <p class="text-sm text-gray-600">ingin keluar dari
                                <a href="{{ route('ekstrakurikuler.show', $r->ekstrakurikuler_id) }}" class="font-semibold hover:underline">{{ $r->ekstrakurikuler->nama_ekstrakurikuler ?? '-' }}</a>
                            </p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $r->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                    <div class="mt-3 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-800 whitespace-pre-line">{{ $r->alasan }}</div>

                    <form action="{{ route('pengunduran.putuskan', $r->id) }}" method="POST" class="mt-3 space-y-2">
                        @csrf
                        @method('PATCH')
                        <textarea name="catatan" rows="2" placeholder="Catatan (wajib diisi kalau menolak)" class="w-full border p-2 rounded-lg text-sm"></textarea>
                        <div class="flex gap-2">
                            <button type="submit" name="keputusan" value="setuju" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700"
                                    onclick="return confirm('Setujui pengunduran diri ini? Statusnya langsung berubah jadi keluar.')">Setujui</button>
                            <button type="submit" name="keputusan" value="tolak" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700">Tolak</button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Tidak ada pengajuan yang menunggu keputusanmu.</div>
            @endforelse
        </div>

        {{-- Riwayat --}}
        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Riwayat keputusan</h2>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto" data-aos="fade-up">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3">Siapa</th>
                        <th class="p-3">Ekskul</th>
                        <th class="p-3">Jenis</th>
                        <th class="p-3">Alasan</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Diputuskan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $r)
                        <tr class="border-t align-top">
                            <td class="p-3 font-semibold">{{ $r->user->name ?? 'Pengguna dihapus' }}<br><span class="text-xs font-normal text-gray-500">{{ ucfirst($r->peran) }}</span></td>
                            <td class="p-3">{{ $r->ekstrakurikuler->nama_ekstrakurikuler ?? '-' }}</td>
                            <td class="p-3">{{ $r->jenis_label }}</td>
                            <td class="p-3 max-w-xs whitespace-pre-line">{{ $r->alasan }}@if($r->catatan_keputusan)<br><span class="text-xs text-gray-500">Catatan: {{ $r->catatan_keputusan }}</span>@endif</td>
                            <td class="p-3">@include('partials.status-badge', ['status' => $badgeStatus[$r->status], 'label' => ucfirst($r->status)])</td>
                            <td class="p-3 text-xs text-gray-500">{{ $r->pemutus->name ?? '-' }}<br>{{ optional($r->diputuskan_pada)->format('d-m-Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500">Belum ada riwayat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>