<?php

namespace App\Http\Controllers;

use App\Models\health;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $healths= health::all();
        dd($healths);
        // return view('Health.index', compact('healths'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(health $health)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(health $health)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, health $health)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(health $health)
    {
        //
    }
}
