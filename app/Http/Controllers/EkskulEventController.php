<?php

namespace App\Http\Controllers;

use App\Models\EkskulEvent;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Hanya admin & pembina (dijaga di route)
class EkskulEventController extends Controller
{
    public function store(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('kelola', $ekstrakurikuler);
        $ekstrakurikuler->events()->create($this->validasi($request));

        return $this->kembali($ekstrakurikuler->id, 'Event berhasil ditambahkan!');
    }

    public function edit(EkskulEvent $event)
    {
        Gate::authorize('kelola', $event->ekstrakurikuler);
        return view('ekskul_event.edit', compact('event'));
    }

    public function update(Request $request, EkskulEvent $event)
    {
        Gate::authorize('kelola', $event->ekstrakurikuler);
        $event->update($this->validasi($request));

        return $this->kembali($event->ekstrakurikuler_id, 'Event berhasil diperbarui!');
    }

    public function destroy(EkskulEvent $event)
    {
        Gate::authorize('kelola', $event->ekstrakurikuler);
        $ekskulId = $event->ekstrakurikuler_id;
        $event->delete();

        return $this->kembali($ekskulId, 'Event berhasil dihapus!');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'judul'           => 'required|string|max:255',
            'jenis'           => ['required', Rule::in(array_keys(EkskulEvent::JENIS))],
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lokasi'          => 'nullable|string|max:255',
            'deskripsi'       => 'nullable|string',
        ]);
    }

    private function kembali(int $ekskulId, string $pesan)
    {
        return redirect()
            ->route('ekstrakurikuler.show', ['id' => $ekskulId, 'tab' => 'event'])
            ->with('success', $pesan);
    }
}