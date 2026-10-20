<?php

namespace App\Console\Commands;

use App\Enums\Hari;
use App\Models\EkskulHari;
use App\Models\PendaftaranEkskul;
use App\Models\PengingatTerkirim;
use App\Support\Pemberitahuan;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class KirimPengingatEkskul extends Command
{
    protected $signature = 'ekskul:pengingat {--tanggal= : Tanggal kegiatan (Y-m-d). Bawaan: besok}';

    protected $description = 'Kirim pengingat jadwal ekskul untuk besok ke anggota aktif dan pembina';

    public function handle(): int
    {
        $tanggal = $this->option('tanggal') ? Carbon::parse($this->option('tanggal')) : now()->addDay();
        $hari    = Hari::cases()[$tanggal->dayOfWeekIso - 1]->value;
        $kata    = $tanggal->isSameDay(now()->addDay()) ? 'besok' : 'pada ' . $tanggal->format('d-m-Y');

        $dikirim = 0;

        foreach (EkskulHari::with('ekstrakurikuler')->where('hari', $hari)->get() as $jadwal) {
            $ekskul = $jadwal->ekstrakurikuler;

            if (!$ekskul || $ekskul->status !== 'aktif') {
                continue;
            }

            $sudah = PengingatTerkirim::where('ekskul_hari_id', $jadwal->id)
                ->whereDate('tanggal', $tanggal->toDateString())
                ->exists();

            if ($sudah) {
                continue;
            }

            $anggota = PendaftaranEkskul::anggotaAktif()
                ->where('ekstrakurikuler_id', $ekskul->id)
                ->whereNotNull('user_id')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter(fn ($u) => $u && $u->is_active);

            $penerima = $anggota->merge($ekskul->pembinaAktif()->where('users.is_active', true)->get());

            $jam = substr($jadwal->jam_mulai, 0, 5);

            Pemberitahuan::kirim(
                $penerima,
                'Pengingat latihan',
                "{$ekskul->nama_ekstrakurikuler} akan dimulai {$kata} pukul {$jam} WIB.",
                route('ekstrakurikuler.show', $ekskul->id, false),
                '⏰'
            );

            PengingatTerkirim::create(['ekskul_hari_id' => $jadwal->id, 'tanggal' => $tanggal->toDateString()]);
            $dikirim++;
        }

        $this->info("Pengingat dikirim untuk {$dikirim} jadwal ({$hari}, {$tanggal->format('d-m-Y')}).");

        return self::SUCCESS;
    }
}