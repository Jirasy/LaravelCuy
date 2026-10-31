<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="mb-6" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Tambah User</h1>
            <p class="text-gray-600 text-sm">Tambah satu akun langsung dari sini. Untuk banyak siswa sekaligus, pakai
                <a href="{{ route('import-siswa.create') }}" class="underline font-semibold text-indigo-700">Import siswa</a>.</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Pilih jenis akun --}}
        <div class="flex gap-2 mb-4" role="tablist">
            <a href="{{ route('users.create') }}" role="tab" aria-selected="{{ $tab === 'siswa' ? 'true' : 'false' }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold border {{ $tab === 'siswa' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' }}">🎓 Siswa</a>
            @if($bolehPembina)
                <a href="{{ route('users.create', ['tab' => 'pembina']) }}" role="tab" aria-selected="{{ $tab === 'pembina' ? 'true' : 'false' }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold border {{ $tab === 'pembina' ? 'bg-sky-600 text-white border-sky-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' }}">🧑‍🏫 Pembina</a>
            @endif
        </div>

        @if($tab === 'siswa')
            <form action="{{ route('users.store-siswa') }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4" data-aos="fade-up">
                @csrf
                <div>
                    <label for="nama" class="block text-sm font-semibold text-gray-700">Nama lengkap <span class="text-red-600">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" maxlength="255" required class="w-full border p-2 rounded-lg mt-1">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="kelas" class="block text-sm font-semibold text-gray-700">Kelas <span class="font-normal text-gray-400">(opsional)</span></label>
                        <input type="text" id="kelas" name="kelas" value="{{ old('kelas') }}" maxlength="50" list="daftar-kelas" placeholder="mis. XI PPLG" class="w-full border p-2 rounded-lg mt-1">
                        <datalist id="daftar-kelas">
                            @foreach($daftarKelas as $k)<option value="{{ $k }}">@endforeach
                        </datalist>
                        <p class="text-xs text-gray-500 mt-1">Tulis tingkat lalu jurusan, mis. <em>X</em> atau <em>XI PPLG</em>.</p>
                    </div>
                    <div>
                        <label for="nisn" class="block text-sm font-semibold text-gray-700">NISN <span class="font-normal text-gray-400">(boleh dikosongkan)</span></label>
                        <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}" inputmode="numeric" maxlength="20" class="w-full border p-2 rounded-lg mt-1">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan dulu kalau belum ada; bisa diisi lewat Edit user nanti.</p>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700">Email login <span class="font-normal text-gray-400">(opsional)</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255" class="w-full border p-2 rounded-lg mt-1">
                    <p class="text-xs text-gray-500 mt-1">Kalau kosong, dibuat otomatis dari nama, mis. <code>budi.santoso@{{ $domainDefault }}</code>.</p>
                </div>

                <details class="text-sm">
                    <summary class="cursor-pointer font-semibold text-gray-700">Opsi lanjutan</summary>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label for="domain_email" class="block text-xs font-semibold text-gray-600">Domain email otomatis</label>
                            <input type="text" id="domain_email" name="domain_email" value="{{ old('domain_email') }}" placeholder="{{ $domainDefault }}" class="w-full border p-2 rounded-lg text-sm">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="izinkan_nama_sama" value="1" {{ old('izinkan_nama_sama') ? 'checked' : '' }}>
                            Izinkan nama yang sama dengan siswa lain (siswa kembar nama)
                        </label>
                    </div>
                </details>

                <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-3 text-xs text-indigo-900">
                    🔑 Password dibuat <strong>acak</strong> otomatis dan wajib diganti siswa saat login pertama. Setelah disimpan, kamu diarahkan ke halaman
                    hasil untuk membuka password sementaranya (tersedia 7 hari, terenkripsi) dan mengunduhnya. Rekapnya juga tersimpan di Riwayat Pesan.
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-emerald-700">Simpan siswa</button>
                    <a href="{{ route('users.index') }}" class="px-5 py-2 rounded-lg text-gray-700 border border-gray-300 hover:bg-gray-100">Batal</a>
                </div>
            </form>
        @else
            <form action="{{ route('users.store-pembina') }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-4" data-aos="fade-up">
                @csrf
                <div>
                    <label for="nama" class="block text-sm font-semibold text-gray-700">Nama pembina <span class="text-red-600">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" maxlength="255" required class="w-full border p-2 rounded-lg mt-1">
                </div>
                <div>
                    <label for="email_pembina" class="block text-sm font-semibold text-gray-700">Email login <span class="text-red-600">*</span></label>
                    <input type="email" id="email_pembina" name="email_pembina" value="{{ old('email_pembina') }}" maxlength="255" required class="w-full border p-2 rounded-lg mt-1">
                </div>
                <div>
                    <label for="ekstrakurikuler_id" class="block text-sm font-semibold text-gray-700">Ekskul yang dibina <span class="font-normal text-gray-400">(opsional)</span></label>
                    <select id="ekstrakurikuler_id" name="ekstrakurikuler_id" class="w-full border p-2 rounded-lg mt-1">
                        <option value="">Belum ditugaskan ke ekskul</option>
                        @foreach($ekskuls as $e)
                            <option value="{{ $e->id }}" {{ (string) old('ekstrakurikuler_id') === (string) $e->id ? 'selected' : '' }}>{{ $e->nama_ekstrakurikuler }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Bisa ditugaskan belakangan lewat halaman ekskulnya.</p>
                </div>

                <div class="rounded-lg bg-sky-50 border border-sky-200 p-3 text-xs text-sky-900">
                    🔑 Password dibuat <strong>acak</strong> otomatis dan ditampilkan <strong>sekali</strong> setelah disimpan. Setelahnya password sementara bisa dibuka admin
                    di halaman <a href="{{ route('kredensial.index') }}" class="underline font-semibold">Kredensial</a> (terenkripsi, berlaku 7 hari, terhapus saat pembina mengganti passwordnya).
                    Riwayat Pesan admin hanya mencatat bahwa akun dibuat, tanpa password.
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-emerald-700">Simpan pembina</button>
                    <a href="{{ route('users.index') }}" class="px-5 py-2 rounded-lg text-gray-700 border border-gray-300 hover:bg-gray-100">Batal</a>
                </div>
            </form>
        @endif
    </div>
</body>
</html>
