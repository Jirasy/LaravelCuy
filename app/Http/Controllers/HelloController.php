<?php

namespace App\Http\Controllers;

use illuminate\Http\Request;

class HelloController extends Controller
{
    public function index()
    {
        return view('hello');
    }


    public function welcome()
    {
        $hitung = 1 + 1;
        return view('welcome', compact('hitung'));
    }

    public function install()
    {
        return view('gararetek');
    }
}
