<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class templatingController extends Controller
{
    public function index()
    {
        return view('template');
    }
}