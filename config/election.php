<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password akun admin bawaan (seeder)
    |--------------------------------------------------------------------------
    |
    | Dipakai hanya saat menjalankan `php artisan db:seed`. Jika kosong, seeder
    | membuat password acak dan menampilkannya sekali di terminal.
    |
    */

    'admin_seed_password' => env('ADMIN_SEED_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Periode kepengurusan bawaan
    |--------------------------------------------------------------------------
    |
    | Nilai bawaan bila admin belum mengisi Periode di halaman Jadwal Pemilihan.
    |
    */

    'period' => '2025/2026',

    /*
    |--------------------------------------------------------------------------
    | Alamat situs pemilihan untuk tautan di email
    |--------------------------------------------------------------------------
    |
    | Tombol di email akun peserta selalu menuju alamat ini (halaman awal), dari
    | mana pun email dikirim, termasuk saat diuji dari komputer lokal.
    |
    */

    'site_url' => env('ELECTION_SITE_URL', 'https://election-vote.hmifunej.id/'),

    /*
    |--------------------------------------------------------------------------
    | Alamat halaman login admin
    |--------------------------------------------------------------------------
    |
    | Admin hanya bisa masuk lewat alamat ini; /login khusus peserta. Di server,
    | isi ADMIN_LOGIN_PATH di .env dengan alamat yang sulit ditebak (repo bisa
    | dibaca orang lain). Tanpa garis miring di depan.
    |
    */

    'admin_login_path' => trim(env('ADMIN_LOGIN_PATH', 'login-admin-hmif'), '/'),

];
