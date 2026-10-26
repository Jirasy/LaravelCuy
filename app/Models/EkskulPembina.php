<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulPembina extends Model
{
    protected $table = 'ekskul_pembinas';

    protected $fillable = ['ekstrakurikuler_id', 'user_id', 'mulai', 'selesai'];

    protected $casts = [
        'mulai'   => 'date',
        'selesai' => 'date',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}