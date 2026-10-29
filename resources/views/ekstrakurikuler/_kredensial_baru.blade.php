{{-- Tampil SEKALI setelah akun pembina dibuat otomatis (flash). Setelahnya dibuka lewat halaman Kredensial. --}}
@if(session('kredensial'))
    @php $kr = session('kredensial'); @endphp
    <div class="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-4 rounded-lg mb-4">
        <p class="font-semibold">🔑 Akun pembina dibuat: {{ $kr['nama'] }}</p>
        <p class="text-sm mt-1">Email: <code class="font-mono select-all">{{ $kr['email'] }}</code></p>
        <p class="text-sm">Password sementara: <code class="font-mono font-bold select-all">{{ $kr['password'] }}</code></p>
        <p class="text-xs mt-2">
            Pembina diminta mengganti password saat login pertama. Kalau lupa, buka lagi di
            <a href="{{ route('kredensial.index') }}" class="underline font-semibold">halaman Kredensial</a>.
        </p>
    </div>
@endif
