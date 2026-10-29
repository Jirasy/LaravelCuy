<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat latihan ekskul untuk jadwal besok (in-app notification ke anggota aktif dan pembina).
// Di server butuh cron:  * * * * * cd /path/proyek && php artisan schedule:run >> /dev/null 2>&1
// Jam 16:00 WIB = 09:00 UTC, jadi "besok" menurut aplikasi (UTC) tanggalnya sama dengan di WIB.
// Pengingat yang sudah terkirim dicatat (PengingatTerkirim), jadi aman kalau jalan dua kali.
Schedule::command('ekskul:pengingat')->dailyAt('16:00')->timezone('Asia/Jakarta')->withoutOverlapping();

// Hapus password sementara yang sudah kedaluwarsa (butuh `php artisan schedule:run` berjalan tiap menit di server)
Schedule::command('model:prune', ['--model' => [\App\Models\KredensialSementara::class]])->daily();
