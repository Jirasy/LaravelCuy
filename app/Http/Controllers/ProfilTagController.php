<?php

namespace App\Http\Controllers;

use App\Models\ProfilTag;
use App\Models\User;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Tag profil: user lain memberi tag, pemilik profil memutuskan tampil/privat/hapus.
// Admin & pembina bisa memoderasi (aturan lengkap ada di ProfilTagPolicy).
class ProfilTagController extends Controller
{
    public function store(Request $request, User $user)
    {
        Gate::authorize('beri', [ProfilTag::class, $user]);

        $this->rapikanTeks($request);

        $data = $request->validate([
            'teks' => array_merge(['required'], $this->aturanTeks()),
        ], $this->pesanTeks());

        $pemberi = $request->user();

        // Satu pemberi hanya boleh 1 tag per profil
        $sudahMemberi = ProfilTag::where('user_id', $user->id)->where('pemberi_id', $pemberi->id)->exists();
        if ($sudahMemberi) {
            return $this->kembali($user->id)->withErrors([
                'teks' => 'Kamu sudah memberi tag ke ' . $user->name . '. Tarik tag lamamu dulu kalau mau menggantinya.',
            ])->withInput();
        }

        ProfilTag::create([
            'user_id' => $user->id,
            'pemberi_id' => $pemberi->id,
            'teks' => $data['teks'],
            'status' => 'privat',
        ]);

        // Pemilik diberi tahu tanpa menyebut pemberinya
        Pemberitahuan::kirim(
            [$user],
            'Tag baru di profilmu',
            'Seseorang memberi tag "' . $data['teks'] . '" di profilmu. Tag ini privat: hanya kamu yang bisa melihatnya sampai kamu menampilkannya.',
            route('profil.show', [], false),
            '🏷️',
            $pemberi->id
        );

        return $this->kembali($user->id)->with('success', 'Tag terkirim. Tag ini privat sampai ' . $user->name . ' memutuskan menampilkannya.');
    }

    public function update(Request $request, ProfilTag $tag)
    {
        Gate::authorize('ubahStatus', $tag); // pemilik atau moderator; yang lain 403

        if ($request->has('teks')) {
            $this->rapikanTeks($request);
        }

        $data = $request->validate([
            'status' => ['required_without:teks', Rule::in(array_keys(ProfilTag::STATUS))],
            'teks' => array_merge(['required_without:status'], $this->aturanTeks()),
        ], $this->pesanTeks());

        if (isset($data['teks'])) {
            Gate::authorize('ubahTeks', $tag); // khusus moderator

            $tag->teks = $data['teks'];
        }

        if (isset($data['status'])) {
            $tag->status = $data['status'];
        }

        $tag->save();

        return $this->kembali($tag->user_id)->with('success', 'Tag diperbarui.');
    }

    public function destroy(ProfilTag $tag)
    {
        Gate::authorize('hapus', $tag);

        $idPemilik = $tag->user_id;
        $tag->delete();

        return $this->kembali($idPemilik)->with('success', 'Tag dihapus.');
    }

    private function kembali(int $idPemilik)
    {
        return redirect()->to(route('profil.show', ['user' => $idPemilik]) . '#tag-profil');
    }

    // Rapikan spasi berlebih sebelum divalidasi
    private function rapikanTeks(Request $request): void
    {
        $request->merge([
            'teks' => trim((string) preg_replace('/\s+/u', ' ', (string) $request->input('teks'))),
        ]);
    }

    private function aturanTeks(): array
    {
        return ['string', 'min:2', 'max:' . ProfilTag::MAKS_KARAKTER];
    }

    private function pesanTeks(): array
    {
        return [
            'teks.required' => 'Teks tag wajib diisi.',
            'teks.required_without' => 'Isi teks tag atau pilih status.',
            'teks.min' => 'Tag minimal 2 karakter.',
            'teks.max' => 'Tag maksimal ' . ProfilTag::MAKS_KARAKTER . ' karakter.',
            'status.required_without' => 'Pilih status tag.',
        ];
    }
}
