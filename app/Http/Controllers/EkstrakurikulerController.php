<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $ekskuls = Ekstrakurikuler::latest()->get();
        return view('ekstrakurikuler.index', compact('ekskuls'));
    }

    public function create()
    {
        return view('ekstrakurikuler.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_ekstrakurikuler' => 'required|string|max:255',
            'pembina'     => 'required|string|max:255',
            'hari'        => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'deskripsi'   => 'nullable|string',
            'status'      => 'nullable|in:aktif,nonaktif',
        ]);

        $data['status'] = $data['status'] ?? 'aktif';

        Ekstrakurikuler::create($data);

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Ekstrakurikuler berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $ekstrakurikuler = Ekstrakurikuler::findOrFail($id);
        return view('ekstrakurikuler.edit', compact('ekstrakurikuler'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_ekstrakurikuler' => 'required|string|max:255',
            'pembina'     => 'required|string|max:255',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'deskripsi'   => 'nullable|string',
            'status'      => 'required|in:aktif,nonaktif',
        ]);

        Ekstrakurikuler::findOrFail($id)->update($data);

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Data ekstrakurikuler berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Ekstrakurikuler::findOrFail($id)->delete();

        return redirect()->route('ekstrakurikuler.index')
                         ->with('success', 'Ekstrakurikuler berhasil dihapus!');
    }
}