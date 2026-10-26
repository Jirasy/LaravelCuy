<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasisReview extends Model
{
    protected $table = 'casis_reviews';

    protected $fillable = ['casis_pendaftaran_id', 'reviewer_id', 'tipe', 'skor', 'rekomendasi', 'catatan'];

    public const TIPE = [
        'pembina' => 'Pembina OSIS',
        'admin'   => 'Admin',
        'senior'  => 'Senior (pengurus sebelumnya)',
    ];

    public const REKOMENDASI = [
        'lolos'         => 'Lolos',
        'pertimbangkan' => 'Dipertimbangkan',
        'tidak_lolos'   => 'Tidak lolos',
    ];

    public function casis()
    {
        return $this->belongsTo(CasisPendaftaran::class, 'casis_pendaftaran_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}