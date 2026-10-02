<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranEkskul extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_ekskuls';

    protected $fillable = [
        'user_id',
        'ekstrakurikuler_id',
        'status',
        'alasan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }
}