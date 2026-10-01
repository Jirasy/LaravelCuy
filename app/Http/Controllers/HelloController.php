<?php

namespace App\Http\Controllers;

use illuminate\Http\Request;

class HelloController extends Controller
{
    public function welcome(){
        $hitung = 1+1 ;
        return view('awikwok', compact('hitung'));
    }

    public function install(){
        return view('gararetek');
    }
}