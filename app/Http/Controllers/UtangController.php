<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pinjam');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $jumlahPinjaman = $request->nominalpinjam;
        $tenor = $request->tenor;
        $bunga = 5.75/100;
        $pokokPinjam = $jumlahPinjaman / $tenor;
        $bungaPerBulan = ($jumlahPinjaman * $bunga) / $tenor;

        return $pokokPinjam+$bungaPerBulan;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
