# COGITO: maskot animasi dengan balon sapaan dan arahan

Tanggal: 2026-11-27. Status: disetujui pengguna 2026-11-27.

## Maksud

Pengguna menambahkan dua GIF COGITO (maskot HMIF):

- `Animasi Salam Penjelajah Cilik.gif` (melambai);
- `Animasi Salute Pramuka Chibi.gif` (hormat).

COGITO harus tampil di halaman awal dan di `/vote`. Balon dialog yang keluar dari COGITO dipakai untuk **sapaan dan
arahan pengunjung**. Kalimatnya mengikuti keadaan jadwal dan pengunjung, dengan data nyata. Tujuannya membuat
halaman terasa hidup sekaligus menuntun orang ke langkah berikutnya.

Pengganti rencana lama: model 3D `.glb` rusak, dan slot `<model-viewer>` di pita jadwal dihapus.

## Tujuan

- **Halaman awal:** COGITO salam menempel di kanan bawah layar (`position: fixed`) dan tetap terlihat saat
  digulir.
- **`/vote` belum dibuka dan ditutup:** sama seperti halaman awal.
- **`/vote` saat mencoblos:** COGITO tetap tampil, sesuai permintaan pengguna. Lihat "Surat suara" di bawah.
- **`/vote` tanda terima:** COGITO salute berdiri di samping kotak suara.
- **Balon dialog:** satu kalimat per waktu.
  - Kalimat pertama muncul sekitar 1 detik setelah halaman dimuat.
  - "Lanjut" atau mengetuk COGITO menampilkan kalimat berikutnya.
  - "Tutup" menyembunyikan balon selama sesi itu (sessionStorage per halaman). Mengetuk COGITO membukanya lagi.
  - Kalimat terakhir boleh membawa satu tombol aksi nyata, seperti "Masuk untuk memilih" atau "Buka surat suara".

## Bukan tujuan

- Mengubah alur memilih, jadwal, atau server.
- Menggambar atau menganimasikan ulang COGITO. Yang dipakai adalah 9 frame GIF dari pengguna.

## Anti-tujuan

- Tidak ada tombol jeda atau matikan animasi (keputusan pengguna). Dengan `prefers-reduced-motion`, COGITO tampil
  sebagai gambar diam, berupa satu frame dari GIF.
- Teks balon tidak berganti sendiri. Pergantian hanya terjadi oleh aksi pengunjung, jadi tidak ada gerak teks
  otomatis yang perlu dijeda.
- COGITO tidak boleh menutupi tombol "Kirim suara", dialog konfirmasi, atau animasi kotak suara.
- Tidak ada angka atau klaim karangan. Tanggal dan nama diambil dari data nyata.

## Pendekatan yang dipertimbangkan

- **A. GIF apa adanya.** Latarnya hitam pekat, sehingga COGITO tampil dalam kotak hitam. Ditolak.
- **B. Latar hitam dihapus, lalu dikonversi ke WebP animasi transparan (dipilih).** Hapus latar dengan flood-fill
  dari keempat sudut tiap frame, toleransi sekitar 8%. Hasil uji bersih di atas putih maupun hijau tua. GIF asli
  disimpan. Bila pengguna kelak memberi ekspor transparan, cukup file-nya yang ditukar.
- **C. Video WebM dengan alpha.** Butuh format kedua untuk Safari. Terlalu berat untuk 9 frame.

## Rancangan

### Aset

- `public/frontend/images/cogito/salam.webp` dan `salute.webp`: animasi transparan, ukuran asli 437x388.
- `salam-still.webp` dan `salute-still.webp`: satu frame diam untuk `prefers-reduced-motion`, dipasang lewat
  `<picture><source media="(prefers-reduced-motion: reduce)">`.
- GIF asli disimpan di `resources/mascot/`, sebagai sumber dan tidak disajikan.

### Komponen bersama

Partial `partials/cogito.blade.php` menerima:

- `pose` (`salam` atau `salute`);
- `lines` (array kalimat);
- `action` (opsional: `{label, href}`);
- `variant` (`corner` atau `inline`).

Markup:

```
<aside class="cogito cogito--corner" aria-label="Pesan dari COGITO" data-cogito data-cogito-key="{halaman}">
  <div class="cogito__bubble" data-cogito-bubble hidden>
    <p data-cogito-line>…</p>
    [<a class="btn btn--primary">aksi</a>] (hanya di kalimat terakhir)
    <button data-cogito-next>Lanjut</button> <button data-cogito-close>Tutup</button>
  </div>
  <button class="cogito__figure" data-cogito-toggle aria-label="Bicara dengan COGITO">
    <picture>… <img alt="" width height></picture>
  </button>
</aside>
```

- Tanpa JS, balon menampilkan kalimat pertama saja, tanpa tombol Lanjut atau Tutup.
- Semua kalimat ditulis di server sebagai `data-lines` (JSON) atau `<template>`, lalu JS menampilkannya satu per
  satu.
- Di kalimat terakhir, "Lanjut" disembunyikan dan tombol aksi tampil bila ada.

### Kalimat (disetujui pengguna)

Halaman awal:

| Keadaan | Kalimat | Aksi |
|---|---|---|
| Jadwal belum ada | "Halo! Aku COGITO, maskot HMIF." / "Jadwal pemilihan belum diumumkan. Akun memilih dikirim ke email kampusmu." / "Sambil menunggu, buka amplop paslon untuk kenalan." | - |
| Akan dibuka | "Halo! Aku COGITO, maskot HMIF." / "Pemilihan dibuka {l, j F} pukul {H.i} WIB." / "Cek email kampusmu untuk akun memilih." | - |
| Berlangsung, belum login | "Halo! Aku COGITO, maskot HMIF." / "Pemilihan sedang berlangsung sampai {l, j F} pukul {H.i} WIB." / "Masuk dengan akun dari email kampusmu." | Masuk untuk memilih |
| Berlangsung, login, belum memilih | "Halo, {nama}! Surat suaramu sudah menunggu." | Buka surat suara |
| Login dan sudah memilih | "Terima kasih sudah memilih! Hasil diumumkan setelah pemilihan ditutup {l, j F} pukul {H.i} WIB." | - |
| Ditutup | "Pemilihan sudah ditutup. Terima kasih untuk semua yang memilih!" / "Lihat hasil penghitungannya di bawah." | Lihat hasil (`#hasil`) |
| Admin login | Sama seperti "Berlangsung, belum login" tanpa aksi masuk. Aksinya "Panel admin". | Panel admin |

`/vote`:

| Halaman | Kalimat | Aksi |
|---|---|---|
| Belum dibuka | "Halo, {nama}!" / "Surat suara muncul di sini saat pemilihan dibuka {l, j F} pukul {H.i} WIB." / "Sambil menunggu, buka amplop paslon di bawah." | - |
| Jadwal belum ada | "Halo, {nama}!" / "Jadwal pemilihan belum diumumkan panitia." / "Sambil menunggu, buka amplop paslon di bawah." | - |
| Surat suara | "Halo, {nama}! Ketuk foto paslon pilihanmu." / "Ingin membandingkan? Tekan Visi & misi di bawah nama paslon." / "Sudah yakin? Tekan Kirim suara. Suara hanya bisa dikirim sekali." | - |
| Ditutup, tidak memilih | "Pemilihan sudah ditutup." / "Hasil penghitungan ada di halaman awal." | Lihat hasil |
| Tanda terima | "Terima kasih sudah memilih!" | - |

### Surat suara (`/vote` dibuka, keputusan pembangun)

Pengguna ingin COGITO tetap tampil saat mencoblos. Agar tidak menutupi tombol "Kirim suara", kolom paslon, dan
animasi kotak suara di tengah bawah layar, COGITO di halaman ini **tidak menempel di pojok**. COGITO berdiri di
sisi kanan pita teal, di samping judul "Berikan suaramu", dengan balon di sebelahnya (varian `inline`). Di HP ukurannya
kecil dan sebaris dengan judul. Saat form dikirim (`.is-sending`), COGITO tidak ikut bergerak.

### Tanda terima

COGITO salute (varian `inline`) berdiri di kanan kotak suara CSS, dengan satu balon "Terima kasih sudah memilih!".

### Ukuran dan posisi

- **Varian corner:** `position: fixed; right: 16px; bottom: 16px`. Lebar COGITO 120px di desktop dan 80px di HP
  (di bawah 720px).
  - Balon berada di kiri atas figur, lebar maksimal 280px (220px di HP), dengan ekor kecil ke arah COGITO.
  - `z-index` di bawah dialog dan navbar.
  - Saat ada `<dialog>` terbuka, balon disembunyikan.
- **Varian inline:** mengikuti alur halaman, tanpa `fixed`.
- Target sentuh tombol balon dan figur 44px atau lebih. Fokus terlihat. Esc pada balon sama dengan Tutup.

### Pembersihan

- Hapus `<model-viewer>`, `.mascot`, `HomeController::mascotUrl()`, `config('election.mascot_path')`, skrip
  model-viewer, dan tes 3D. Ganti dengan tes COGITO baru.

## Batasan

- Laravel Blade, CSS dan JS biasa, tanpa build. Tes di `election_vote_testing`. Satu commit per perubahan, tanpa
  push.
- AA kontras, 44px, tanpa geser horizontal di 320px, tanpa em dash.
- Ukuran aset WebP diupayakan masing-masing di bawah 250KB (GIF aslinya sekitar 240KB).

## Diserahkan ke pembangun

- Toleransi hapus latar yang tepat, serta kualitas dan ukuran WebP.
- Bentuk ekor balon dan detail animasi muncul balon (singkat, dan mati bila `prefers-reduced-motion`).
- Penamaan kunci sessionStorage.
