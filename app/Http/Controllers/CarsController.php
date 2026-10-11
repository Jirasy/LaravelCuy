<?php

namespace App\Http\Controllers;

use App\Models\cars;
use Illuminate\Http\Request;

class CarsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cars = cars::all();
        return view('car', compact('cars'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Cars.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'car_brand' => 'required',
            'car_model' => 'required',
            'car_engine' => 'required',
            'car_fuel' => 'required',
            'car_type' => 'required',
        ]);

        cars::create($request->all());

        return redirect()->route('cars')
            ->with('success', 'Car created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(cars $cars)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(cars $cars)
    {
        return view('Cars.edit', compact('cars'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, cars $cars)
    {
        $request->validate([
            'car_brand' => 'required',
            'car_model' => 'required',
            'car_engine' => 'required',
            'car_fuel' => 'required',
            'car_type' => 'required',
        ]);

        $cars->update($request->all());

        return redirect()->route('cars.index')
            ->with('success', 'Car updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(cars $cars)
    {
        $cars->delete();

        return redirect()->route('cars.index')
            ->with('success', 'Car deleted successfully.');
    }
}
