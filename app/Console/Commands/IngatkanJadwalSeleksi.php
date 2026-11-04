<?php

namespace App\Console\Commands;

use App\Models\CasisPendaftaran;
use App\Models\SeleksiPengingat;
use App\Models\SeleksiTahap;
use App\Services\OsisService;
use App\Support\Pemberitahuan;
use Illuminate\Console\Command;

// Pengingat jadwal tahap seleksi: sekali saat tahap dimulai dalam 24 jam, sekali lagi saat kurang dari 1 jam.
// Penerima: pendaftar yang SEDANG berada di tahap itu (tahap sebelumnya sudah lolos), serta penilai periode (admin, pembina,
// senior) kalau ada pendaftar yang dijadwalkan. Aman dijalankan berulang: pengingat yang sudah terkirim dicatat di
// seleksi_pengingats, dan catatannya dihapus saat jadwal tahap diubah (supaya jadwal baru diingatkan lagi).
class IngatkanJadwalSeleksi extends Command
{
    protected $signature = 'seleksi:ingatkan';

    protected $description = 'Kirim pengingat jadwal tahap seleksi (24 jam dan 1 jam sebelum mulai) ke pendaftar dan penilai';

    // Jenis pengingat => jam sebelum mulai. Yang terkecil diproses dulu: tahap yang sudah dekat cukup menerima
    // satu pengingat, tidak dua sekaligus (pengingat 24 jam ikut ditandai terkirim).
    private const JENIS = ['1j' => 1, '24j' => 24];

    public function handle(): int
    {
        $dikirim = 0;

        foreach (self::JENIS as $jenis => $jam) {
            $tahaps = SeleksiTahap::with('periode.ekstrakurikuler')
                ->whereNotNull('mulai_pada')
                ->where('mulai_pada', '>', now())
                ->where('mulai_pada', '<=', now()->addHours($jam))
                ->whereHas('periode', fn ($q) => $q->whereIn('status', ['rekrutmen', 'seleksi']))
                ->whereDoesntHave('pengingats', fn ($q) => $q->where('jenis', $jenis))
                ->orderBy('mulai_pada')
                ->get();

            foreach ($tahaps as $tahap) {
                if ($this->kirim($tahap, $jenis)) {
                    $dikirim++;
                }
            }
        }

        $this->info("Pengingat seleksi dikirim untuk {$dikirim} tahap.");

        return self::SUCCESS;
    }

    private function kirim(SeleksiTahap $tahap, string $jenis): bool
    {
        $periode = $tahap->periode;
        $ekskul  = $periode->ekstrakurikuler;
        $tahaps  = $periode->tahapSemua();

        // Pendaftar yang sedang di tahap ini: belum punya hasil di tahap ini dan semua tahap sebelumnya
        $peserta = CasisPendaftaran::with(['user', 'hasils'])
            ->where('osis_periode_id', $periode->id)
            ->where('status', 'diajukan')
            ->get()
            ->filter(function ($c) use ($tahap, $tahaps) {
                $sudah = $c->hasils->pluck('seleksi_tahap_id')->all();

                return $tahaps->first(fn ($t) => ! in_array($t->id, $sudah, true))?->id === $tahap->id;
            })
            ->values();

        if ($peserta->isEmpty()) {
            return false;   // belum ada yang perlu diingatkan; dicek lagi di jalan berikutnya
        }

        $awal  = $jenis === '1j' ? 'Kurang dari 1 jam lagi' : 'Dalam 24 jam ke depan';
        $label = $ekskul->label_calon;

        Pemberitahuan::kirim(
            $peserta->pluck('user')->filter(fn ($u) => $u && $u->is_active),
            "Pengingat seleksi: {$tahap->nama}",
            "{$awal}: tahap {$tahap->urutan} seleksi {$label} {$ekskul->nama_ekstrakurikuler} {$periode->nama}.\n{$tahap->ringkasan}",
            route('casis.create', [$ekskul->id], false),
            'alarm'
        );

        $penilai = app(OsisService::class)->pengelolaSeleksi($ekskul)->merge($periode->seniors()->where('users.is_active', true)->get())->unique('id');

        Pemberitahuan::kirim(
            $penilai,
            "Pengingat seleksi: {$tahap->nama}",
            "{$awal}: tahap {$tahap->urutan} seleksi {$label} {$ekskul->nama_ekstrakurikuler} {$periode->nama} untuk {$peserta->count()} pendaftar.\n{$tahap->ringkasan}",
            route('casis.index', [$ekskul->id, 'periode' => $periode->id], false),
            'alarm'
        );

        // Pengingat 1 jam menggantikan yang 24 jam kalau yang terakhir ini belum sempat terkirim
        foreach ($jenis === '1j' ? ['1j', '24j'] : ['24j'] as $tandai) {
            SeleksiPengingat::firstOrCreate(['seleksi_tahap_id' => $tahap->id, 'jenis' => $tandai], ['dikirim_pada' => now()]);
        }

        return true;
    }
}
