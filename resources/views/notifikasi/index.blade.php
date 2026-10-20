<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-3xl mx-auto">
        @include('partials.navbar')

        <div class="flex flex-wrap justify-between items-center gap-3 mb-6" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Notifikasi</h1>
                <p class="text-gray-600 text-sm">Pemberitahuan terbaru untukmu.</p>
            </div>
            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form action="{{ route('notifikasi.bacaSemua') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="space-y-2">
            @forelse($notifikasi as $n)
                <a href="{{ route('notifikasi.buka', $n->id) }}"
                   class="block bg-white px-5 py-4 rounded-xl border shadow-sm hover:shadow-md transition {{ $n->read_at ? 'border-gray-200 opacity-75' : 'border-indigo-300' }}">
                    <div class="flex gap-3">
                        <span class="text-2xl">{{ $n->data['ikon'] ?? '🔔' }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900">
                                {{ $n->data['judul'] ?? 'Pemberitahuan' }}
                                @unless($n->read_at)<span class="ml-1 inline-block w-2 h-2 rounded-full bg-indigo-500 align-middle"></span>@endunless
                            </p>
                            <p class="text-sm text-gray-700 mt-0.5 whitespace-pre-line">{{ $n->data['pesan'] ?? '' }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->format('d-m-Y H:i') }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada notifikasi.</div>
            @endforelse
        </div>

        <div class="mt-4">{{ $notifikasi->links() }}</div>
    </div>
</body>
</html>