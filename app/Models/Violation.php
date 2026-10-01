<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    protected $fillable = ['id','vlt_name','vlt_point','vlt_punishment'];
}
