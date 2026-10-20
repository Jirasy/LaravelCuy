<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengingatTerkirim extends Model
{
    protected $table = 'pengingat_terkirims';

    protected $fillable = ['ekskul_hari_id', 'tanggal'];
}