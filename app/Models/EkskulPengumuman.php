<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulPengumuman extends Model
{
    protected $table = 'ekskul_pengumumans';

    protected $fillable = ['ekstrakurikuler_id', 'user_id', 'judul', 'isi'];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}