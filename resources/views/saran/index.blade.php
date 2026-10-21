@php
    $badgeStatus = ['baru' => 'pending', 'dibaca' => 'pending', 'ditindaklanjuti' => 'diterima'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kritik &amp; Saran</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-3xl mx-auto">
        @include('partials.navbar')

        <div class="flex flex-wrap justify-between items-center gap-3 mb-6" data-aos="fade-down">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Kritik &amp; Saran</h1>
                <p class="text-gray-600 text-sm">Sampaikan masukanmu untuk sekolah atau ekskul tertentu.</p>
            </div>
            @if(auth()->user()->isStaff())
                <a href="{{ route('saran.masuk') }}" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800">Kotak masuk</a>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('saran.store') }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4 mb-8" data-aos="fade-up">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700">Ditujukan untuk</label>
                <select name="ekstrakurikuler_id" class="w-full border p-2 rounded-lg mt-1">
                    <option value="">Umum (untuk sekolah / admin)</option>
                    @foreach($ekskulOpsi as $e)
                        <option value="{{ $e->id }}" {{ (string) old('ekstrakurikuler_id', $terpilih) === (string) $e->id ? 'selected' : '' }}>{{ $e->nama_ekstrakurikuler }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-6 text-sm text-gray-800">
                <label class="flex items-center gap-2"><input type="radio" name="jenis" value="saran" {{ old('jenis', 'saran') === 'saran' ? 'checked' : '' }}> 💡 Saran</label>
                <label class="flex items-center gap-2"><input type="radio" name="jenis" value="kritik" {{ old('jenis') === 'kritik' ? 'checked' : '' }}> 📣 Kritik</label>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Isi</label>
                <textarea name="isi" rows="5" required minlength="10" maxlength="2000" placeholder="Tulis dengan sopan dan jelas, supaya mudah ditindaklanjuti." class="w-full border p-2 rounded-lg mt-1">{{ old('isi') }}</textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-800">
                    <input type="checkbox" name="anonim" value="1" {{ old('anonim') ? 'checked' : '' }}> Kirim sebagai anonim
                </label>
                <p class="text-xs text-gray-500 mt-1">Namamu tidak ditampilkan ke admin maupun pembina. Akunmu tetap tercatat di sistem hanya untuk membatasi penyalahgunaan, dan kamu tetap bisa melihat balasannya di sini.</p>
            </div>

            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Kirim</button>
        </form>

        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Kirimanmu</h2>
        <div class="space-y-3">
            @forelse($saya as $s)
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200" data-aos="fade-up">
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <p class="text-xs text-gray-500">
                            {{ $s->jenis === 'kritik' ? '📣 Kritik' : '💡 Saran' }}
                            &middot; {{ $s->ekstrakurikuler->nama_ekstrakurikuler ?? 'Umum' }}
                            &middot; {{ $s->created_at->format('d-m-Y H:i') }}
                            @if($s->anonim) &middot; anonim @endif
                        </p>
                        @include('partials.status-badge', ['status' => $badgeStatus[$s->status], 'label' => \App\Models\EkskulSaran::STATUS[$s->status]])
                    </div>
                    <p class="text-sm text-gray-800 mt-2 whitespace-pre-line">{{ $s->isi }}</p>
                    @if($s->balasan)
                        <div class="mt-3 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-800 uppercase mb-1">Balasan</p>
                            <p class="text-sm text-indigo-900 whitespace-pre-line">{{ $s->balasan }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">Kamu belum mengirim kritik atau saran.</div>
            @endforelse
        </div>
    </div>
</body>
</html>