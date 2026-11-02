{{-- Input kontak & sosmed ekskul (opsional). Dipakai create & edit; hanya terlihat oleh anggota aktif, admin, dan pembina. --}}
@php $ekskulIni = $ekstrakurikuler ?? null; @endphp
<div class="rounded-lg border border-gray-200 p-4 space-y-3">
    <div>
        <p class="text-sm font-semibold text-gray-700">Kontak &amp; Sosial Media <span class="font-normal text-gray-500">(opsional)</span></p>
        <p class="text-xs text-gray-500">Hanya terlihat oleh anggota aktif ekskul ini, admin, dan pembina. Siswa yang belum menjadi anggota tidak melihatnya.</p>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600">Link Grup WhatsApp</label>
        <input type="url" name="wa_grup_url" value="{{ old('wa_grup_url', $ekskulIni?->wa_grup_url) }}" maxlength="500" placeholder="https://chat.whatsapp.com/..." class="w-full border p-2 rounded-lg mt-1 text-sm">
    </div>

    @if(empty($tanpaPembina))
    <div>
        <label class="block text-xs font-semibold text-gray-600">Nomor WhatsApp Pembina (chat langsung)</label>
        <input type="text" name="wa_pembina" value="{{ old('wa_pembina', $ekskulIni?->wa_pembina_tampilan) }}" maxlength="25" placeholder="081234567890" class="w-full border p-2 rounded-lg mt-1 text-sm">
        <p class="text-xs text-gray-500 mt-1">Nomor ini sengaja kamu bagikan ke anggota. Nomor di profil pribadi pembina tidak ikut tampil.</p>
    </div>
    @endif

    <div>
        <label class="block text-xs font-semibold text-gray-600">Link Telegram</label>
        <input type="url" name="telegram_url" value="{{ old('telegram_url', $ekskulIni?->telegram_url) }}" maxlength="500" placeholder="https://t.me/..." class="w-full border p-2 rounded-lg mt-1 text-sm">
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600">Instagram</label>
        <input type="url" name="instagram_url" value="{{ old('instagram_url', $ekskulIni?->instagram_url) }}" maxlength="500" placeholder="https://instagram.com/..." class="w-full border p-2 rounded-lg mt-1 text-sm">
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600">TikTok</label>
        <input type="url" name="tiktok_url" value="{{ old('tiktok_url', $ekskulIni?->tiktok_url) }}" maxlength="500" placeholder="https://tiktok.com/@..." class="w-full border p-2 rounded-lg mt-1 text-sm">
    </div>

    <p class="text-xs text-gray-500">Alamat harus diawali https:// dan sesuai platformnya. Kosongkan kolom yang tidak dipakai.</p>
</div>
