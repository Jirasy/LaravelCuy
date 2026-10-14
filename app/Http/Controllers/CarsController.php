<?php

namespace App\Http\Controllers;

use App\Models\cars;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        return view('Createcar');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'car_brand'  => 'required',
            'car_model'  => 'required',
            'car_engine' => 'required',
            'car_fuel'   => 'required',
            'car_type'   => 'required',
            'car_stock'  => 'required|integer',
            'car_price'  => 'required|integer',
        ]);

        cars::create($data);

        return redirect('/cars')
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
    public function edit($id)
    {
        $cars = cars::find($id);
        return view('editcar', compact('cars'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cars = cars::where('id', $id)->first();
        $cars->update([
            'car_brand' => $request->car_brand,
            'car_model' => $request->car_model,
            'car_engine' => $request->car_engine,
            'car_fuel' => $request->car_fuel,
            'car_type' => $request->car_type,
            'car_stock' => $request->car_stock,
            'car_price' => $request->car_price
        ]);

        return redirect('/cars')
            ->with('success', 'Car updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cars = cars::where('id', $id)->first();
        $cars->delete();
        return redirect('/cars')
            ->with('success', 'Car deleted successfully.');
    }

    /**
     * Show the buy form for a car.
     */
    public function buyForm($id)
    {
        $car = cars::findOrFail($id);
        return view('buycar', compact('car'));
    }

    /**
     * Process a purchase: reduce stock and record the sale.
     */
    public function buy(Request $request, $id)
    {
        $request->validate([
            'buyer_name' => 'required|string|max:255',
            'quantity'   => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $id) {
            $car = cars::lockForUpdate()->findOrFail($id);

            if ($car->car_stock < $request->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tidak cukup. Sisa stok: ' . $car->car_stock,
                ]);
            }

            $car->decrement('car_stock', $request->quantity);

            Sale::create([
                'car_id'      => $car->id,
                'buyer_name'  => $request->buyer_name,
                'quantity'    => $request->quantity,
                'total_price' => $car->car_price * $request->quantity,
            ]);
        });

        return redirect('/sales')
            ->with('success', 'Pembelian berhasil.');
    }

    /**
     * Sales history.
     */
    public function sales()
    {
        $sales = Sale::with('car')->latest()->get();
        return view('sales', compact('sales'));
    }
}