<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function biodata()
    {
        $name = 'kemed';
        $nama = 'kehed';
        $umur = 20;
        return view('profile.biodata', compact(['name', 'nama', 'umur']));
    }

    public function previousSchool()
    {
    //     $school = 'SMK Mahaputra Cerdas Utama';
    //     $major = 'Rekayasa Perangkat Lunak';
    //     return view('profile.previous-school', compact(['school', 'major']));
    return "alhamdullillah saya sudah bisa menampilkan data sekolah sebelumnya";
    }
}