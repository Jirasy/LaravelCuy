<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Pindahkan jabatan lama (kolom enum pendaftaran_ekskuls.jabatan) ke struktur baru.
// Hanya jabatan yang BENAR-BENAR dipakai yang dibuat, jadi ekskul tidak dipenuhi jabatan yang tidak perlu.
// Kolom enum lama dibiarkan (tidak dihapus) sampai kamu yakin datanya sudah benar.
return new class extends Migration
{
    public function up(): void
    {
        $label = [
            'ketua'              => 'Ketua',
            'wakil_ketua'        => 'Wakil Ketua',
            'sekretaris'         => 'Sekretaris',
            'bendahara'          => 'Bendahara',
            'koordinator'        => 'Koordinator',
            'pelatih'            => 'Pelatih',
            'pembina_pendamping' => 'Pembina Pendamping',
        ];
        $urutan = array_flip(array_keys($label));
        $sekarang = now();

        $baris = DB::table('pendaftaran_ekskuls')
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', 'anggota')
            ->get();

        foreach ($baris as $r) {
            if (!isset($label[$r->jabatan])) {
                continue;
            }

            $jabatan = DB::table('ekskul_jabatans')
                ->where('ekstrakurikuler_id', $r->ekstrakurikuler_id)
                ->where('nama', $label[$r->jabatan])
                ->where('varian', '')
                ->first();

            $jabatanId = $jabatan ? $jabatan->id : DB::table('ekskul_jabatans')->insertGetId([
                'ekstrakurikuler_id' => $r->ekstrakurikuler_id,
                'nama'               => $label[$r->jabatan],
                'varian'             => '',
                'urutan'             => $urutan[$r->jabatan],
                'created_at'         => $sekarang,
                'updated_at'         => $sekarang,
            ]);

            $mulai = $r->tahun_mulai ?: Carbon::parse($r->created_at ?? $sekarang)->year;

            DB::table('ekskul_anggota_jabatans')->insert([
                'pendaftaran_id'  => $r->id,
                'jabatan_id'      => $jabatanId,
                'periode_mulai'   => $mulai,
                'periode_selesai' => $r->tahun_selesai,
                'created_at'      => $sekarang,
                'updated_at'      => $sekarang,
            ]);
        }
    }

    public function down(): void
    {
        // data hasil salinan dibiarkan
    }
};