<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPanggilan extends Model
{
    protected $table = 'user_panggilans';

    protected $fillable = ['user_id', 'nama', 'utama'];

    protected $casts = [
        'utama' => 'boolean',
    ];

    public const MAKS_PER_USER = 5;
    public const MAKS_KARAKTER = 30;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
