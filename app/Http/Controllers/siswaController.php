<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\siswa;

class siswaController extends Controller
{
    public function siswa()
    {
        $siswa = [
            'ssw_name' => 'Jimny Al Ramadhan Syahnendi',
            'ssw_nisn' => '2526.10.0.220417',
            'ssw_class' => 'XII RPL',
            'ssw_major' => 'REKAYASA PERANGKAT LUNAK',
        ];
        return view('siswa', compact('siswa'));
    }
    public function index()
    {
        $query = siswa::query();

    if ($request->has('search')) {
        $query->where('ssw_name', 'like', '%' . $request->search . '%')
              ->orWhere('ssw_nisn', 'like', '%' . $request->search . '%');
    }

    $siswas = $query->get();
    return view('siswa.index', compact('siswas'));
    }
    public function create()
    {
        return view('siswa.create');
    }
    public function store(Request $request)
    {
        request()->validate([
            'ssw_name' => 'required',
            'ssw_nisn' => 'required|unique:siswas,ssw_nisn',
            'ssw_class' => 'required',
            'ssw_major' => 'required',
        ]);
        siswa::create([
            'ssw_name'  => $request->ssw_name,
            'ssw_nisn'  => $request->ssw_nisn,
            'ssw_class' => $request->ssw_class,
            'ssw_major' => $request->ssw_major,
        ]);
        return redirect('/siswa')->with('success', 'Data siswa berhasil ditambahkan!');
}
    
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
    }
}

    
    
