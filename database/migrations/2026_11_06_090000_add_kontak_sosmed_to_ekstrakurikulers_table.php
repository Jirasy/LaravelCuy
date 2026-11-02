<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kontak & sosial media ekskul yang HANYA tampil untuk anggota aktif (dan admin/pembina ekskul itu).
        // Terpisah dari ekskul_tautans, yang tampil untuk semua pengguna.
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->string('wa_grup_url', 500)->nullable()->after('deskripsi');
            $table->string('wa_pembina', 20)->nullable()->after('wa_grup_url');     // format 62812xxxx, sama seperti users.no_hp
            $table->string('telegram_url', 500)->nullable()->after('wa_pembina');
            $table->string('instagram_url', 500)->nullable()->after('telegram_url');
            $table->string('tiktok_url', 500)->nullable()->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::table('ekstrakurikulers', function (Blueprint $table) {
            $table->dropColumn(['wa_grup_url', 'wa_pembina', 'telegram_url', 'instagram_url', 'tiktok_url']);
        });
    }
};
