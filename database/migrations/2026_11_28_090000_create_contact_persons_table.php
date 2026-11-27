<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kontak panitia (WhatsApp) yang tampil di footer, halaman login, dan email akun.
     * Diatur admin lewat menu Kontak panitia; diisi awal dengan kontak yang sebelumnya
     * tertulis langsung di tampilan.
     */
    public function up(): void
    {
        Schema::create('contact_persons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('whatsapp', 20); // selalu diawali 62, hanya angka
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        DB::table('contact_persons')->insert([
            ['name' => 'Farda', 'whatsapp' => '6283823093973', 'sort' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dewi', 'whatsapp' => '6283852061105', 'sort' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_persons');
    }
};
