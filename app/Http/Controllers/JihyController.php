<?php

namespace App\Http\Controllers;

use App\Models\Jihy;
use Illuminate\Http\Request;

class JihyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jihys= Jihy::all();
        // dd($jihys);
        return view('Jihy', compact(['jihys']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('CreateJihy');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(JIHY $jIHY)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JIHY $jIHY)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JIHY $jIHY)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JIHY $jIHY)
    {
        //
    }
}
