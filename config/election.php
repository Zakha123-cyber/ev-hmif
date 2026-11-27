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

];
