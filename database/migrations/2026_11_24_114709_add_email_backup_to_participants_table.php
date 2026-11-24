<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email cadangan (opsional) untuk mengirim ulang akun jika email kampus bermasalah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('email_backup')->nullable()->after('whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('email_backup');
        });
    }
};
