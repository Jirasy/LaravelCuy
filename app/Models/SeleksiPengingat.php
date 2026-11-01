<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Penanda bahwa pengingat jadwal sebuah tahap seleksi sudah dikirim (jenis: 24j atau 1j).
class SeleksiPengingat extends Model
{
    protected $table = 'seleksi_pengingats';

    protected $fillable = ['seleksi_tahap_id', 'jenis', 'dikirim_pada'];

    protected $casts = ['dikirim_pada' => 'datetime'];

    public function tahap()
    {
        return $this->belongsTo(SeleksiTahap::class, 'seleksi_tahap_id');
    }
}
