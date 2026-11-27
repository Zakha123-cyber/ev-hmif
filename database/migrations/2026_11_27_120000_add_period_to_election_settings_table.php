<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Periode kepengurusan (mis. "2025/2026") diatur admin bersama jadwal.
     * Kosong = pakai config('election.period').
     */
    public function up(): void
    {
        Schema::table('election_settings', function (Blueprint $table) {
            $table->string('period', 9)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('election_settings', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
};
