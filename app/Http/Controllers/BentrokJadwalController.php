<?php

namespace App\Http\Controllers;

use App\Enums\Hari;
use App\Models\BentrokJadwalPilihan;
use App\Services\BentrokJadwalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Siswa memilih cara menyikapi jadwal ekskul yang bentrok: hadir di salah satu, atau membagi waktu.
class BentrokJadwalController extends Controller
{
    public function __construct(private BentrokJadwalService $bentrok) {}

    public function simpan(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'hari' => ['required', Rule::in(array_map(fn (Hari $h) => $h->value, Hari::cases()))],
            'ekskul_a_id' => 'required|integer',
            'ekskul_b_id' => 'required|integer|different:ekskul_a_id',
            'pilihan' => ['required', Rule::in(BentrokJadwalPilihan::PILIHAN)],
            'catatan' => 'nullable|string|max:200|required_if:pilihan,bagi_waktu',
        ], [
            'catatan.required_if' => 'Tulis singkat bagaimana kamu membagi waktunya.',
            'catatan.max' => 'Catatan maksimal 200 karakter.',
        ]);

        // Urutan pasangan dinormalkan: ekskul "a" selalu yang id-nya lebih kecil
        if ($data['ekskul_a_id'] > $data['ekskul_b_id']) {
            [$data['ekskul_a_id'], $data['ekskul_b_id']] = [$data['ekskul_b_id'], $data['ekskul_a_id']];
            $data['pilihan'] = match ($data['pilihan']) {
                'ekskul_a' => 'ekskul_b',
                'ekskul_b' => 'ekskul_a',
                default => $data['pilihan'],
            };
        }

        // Hanya pasangan yang benar-benar sedang bentrok di jadwal milik user ini yang boleh disimpan
        $sedangBentrok = $this->bentrok->bentrokUntukUser($user)->contains(
            fn (array $b) => $b['hari'] === $data['hari']
                && $b['a']['ekskul_id'] === (int) $data['ekskul_a_id']
                && $b['b']['ekskul_id'] === (int) $data['ekskul_b_id']
        );

        abort_unless($sedangBentrok, 422, 'Jadwal ini tidak sedang bentrok untukmu.');

        BentrokJadwalPilihan::updateOrCreate(
            [
                'user_id' => $user->id,
                'hari' => $data['hari'],
                'ekskul_a_id' => $data['ekskul_a_id'],
                'ekskul_b_id' => $data['ekskul_b_id'],
            ],
            [
                'pilihan' => $data['pilihan'],
                'catatan' => $data['pilihan'] === 'bagi_waktu' ? trim((string) $data['catatan']) : null,
            ]
        );

        return redirect()
            ->route('profil.show', ['user' => $user->id, 'hari' => $data['hari']])
            ->with('success', 'Pilihan untuk jadwal yang bentrok sudah disimpan.');
    }

    public function hapus(Request $request, BentrokJadwalPilihan $pilihan): RedirectResponse
    {
        abort_unless($pilihan->user_id === $request->user()->id, 403);

        $hari = $pilihan->hari;
        $pilihan->delete();

        return redirect()
            ->route('profil.show', ['user' => $request->user()->id, 'hari' => $hari])
            ->with('success', 'Pilihan dihapus. Kamu bisa memilih lagi kapan saja.');
    }
}
