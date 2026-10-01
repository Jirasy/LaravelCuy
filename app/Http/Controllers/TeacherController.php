<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;

class TeacherController extends Controller
{
    public function mapel()
    {
        $teachers = Teacher::all();
        $mapel1 = "Matematika";
        $mapel2 = "Bahasa Indonesia";
        $mapel3 = "Bahasa Inggris";
        return view('mapel', compact('teachers'));
    }
}
