<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulHari extends Model
{
    protected $table = 'ekskul_haris';

    public $timestamps = false;

    protected $fillable = ['ekstrakurikuler_id', 'hari'];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }
}