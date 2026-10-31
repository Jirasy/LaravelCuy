{{--
  Kartu tag profil di halaman profil. Variabel:
  $user (pemilik profil), $tags (ProfilTag yang boleh dilihat viewer), $isSelf, $bisaModerasi, $bisaBeriTag

  - Pemilik / moderator (admin & pembina): daftar lengkap + tombol tampilkan/sembunyikan dan hapus.
    Pemberi tag hanya dimuat dan ditampilkan untuk moderator; pemilik biasa tidak melihat siapa pemberinya.
  - Pengunjung lain: hanya tag yang ia beri sendiri (bisa ditarik) + form beri tag.
  - Tag berstatus "tampil" digambar di kartu profil (partials/kartu-profil), bukan di sini.
--}}
@php
    $kelolaPenuh = $isSelf || $bisaModerasi;
    $tagKu = $kelolaPenuh ? collect() : $tags->where('pemberi_id', auth()->id());
    $sudahBeri = $tags->where('pemberi_id', auth()->id())->isNotEmpty(); // aturan: 1 tag per pemberi per profil
    $pil = 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border';
@endphp
<div id="tag-profil" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6 text-sm" data-aos="fade-up">
    <p class="text-xs font-semibold text-gray-500 uppercase mb-3">
        Tag profil
        @if($isSelf)<span class="normal-case font-normal text-gray-400">(tag dari pengguna lain bersifat privat sampai kamu menampilkannya)</span>@endif
        @if($bisaModerasi && ! $isSelf)<span class="normal-case font-normal text-gray-400">(moderasi admin/pembina)</span>@endif
    </p>

    @if($kelolaPenuh)
        @if($tags->isEmpty())
            <p class="text-gray-500">Belum ada tag.</p>
        @else
            <div class="space-y-2">
                @foreach($tags as $t)
                    <div class="flex flex-wrap items-center justify-between gap-2 border border-gray-200 rounded-lg px-3 py-2">
                        <div class="min-w-0">
                            <span class="{{ $pil }} {{ $t->isTampil() ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ $t->isTampil() ? '👁️' : '🔒' }} {{ $t->teks }}
                            </span>
                            <span class="ml-1 text-xs text-gray-400">{{ \App\Models\ProfilTag::STATUS[$t->status] ?? $t->status }}</span>
                            @if($bisaModerasi)
                                <span class="ml-1 text-xs text-gray-500">· dari {{ $t->pemberi?->name ?? 'akun yang sudah dihapus' }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            <form action="{{ route('profil.tag.update', $t->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $t->isTampil() ? 'privat' : 'tampil' }}">
                                <button type="submit" class="font-semibold text-indigo-600 hover:underline">{{ $t->isTampil() ? 'Sembunyikan' : 'Tampilkan' }}</button>
                            </form>

                            @if($bisaModerasi)
                                <details class="relative">
                                    <summary class="font-semibold text-amber-600 hover:underline cursor-pointer select-none">Edit</summary>
                                    <form action="{{ route('profil.tag.update', $t->id) }}" method="POST" class="mt-2 flex gap-2">
                                        @csrf @method('PATCH')
                                        <input type="text" name="teks" value="{{ $t->teks }}" maxlength="30" required class="border p-1.5 rounded-lg text-sm">
                                        <button type="submit" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg font-semibold hover:bg-emerald-700">Simpan</button>
                                    </form>
                                </details>
                            @endif

                            <form action="{{ route('profil.tag.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus tag ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="font-semibold text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @elseif($tagKu->isNotEmpty())
        <p class="text-gray-500 mb-2">Tag yang kamu beri ke {{ $user->name }}:</p>
        <div class="space-y-2">
            @foreach($tagKu as $t)
                <div class="flex flex-wrap items-center justify-between gap-2 border border-gray-200 rounded-lg px-3 py-2">
                    <div>
                        <span class="{{ $pil }} bg-gray-100 text-gray-600 border-gray-200">{{ $t->teks }}</span>
                        <span class="ml-1 text-xs text-gray-400">{{ $t->isTampil() ? 'ditampilkan di profil' : 'masih privat' }}</span>
                    </div>
                    <form action="{{ route('profil.tag.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Tarik tag ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Tarik</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    @if($bisaBeriTag && $sudahBeri)
        <p class="text-xs text-gray-500 {{ $kelolaPenuh || $tagKu->isNotEmpty() ? 'mt-4' : '' }}">Kamu sudah memberi tag ke {{ $user->name }}. Tarik tag lamamu dulu kalau mau menggantinya.</p>
    @elseif($bisaBeriTag)
        <form action="{{ route('profil.tag.store', $user->id) }}" method="POST" class="{{ $kelolaPenuh || $tagKu->isNotEmpty() ? 'mt-4' : '' }} flex flex-wrap gap-2">
            @csrf
            <input type="text" name="teks" value="{{ old('teks') }}" maxlength="30" required placeholder="Beri tag, contoh: si rajin"
                   class="flex-1 min-w-[10rem] border p-2 rounded-lg">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700">Beri tag</button>
        </form>
        <p class="text-xs text-gray-500 mt-2">Tag awalnya privat: hanya {{ $user->name }} (dan admin/pembina) yang bisa melihatnya, dan {{ $user->name }} yang memutuskan mau menampilkannya. Pakai kata yang baik ya.</p>
    @endif
</div>
