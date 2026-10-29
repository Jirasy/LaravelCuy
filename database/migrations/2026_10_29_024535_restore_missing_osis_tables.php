<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('osis_periode_seniors')) {
            Schema::create('osis_periode_seniors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('osis_periode_id')->constrained('osis_periodes')->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['osis_periode_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('casis_pendaftarans')) {
            Schema::create('casis_pendaftarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('osis_periode_id')->constrained('osis_periodes')->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('motivasi')->nullable();
                $table->text('pengalaman')->nullable();
                $table->text('visi_misi')->nullable();
                $table->enum('status', ['draft', 'diajukan', 'diterima', 'ditolak', 'mengundurkan_diri'])->default('draft');
                $table->foreignId('didaftarkan_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->text('catatan_keputusan')->nullable();
                $table->timestamp('diputuskan_pada')->nullable();
                $table->timestamps();

                $table->unique(['osis_periode_id', 'user_id']);
                $table->index(['osis_periode_id', 'status']);
            });
        }

        if (! Schema::hasTable('casis_reviews')) {
            Schema::create('casis_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('casis_pendaftaran_id')->constrained('casis_pendaftarans')->onDelete('cascade');
                $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
                $table->enum('tipe', ['pembina', 'admin', 'senior']);
                $table->unsignedTinyInteger('skor');
                $table->enum('rekomendasi', ['lolos', 'pertimbangkan', 'tidak_lolos']);
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['casis_pendaftaran_id', 'reviewer_id']);
            });
        }
    }

    public function down(): void
    {
        // Keep this repair non-destructive; some installations already had these tables.
    }
};
