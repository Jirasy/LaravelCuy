<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kotak Masuk Kritik &amp; Saran</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-4xl mx-auto">
        @include('partials.navbar')

        <div class="flex flex-wrap justify-between items-center gap-3 mb-6" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Kotak Masuk</h1>
                <p class="text-gray-600 text-sm">
                    Kritik dan saran dari siswa.
                    @if(auth()->user()->isAdmin()) Kamu melihat semuanya, termasuk yang umum untuk sekolah.
                    @else Kamu melihat yang ditujukan ke ekskul yang kamu bina. @endif
                </p>
            </div>
            <a href="{{ route('saran.index') }}" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800">Kirim kritik/saran</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="flex flex-wrap gap-2 mb-4">
            <a href="{{ route('saran.masuk') }}" class="px-3 py-1.5 rounded-full text-sm font-semibold border {{ !$status ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200' }}">Semua</a>
            @foreach(\App\Models\EkskulSaran::STATUS as $kode => $label)
                <a href="{{ route('saran.masuk', ['status' => $kode]) }}" class="px-3 py-1.5 rounded-full text-sm font-semibold border {{ $status === $kode ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200' }}">
                    {{ $label }}@if($kode === 'baru' && $jumlah > 0) ({{ $jumlah }})@endif
                </a>
            @endforeach
        </div>

        <div class="space-y-3">
            @forelse($daftar as $s)
                <div class="bg-white p-5 rounded-xl shadow-sm border {{ $s->status === 'baru' ? 'border-indigo-300' : 'border-gray-200' }}" data-aos="fade-up">
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $s->jenis === 'kritik' ? '📣 Kritik' : '💡 Saran' }}
                                <span class="font-normal text-gray-500">dari</span> {{ $s->nama_pengirim }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $s->ekstrakurikuler->nama_ekstrakurikuler ?? 'Umum (sekolah)' }} &middot; {{ $s->created_at->format('d-m-Y H:i') }}
                            </p>
                        </div>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $s->status === 'baru' ? 'bg-yellow-100 text-yellow-800' : ($s->status === 'dibaca' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                            {{ \App\Models\EkskulSaran::STATUS[$s->status] }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-800 mt-3 whitespace-pre-line">{{ $s->isi }}</p>

                    <form action="{{ route('saran.tanggapi', $s->id) }}" method="POST" class="mt-3 grid grid-cols-1 sm:grid-cols-4 gap-2 items-start">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-xs text-gray-500">Status</label>
                            <select name="status" class="w-full border p-2 rounded-lg text-sm">
                                @foreach(\App\Models\EkskulSaran::STATUS as $kode => $label)
                                    <option value="{{ $kode }}" {{ $s->status === $kode ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-gray-500">Balasan <span class="text-gray-400">(dikirim ke pengirim)</span></label>
                            <textarea name="balasan" rows="2" maxlength="2000" class="w-full border p-2 rounded-lg text-sm">{{ $s->balasan }}</textarea>
                        </div>
                        <div class="sm:pt-5">
                            <button type="submit" class="w-full bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Belum ada kritik atau saran.</div>
            @endforelse
        </div>
    </div>
</body>
</html>