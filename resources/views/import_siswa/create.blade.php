<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Siswa</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-2xl font-bold text-gray-800">Import Data Siswa</h1>
                <a href="{{ route('users.index') }}" class="text-gray-600 hover:underline text-sm">← Kelola User</a>
            </div>
            <p class="text-sm text-gray-600 mb-5">
                Unggah daftar nama siswa. Sistem membuatkan akun role <b>siswa</b> dengan password acak untuk tiap nama,
                lalu mengirim rekapnya ke notifikasimu.
            </p>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if(session('dilewati'))
                <div class="bg-amber-50 border border-amber-300 rounded-lg px-4 py-3 mb-4">
                    <p class="text-sm font-semibold text-amber-900 mb-1">Nama yang dilewati:</p>
                    <ul class="list-disc pl-5 text-sm text-amber-900 max-h-48 overflow-y-auto">
                        @foreach(session('dilewati') as $d)
                            <li>{{ $d['nama'] }} <span class="text-amber-700">— {{ $d['alasan'] }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('import-siswa.proses') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Berkas daftar siswa</label>
                    <input type="file" name="berkas" required
                           accept="{{ collect($ekstensi)->map(fn ($e) => '.'.$e)->implode(',') }}"
                           class="w-full border p-2 rounded-lg mt-1 bg-white text-sm">
                    <p class="text-xs text-gray-500 mt-1">
                        Format: {{ implode(', ', $ekstensi) }}. Maksimal 5 MB dan {{ $maks }} nama per unggahan.
                    </p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 space-y-1">
                    <p class="font-semibold">Cara sistem membaca berkas</p>
                    <ul class="list-disc pl-5 text-xs text-gray-600 space-y-0.5">
                        <li>Kolom yang dipakai: <b>Nama</b> (wajib), serta <b>NISN</b>, <b>Kelas</b>, <b>Email</b> kalau ada.
                            Kolom lain (mis. nomor kelompok CAMP) diabaikan.</li>
                        <li>Tanpa judul kolom, sistem menebak kolom nama. Untuk berkas teks/Word cukup satu nama per baris (boleh bernomor).</li>
                        <li>NISN dikosongkan bila tidak ada di berkas; bisa dilengkapi nanti lewat Kelola User.</li>
                        <li>Tiap siswa mendapat email login otomatis (mis. <code>budi.santoso@{{ $domainDefault }}</code>) kecuali berkas sudah memuat email,
                            dan wajib mengganti password saat login pertama.</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Domain email siswa <span class="font-normal text-gray-500">(opsional)</span></label>
                    <input type="text" name="domain_email" value="{{ old('domain_email') }}" placeholder="{{ $domainDefault }}"
                           class="w-full border p-2 rounded-lg mt-1 focus:ring focus:ring-indigo-200">
                </div>

                <label class="flex items-start gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="izinkan_nama_sama" value="1" class="mt-1" @checked(old('izinkan_nama_sama'))>
                    <span>Tetap buat akun walau nama sama dengan siswa yang sudah ada
                        <span class="block text-xs text-gray-500">Biarkan kosong untuk mencegah akun ganda kalau berkas yang sama terunggah dua kali.</span>
                    </span>
                </label>

                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">
                    Import sekarang
                </button>
            </form>
        </div>
    </div>
</body>
</html>
