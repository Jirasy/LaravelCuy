<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use Illuminate\Http\Request;

class ViolationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vlt = Violation::all();
        // dd($vlt);
        return view('Violation', compact(['vlt']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('CreateViolation');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $createViolation = Violation::create([
            'vlt_name' => $request->vlt_name,
            'vlt_point' => $request->vlt_point,
            'vlt_punishment' => $request->vlt_punishment
        ]);
        
        return redirect('/violation');
    }

    /**
     * Display the specified resource.
     */
    public function show(Violation $violation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Violation $violation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Violation $violation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Violation $violation)
    {
        //
    }
}
