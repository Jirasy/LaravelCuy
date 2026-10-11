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
        $data = $request->validate($this->rules(false), $this->messages());
        $data['status'] = $data['status'] ?? 'aktif';

        DB::transaction(function () use ($data) {
            $ekskul = Ekstrakurikuler::create(Arr::except($data, 'hari'));
            $ekskul->syncHari($data['hari']);
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
        $data = $request->validate($this->rules(true), $this->messages());
        $ekskul = Ekstrakurikuler::findOrFail($id);

        DB::transaction(function () use ($ekskul, $data) {
            $ekskul->update(Arr::except($data, 'hari'));
            $ekskul->syncHari($data['hari']);
        });

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Data ekstrakurikuler berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Ekstrakurikuler::findOrFail($id)->delete(); // baris hari ikut terhapus (cascade)

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Ekstrakurikuler berhasil dihapus!');
    }

    private function rules(bool $statusWajib): array
    {
        return [
            'nama_ekstrakurikuler' => 'required|string|max:255',
            'pembina'     => 'required|string|max:255',
            'hari'        => 'required|array|min:1',
            'hari.*'      => ['required', Rule::in(array_map(fn ($c) => $c->value, Hari::cases()))],
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'deskripsi'   => 'nullable|string',
            'status'      => ($statusWajib ? 'required' : 'nullable') . '|in:aktif,nonaktif',
        ];
    }

    private function messages(): array
    {
        return [
            'hari.required' => 'Pilih minimal satu hari pelaksanaan.',
            'hari.min'      => 'Pilih minimal satu hari pelaksanaan.',
        ];
    }
}