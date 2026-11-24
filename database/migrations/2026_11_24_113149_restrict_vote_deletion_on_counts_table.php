<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suara tidak boleh ikut terhapus saat kandidat, akun, atau peserta dihapus.
 * (peserta → users → counts sebelumnya CASCADE sehingga suara hilang diam-diam.)
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->recreateForeignKeys(fn ($foreign) => $foreign->restrictOnDelete());
    }

    public function down(): void
    {
        $this->recreateForeignKeys(fn ($foreign) => $foreign->cascadeOnDelete());
    }

    private function recreateForeignKeys(callable $onDelete): void
    {
        Schema::table('counts', function (Blueprint $table) {
            $table->dropForeign('counts_candidate_id_foreign');
            $table->dropForeign('counts_user_id_foreign');
        });

        Schema::table('counts', function (Blueprint $table) use ($onDelete) {
            $onDelete($table->foreign('candidate_id')->references('id')->on('candidates')->cascadeOnUpdate());
            $onDelete($table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate());
        });
    }
};
