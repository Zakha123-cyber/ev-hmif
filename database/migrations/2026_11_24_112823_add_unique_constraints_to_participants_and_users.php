<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satu NIM = satu peserta, satu peserta = satu akun.
 * Mencegah satu orang memiliki beberapa akun (dan memilih berkali-kali).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->abortIfDuplicates('participants', 'nim', 'NIM peserta');
        $this->abortIfDuplicates('users', 'participant_id', 'akun per peserta (users.participant_id)');

        Schema::table('participants', function (Blueprint $table) {
            $table->unique('nim', 'participants_nim_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('participant_id', 'users_participant_id_unique');
        });
    }

    public function down(): void
    {
        // MySQL memakai index unique ini untuk foreign key participant_id,
        // jadi FK harus dilepas dulu lalu dipasang kembali.
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('user_participant_foreign_id');
            $table->dropUnique('users_participant_id_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('participant_id', 'user_participant_foreign_id')
                ->references('id')->on('participants')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique('participants_nim_unique');
        });
    }

    /**
     * Jangan hapus data otomatis: hentikan migrasi dan tampilkan duplikat
     * supaya admin bisa membersihkannya secara manual.
     */
    private function abortIfDuplicates(string $table, string $column, string $label): void
    {
        $duplicates = DB::table($table)
            ->select($column, DB::raw('COUNT(*) as total'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->having('total', '>', 1)
            ->pluck('total', $column);

        if ($duplicates->isNotEmpty()) {
            $list = $duplicates->map(fn ($total, $value) => "{$value} ({$total}x)")->implode(', ');

            throw new RuntimeException(
                "Migrasi dibatalkan: ditemukan duplikat {$label}: {$list}. Bersihkan data duplikat terlebih dahulu, lalu jalankan migrate lagi."
            );
        }
    }
};
