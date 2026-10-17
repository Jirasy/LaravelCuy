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
        'must_change_password',
        'no_hp',
        'no_hp_tampil',
        'bio',
        'navbar_gaya',
        'kartu_gaya',
        'kartu_aksen',
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
            'must_change_password' => 'boolean',
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

    // Ekskul yang sedang dibina user ini (relevan untuk role pembina)
    public function ekskulDikelola()
    {
        return $this->belongsToMany(Ekstrakurikuler::class, 'ekskul_pembinas', 'user_id', 'ekstrakurikuler_id')
            ->wherePivotNull('selesai');
    }

    private ?array $cacheIdKelola = null;

    // Daftar id ekskul yang dikelola (di-cache selama satu request)
    public function idEkskulDikelola(): array
    {
        return $this->cacheIdKelola ??= $this->ekskulDikelola()
            ->pluck('ekstrakurikulers.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    // Pilihan tampilan yang diizinkan (dipakai validasi dan view)
    public const NAVBAR_GAYA = ['melayang', 'kaca', 'solid', 'ringkas'];
    public const KARTU_GAYA  = ['kaca', 'gradasi', 'polos'];

    // Warna aksen kartu profil. "ikut_palet" mengikuti palet wallpaper pilihan user.
    public const AKSEN_RGB = [
        'indigo'  => '99 102 241',
        'rose'    => '244 63 94',
        'emerald' => '16 185 129',
        'amber'   => '245 158 11',
        'sky'     => '14 165 233',
    ];

    public const NO_HP_TAMPIL = [
        'staff'          => 'Hanya admin & pembina',
        'anggota_ekskul' => 'Teman satu ekskul + admin & pembina',
        'semua'          => 'Semua pengguna',
    ];

    // Tautan WhatsApp, contoh: https://wa.me/6281234567890
    public function getWaLinkAttribute(): ?string
    {
        $digit = preg_replace('/\D+/', '', (string) $this->no_hp);

        return $digit ? 'https://wa.me/' . $digit : null;
    }

    // Nomor untuk dibaca manusia, contoh: 081234567890
    public function getNoHpTampilanAttribute(): ?string
    {
        $digit = preg_replace('/\D+/', '', (string) $this->no_hp);
        if ($digit === '') {
            return null;
        }

        return str_starts_with($digit, '62') ? '0' . substr($digit, 2) : $digit;
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