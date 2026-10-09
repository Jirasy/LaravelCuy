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
        ];
    }

    public function pendaftaran()
    {
        return $this->hasMany(PendaftaranEkskul::class);
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