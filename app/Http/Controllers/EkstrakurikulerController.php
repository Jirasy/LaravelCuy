<?php

namespace App\Http\Controllers;

use App\Enums\Hari;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $ekskuls = Ekstrakurikuler::with('haris')->latest()->get();
        return view('ekstrakurikuler.index', compact('ekskuls'));
    }

    public function create()
    {
        return view('ekstrakurikuler.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request, false), $this->messages());
        $data['status'] = $data['status'] ?? 'aktif';

        DB::transaction(function () use ($data) {
            $ekskul = Ekstrakurikuler::create(
                Arr::only($data, ['nama_ekstrakurikuler', 'pembina', 'deskripsi', 'status'])
            );
            $ekskul->syncJadwal($this->susunJadwal($data));
        });

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Ekstrakurikuler berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $ekstrakurikuler = Ekstrakurikuler::with('haris')->findOrFail($id);
        return view('ekstrakurikuler.edit', compact('ekstrakurikuler'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate($this->rules($request, true), $this->messages());
        $ekskul = Ekstrakurikuler::findOrFail($id);

        DB::transaction(function () use ($ekskul, $data) {
            $ekskul->update(
                Arr::only($data, ['nama_ekstrakurikuler', 'pembina', 'deskripsi', 'status'])
            );
            $ekskul->syncJadwal($this->susunJadwal($data));
        });

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Data ekstrakurikuler berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Ekstrakurikuler::findOrFail($id)->delete(); // jadwal ikut terhapus (cascade)

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Ekstrakurikuler berhasil dihapus!');
    }

    // Ubah input form (hari[] + jadwal[hari][jam_*]) menjadi daftar jadwal
    private function susunJadwal(array $data): array
    {
        return collect($data['hari'])
            ->unique()
            ->map(fn ($h) => [
                'hari'        => $h,
                'jam_mulai'   => $data['jadwal'][$h]['jam_mulai'],
                'jam_selesai' => $data['jadwal'][$h]['jam_selesai'],
            ])
            ->values()
            ->all();
    }

    private function rules(Request $request, bool $statusWajib): array
    {
        $valid = array_map(fn ($c) => $c->value, Hari::cases());

        $rules = [
            'nama_ekstrakurikuler' => 'required|string|max:255',
            'pembina'   => 'required|string|max:255',
            'hari'      => 'required|array|min:1',
            'hari.*'    => ['required', Rule::in($valid)],
            'deskripsi' => 'nullable|string',
            'status'    => ($statusWajib ? 'required' : 'nullable') . '|in:aktif,nonaktif',
        ];

        // Jam wajib diisi untuk SETIAP hari yang dipilih
        $dipilih = array_intersect(array_filter((array) $request->input('hari', []), 'is_string'), $valid);

        foreach ($dipilih as $h) {
            $rules["jadwal.$h.jam_mulai"]   = 'required|date_format:H:i,H:i:s';
            $rules["jadwal.$h.jam_selesai"] = "required|date_format:H:i,H:i:s|after:jadwal.$h.jam_mulai";
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'hari.required' => 'Pilih minimal satu hari pelaksanaan.',
            'hari.min'      => 'Pilih minimal satu hari pelaksanaan.',
            'jadwal.*.jam_mulai.required'   => 'Jam mulai wajib diisi untuk setiap hari yang dipilih.',
            'jadwal.*.jam_selesai.required' => 'Jam selesai wajib diisi untuk setiap hari yang dipilih.',
            'jadwal.*.jam_mulai.date_format'   => 'Format jam mulai tidak valid.',
            'jadwal.*.jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jadwal.*.jam_selesai.after'       => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}