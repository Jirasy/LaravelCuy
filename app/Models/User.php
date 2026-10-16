<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nisn',
        'kelas',
        'role',
        'foto',
        'wallpaper',
        'palet',
        'is_alumni',
        'angkatan_sekolah',
        'tahun_lulus',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_alumni' => 'boolean',
        ];
    }

    public function pendaftaran()
    {
        return $this->hasMany(PendaftaranEkskul::class);
    }

    // URL pakai asset() supaya ikut host yang sedang dibuka (tidak tergantung APP_URL)
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    public function getWallpaperUrlAttribute(): ?string
    {
        return $this->wallpaper ? asset('storage/' . $this->wallpaper) : null;
    }

    // Inisial untuk avatar kalau belum punya foto, mis. "Jimny Al" -> "JA"
    public function getInisialAttribute(): string
    {
        $bagian = preg_split('/\s+/', trim($this->name));
        return mb_strtoupper(mb_substr($bagian[0] ?? '?', 0, 1) . (isset($bagian[1]) ? mb_substr($bagian[1], 0, 1) : ''));
    }

    // Contoh: "Alumni angkatan 2023" / "Angkatan 2025" / null
    public function getStatusSekolahAttribute(): ?string
    {
        if ($this->is_alumni) {
            return 'Alumni' . ($this->angkatan_sekolah ? ' angkatan ' . $this->angkatan_sekolah : '');
        }
        return $this->angkatan_sekolah ? 'Angkatan ' . $this->angkatan_sekolah : null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPembina(): bool
    {
        return $this->role === 'pembina';
    }

    // Admin & pembina = pengelola
    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'pembina'], true);
    }
}