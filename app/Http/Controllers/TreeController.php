<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trees= Tree::all(); 
        // dd($trees);
        // return view('Tree.index', compact('trees'));
        return view('pohon',compact(['trees']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('CreatePohon');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       // dd($request);
        $createTree = Tree::create([
            'tre_name' => $request->tre_name,
            'tre_family' => $request->tre_family,
            'tre_age' => $request->tre_age,
            'tre_origin' => $request->tre_origin,
            'tre_condition' => $request->tre_condition
        ]);
        
        return redirect('/Tree');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tree $tree)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $Tree = Tree::find($id);
        // dd($Tree);
        return view('editPohon',compact(['Tree']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $Tree = Tree::where('id', $id)->first();
        // dd($Tree);
        $Tree->update([
            'tre_name' => $request->tre_name,
            'tre_family' => $request->tre_family,
            'tre_age' => $request->tre_age,
            'tre_origin' => $request->tre_origin,
            'tre_condition' => $request->tre_condition
        ]);
        return redirect('/Tree');
    
    }
        //
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tree = Tree::where('id', $id)->first();
        $tree->delete();
        return redirect('/Tree');
    }

    }

