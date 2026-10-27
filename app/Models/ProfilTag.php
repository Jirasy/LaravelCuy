<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilTag extends Model
{
    protected $table = 'profil_tags';

    protected $fillable = ['user_id', 'pemberi_id', 'teks', 'status'];

    public const STATUS = [
        'privat' => 'Privat',
        'tampil' => 'Ditampilkan',
    ];

    public const MAKS_KARAKTER = 30;

    // Aturan: satu pemberi hanya boleh memberi 1 tag ke satu profil (tapi boleh men-tag banyak profil berbeda).
    // Mau mengganti? Tarik tag lamanya dulu, lalu beri yang baru.

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pemberi()
    {
        return $this->belongsTo(User::class, 'pemberi_id');
    }

    public function isTampil(): bool
    {
        return $this->status === 'tampil';
    }
}
