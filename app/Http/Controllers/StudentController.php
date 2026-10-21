<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::all();
        return view('student', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('createStudent');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $createStudent = Student::create([
            'std_name' => $request->std_name,
            'std_nis' => $request->std_nis,
            'std_gender' => $request->std_gender,
            'std_birthplace' => $request->std_birthplace,
            'std_birthdate' => $request->std_birthdate,
            'std_address' => $request->std_address,
            'std_phone' => $request->std_phone,
            'std_email' => $request->std_email
        ]);
        
        return redirect('/Student');
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
