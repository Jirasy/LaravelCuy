<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function welcome(){
        return "hello world!";
    }

    public function oce(){
        return "oce";
    }

    public function beranda(){
        return "beranda";
    }
}
