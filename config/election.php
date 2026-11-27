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
    | Maskot 3D COGITO di halaman awal
    |--------------------------------------------------------------------------
    |
    | Path relatif terhadap folder public. Maskot hanya tampil jika file ada.
    | Ekspor dari Blender: File > Export > glTF 2.0 (.glb).
    |
    */

    'mascot_path' => 'frontend/3d/cogito.glb',

    /*
    |--------------------------------------------------------------------------
    | Periode kepengurusan yang dipilih
    |--------------------------------------------------------------------------
    |
    | Ditampilkan di halaman /vote. Ganti tiap tahun pemilihan.
    |
    */

    'period' => '2025/2026',

];
