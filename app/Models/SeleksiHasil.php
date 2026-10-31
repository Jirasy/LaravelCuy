<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Hasil seorang pendaftar di satu tahap seleksi. Lolos di tahap terakhir = diterima; tidak lolos di tahap mana pun = ditolak.
class SeleksiHasil extends Model
{
    protected $table = 'seleksi_hasils';

    protected $fillable = ['casis_pendaftaran_id', 'seleksi_tahap_id', 'hasil', 'catatan', 'diputuskan_oleh'];

    public const HASIL = [
        'lolos'       => 'Lolos',
        'tidak_lolos' => 'Tidak lolos',
    ];

    public function casis()
    {
        return $this->belongsTo(CasisPendaftaran::class, 'casis_pendaftaran_id');
    }

    public function tahap()
    {
        return $this->belongsTo(SeleksiTahap::class, 'seleksi_tahap_id');
    }

    public function pemutus()
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }
}
