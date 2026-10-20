<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulPertemuan extends Model
{
    protected $table = 'ekskul_pertemuans';

    protected $fillable = ['ekstrakurikuler_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'topik', 'dibuat_oleh'];

    protected $casts = ['tanggal' => 'date'];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function kehadirans()
    {
        return $this->hasMany(EkskulKehadiran::class, 'pertemuan_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // Contoh: "Rabu, 5 November 2026"
    public function getTanggalLengkapAttribute(): string
    {
        return $this->tanggal->copy()->locale('id')->isoFormat('dddd, D MMMM Y');
    }

    // Contoh: "15:30 - 17:00" atau null
    public function getJamAttribute(): ?string
    {
        if (!$this->jam_mulai) {
            return null;
        }

        return substr($this->jam_mulai, 0, 5) . ($this->jam_selesai ? ' - ' . substr($this->jam_selesai, 0, 5) : '');
    }
}