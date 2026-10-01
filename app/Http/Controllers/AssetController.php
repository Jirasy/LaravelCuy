<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;

class AssetController extends Controller
{
    public function Asset(){
        $infocus = Asset::all();
    //    foreach ($Asset as $Asset) {
    //     echo $Asset->ast_name. "<br>";
    //    }
   
    return view('Asset',compact(['infocus']));
    }
}