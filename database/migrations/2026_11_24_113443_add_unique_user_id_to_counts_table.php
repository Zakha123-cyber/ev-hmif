<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satu akun hanya boleh punya satu suara — dijamin di level database,
 * sehingga dua request bersamaan tidak bisa menghasilkan dua suara.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('counts')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->having('total', '>', 1)
            ->pluck('total', 'user_id');

        if ($duplicates->isNotEmpty()) {
            $list = $duplicates->map(fn ($total, $userId) => "user_id {$userId} ({$total}x)")->implode(', ');

            throw new RuntimeException(
                "Migrasi dibatalkan: ditemukan suara ganda: {$list}. Bersihkan data duplikat terlebih dahulu, lalu jalankan migrate lagi."
            );
        }

        Schema::table('counts', function (Blueprint $table) {
            $table->unique('user_id', 'counts_user_id_unique');
        });
    }

    public function down(): void
    {
        // MySQL memakai index unique ini untuk foreign key user_id,
        // jadi FK harus dilepas dulu lalu dipasang kembali.
        Schema::table('counts', function (Blueprint $table) {
            $table->dropForeign('counts_user_id_foreign');
            $table->dropUnique('counts_user_id_unique');
        });

        Schema::table('counts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }
};
