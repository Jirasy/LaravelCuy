<?php

namespace App\Http\Controllers;

use App\Models\EkskulTautan;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// Tautan ekskul: pembina ekskul itu / admin. Tautan sekolah (ekskul kosong): hanya admin.
class EkskulTautanController extends Controller
{
    // Halaman kelola tautan sekolah (admin)
    public function index()
    {
        $tautans = EkskulTautan::whereNull('ekstrakurikuler_id')->orderBy('urutan')->orderBy('id')->get();

        return view('tautan.index', compact('tautans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ekstrakurikuler_id' => 'nullable|integer|exists:ekstrakurikulers,id',
            'platform'           => ['required', Rule::in(array_keys(EkskulTautan::PLATFORM))],
            'label'              => 'nullable|string|max:60',
            'url'                => 'required|string|max:500|url|starts_with:https://',
        ], [
            'url.url'         => 'Alamat tautan tidak valid.',
            'url.starts_with' => 'Tautan harus diawali https://',
        ]);

        $this->izinkan($data['ekstrakurikuler_id'] ?? null, $request);
        $this->cocokkanDomain($data['platform'], $data['url']);

        EkskulTautan::create([
            'ekstrakurikuler_id' => $data['ekstrakurikuler_id'] ?? null,
            'platform'           => $data['platform'],
            'label'              => trim($data['label'] ?? '') ?: EkskulTautan::PLATFORM[$data['platform']][1],
            'url'                => $data['url'],
            'dibuat_oleh'        => $request->user()->id,
        ]);

        return back()->with('success', 'Tautan ditambahkan.');
    }

    public function destroy(Request $request, EkskulTautan $tautan)
    {
        $this->izinkan($tautan->ekstrakurikuler_id, $request);

        $tautan->delete();

        return back()->with('success', 'Tautan dihapus.');
    }

    private function izinkan(?int $ekskulId, Request $request): void
    {
        if ($ekskulId) {
            Gate::authorize('kelola', Ekstrakurikuler::findOrFail($ekskulId));
            return;
        }

        abort_unless($request->user()->isAdmin(), 403, 'Hanya admin yang boleh mengatur tautan sekolah.');
    }

    // Alamat harus benar-benar milik platform yang dipilih (mencegah tautan palsu)
    private function cocokkanDomain(string $platform, string $url): void
    {
        $domain = EkskulTautan::PLATFORM[$platform][2];

        if ($domain === null) {
            return;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        foreach ($domain as $d) {
            if ($host === $d || str_ends_with($host, '.' . $d)) {
                return;
            }
        }

        throw ValidationException::withMessages([
            'url' => 'Alamat tidak cocok dengan platform ' . EkskulTautan::PLATFORM[$platform][1] . '.',
        ]);
    }
}