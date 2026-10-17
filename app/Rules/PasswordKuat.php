<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordKuat implements ValidationRule
{
    public const MIN = 10;

    public function __construct(private ?string $nama = null, private ?string $email = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $v = (string) $value;

        if (mb_strlen($v) < self::MIN) {
            $fail('Password minimal ' . self::MIN . ' karakter.');
            return;
        }

        $kurang = [];
        if (!preg_match('/\p{Lu}/u', $v))       $kurang[] = 'huruf besar';
        if (!preg_match('/\p{Ll}/u', $v))       $kurang[] = 'huruf kecil';
        if (!preg_match('/\d/', $v))            $kurang[] = 'angka';
        if (!preg_match('/[^\p{L}\d]/u', $v))   $kurang[] = 'simbol (contoh: ! @ # $ %)';

        if ($kurang) {
            $fail('Password harus mengandung ' . implode(', ', $kurang) . '.');
            return;
        }

        // Tidak boleh memuat nama/email sendiri atau kata yang terlalu umum
        $kecil = mb_strtolower($v);
        foreach ($this->kataTerlarang() as $kata) {
            if (mb_strlen($kata) >= 4 && str_contains($kecil, $kata)) {
                $fail('Password tidak boleh mengandung namamu, emailmu, atau kata yang terlalu umum.');
                return;
            }
        }

        // Cek daftar password bocor (hanya production, butuh internet; kalau gagal dicek, dianggap lolos)
        if (app()->isProduction() && !$this->tidakBocor($v)) {
            $fail('Password ini pernah bocor di internet. Pilih password lain.');
        }
    }

    private function kataTerlarang(): array
    {
        $kata = ['password', 'qwerty', '123456', 'abcdef', 'sekolah'];

        if ($this->email) {
            $kata[] = mb_strtolower(strstr($this->email, '@', true) ?: $this->email);
        }

        if ($this->nama) {
            foreach (preg_split('/\s+/', mb_strtolower(trim($this->nama))) as $bagian) {
                $kata[] = $bagian;
            }
        }

        return array_unique($kata);
    }

    private function tidakBocor(string $password): bool
    {
        try {
            return app(\Illuminate\Contracts\Validation\UncompromisedVerifier::class)
                ->verify(['value' => $password, 'threshold' => 1]);
        } catch (\Throwable) {
            return true;
        }
    }
}