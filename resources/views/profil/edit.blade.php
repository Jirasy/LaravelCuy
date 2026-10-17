<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    @include('partials.head')
</head>
<body class="app-bg p-6">
    <div class="max-w-2xl mx-auto">
        @include('partials.navbar')

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit Profil</h1>
            <a href="{{ route('profil.show') }}" class="text-gray-600 hover:underline text-sm">← Profilku</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data"
              class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-5" data-aos="fade-up">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4">
                @include('partials.avatar', ['user' => $user, 'ukuran' => 72])
                <div class="text-sm">
                    <label class="block font-semibold text-gray-700 mb-1">Foto profil</label>
                    <input type="file" name="foto" accept="image/png,image/jpeg,image/webp" class="text-xs">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    @if($user->foto)
                        <button type="submit" form="form-hapus-foto" onclick="return confirm('Hapus foto profil?')" class="text-xs text-red-600 hover:underline mt-1">Hapus foto</button>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border p-2 rounded-lg mt-1">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Bio singkat <span class="font-normal text-gray-400">(terlihat oleh semua pengguna)</span></label>
                <textarea name="bio" id="bio" rows="2" maxlength="160" placeholder="Contoh: Sibuk latihan, balas chat agak lama" class="w-full border p-2 rounded-lg mt-1">{{ old('bio', $user->bio) }}</textarea>
                <p class="text-xs text-gray-400 mt-1"><span id="bio-hitung">0</span>/160</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Angkatan sekolah <span class="font-normal text-gray-400">(mis. 2025)</span></label>
                    <input type="text" name="angkatan_sekolah" value="{{ old('angkatan_sekolah', $user->angkatan_sekolah) }}" class="w-full border p-2 rounded-lg mt-1">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp_tampilan) }}" placeholder="081234567890" class="w-full border p-2 rounded-lg mt-1">
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Siapa yang boleh melihat nomor HP-mu?</p>
                <div class="space-y-2">
                    @foreach(\App\Models\User::NO_HP_TAMPIL as $nilai => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="radio" name="no_hp_tampil" value="{{ $nilai }}" {{ old('no_hp_tampil', $user->no_hp_tampil ?? 'staff') === $nilai ? 'checked' : '' }}>
                            {{ $label }}
                            @if($nilai === 'staff')<span class="text-xs text-emerald-600">(paling aman)</span>@endif
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">Nomor hanya dipakai agar orang lain bisa menghubungimu lewat WhatsApp. Jangan bagikan ke orang yang tidak kamu kenal.</p>
            </div>

            <div class="grid grid-cols-3 gap-4 text-sm border-t pt-4">
                <div><p class="text-xs text-gray-500">Email</p><p class="text-gray-800 break-all">{{ $user->email }}</p></div>
                <div><p class="text-xs text-gray-500">Kelas</p><p class="text-gray-800">{{ $user->kelas ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">Role</p><p class="text-gray-800">{{ ucfirst($user->role) }}</p></div>
            </div>
            <p class="text-xs text-gray-500">Email, kelas, dan role tidak bisa diubah di sini. Role hanya diatur lewat Kelola User.</p>

            <div class="flex justify-end gap-2">
                <a href="{{ route('profil.show') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">Batal</a>
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700">Simpan Profil</button>
            </div>
        </form>
    </div>

    <form id="form-hapus-foto" action="{{ route('profil.foto.hapus') }}" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        var bio = document.getElementById('bio'), hitung = document.getElementById('bio-hitung');
        function hitungBio() { hitung.textContent = bio.value.length; }
        bio.addEventListener('input', hitungBio);
        hitungBio();
    </script>
</body>
</html>