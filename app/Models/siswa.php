<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class siswa extends Model
{
    protected $table = 'siswas';
    protected $fillable = [
        'ssw_name',
        'ssw_nisn',
        'ssw_class',
        'ssw_major',
    ];
}
