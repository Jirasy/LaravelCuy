<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $guarded = [];

    public function car()
    {
        return $this->belongsTo(cars::class, 'car_id');
    }
}