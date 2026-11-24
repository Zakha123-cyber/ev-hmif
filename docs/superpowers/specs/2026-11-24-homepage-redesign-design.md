# Redesign Halaman Awal (`/`): Design Doc

Status: menunggu persetujuan, 24 Nov 2026

## Tujuan

Halaman awal dibuka mahasiswa Informatika untuk tiga hal: **kapan bisa memilih**, **siapa paslonnya dan apa visi-misinya**, dan **hasilnya** setelah ditutup. Desain baru harus terasa milik HMIF (bukan template), sesuai tema warna sekarang, dan bergaya situs pemilihan: jelas, resmi, bisa dipercaya.

**Bukan tujuan:** mengubah halaman `/vote`, login, atau panel admin; menambah fitur baru selain menampilkan jadwal yang sudah ada.
**Anti-goal:** statistik/klaim karangan, testimoni, FAQ generik, animasi dekoratif, atau apa pun yang membuat hasil suara terlihat sebelum pemilihan ditutup (aturan #2 tetap berlaku).

## Pendekatan (dipilih: A)

| | Pendekatan | Trade-off |
|---|---|---|
| **A ✔** | **Surat suara**: hero slider foto bersama + status jadwal; paslon berdampingan seperti kolom surat suara | Paslon mudah dibandingkan; identitas "pemilihan" paling kuat |
| B | Poster per paslon, berselang kiri-kanan | Dramatis, tapi membandingkan paslon harus scroll bolak-balik (dan mirip layout lama) |
| C | Papan informasi ringkas satu layar | Cepat dipindai, tapi foto bersama mengecil dan kesan perayaan hilang |

## Design Read (antislop, mode DURING)

> Halaman pemilihan organisasi untuk mahasiswa Informatika UNEJ, gaya **surat suara resmi bertema teal–kuning HMIF**, dial **ENERGY 2 / RHYTHM 3 / MOTION 2**.

- ENERGY 2: acara resmi organisasi, bukan landing produk, percaya diri tapi tidak berisik.
- RHYTHM 3: tiap bagian punya komposisi berbeda (foto penuh → kertas bergaris → blok gelap asimetris → footer).
- MOTION 2: hanya crossfade slider (bisa dijeda) dan buka/tutup misi. Tanpa fade-up per section.

## Token

**Warna** (semua diambil dari aset HMIF, bukan palet default):

| Nama | Hex | Sumber / peran |
|---|---|---|
| `teal` | `#31BDAA` | Seragam di foto & primary lama. Bidang/garis, **bukan** teks di atas putih (kontras 2,3:1) |
| `teal-ink` | `#1C6B63` | Turunan gelap teal untuk teks/link di atas putih (kontras ≥ 5,5:1) |
| `deep` | `#0E3532` | Latar gelap (scrim hero, blok hasil, footer); logo EV yang putih butuh latar gelap |
| `yellow` | `#FFE500` | Kuning logo HMIF & tulisan "ELECTION". **Aksen tunggal**: nomor paslon (heksagon) + tombol aksi utama |
| `paper` / `ink` | `#FFFFFF` / `#122423` | Kertas surat suara & tinta |

Perubahan dari tema lama: **oranye `#f48c06` dihapus** dan diganti kuning, karena oranye tidak ada di logo mana pun sedangkan kuning ada di keduanya. Teal tetap warna dominan.

**Huruf:**
- **Archivo** (variable, sumbu lebar) untuk judul dan nomor paslon. Alasan: grotesk tebal yang selaras dengan huruf "ELECTION VOTE"; sumbu lebar dipakai *expanded* untuk nomor besar seperti di surat suara.
- **Plus Jakarta Sans** untuk teks. Alasan: typeface buatan Indonesia (Tokotype), nyaman dibaca untuk visi-misi berbahasa Indonesia.
- Space Grotesk (font lama) tidak dipakai: termasuk daftar font default AI dan tidak punya alasan merek.
- Skala: 14 / 16 / 20 / 28 / 40 / 64 px; panjang baris teks ≤ 70 karakter.

**Radius:** kertas surat suara 0 (lembar kertas), slip status 6px, tombol 6px, heksagon = bentuk sendiri. Bayangan hanya pada slip status (satu-satunya elemen yang "melayang" di atas hero).

**Motif identitas:** **heksagon** dari logo HMIF, dipakai untuk bingkai nomor paslon dan tidak di tempat lain. Ditambah garis tinta 2px antar kolom surat suara.

## Layout

```
┌───────────────────────────────────────────────┐
│ [logo Election Vote]            [Masuk]       │  header di atas foto
│░░░ slider 3 foto bersama (crossfade, jeda) ░░░│
│░ Pemilihan Ketua dan Wakil Ketua Umum      ░░░│  h1 kiri-atas (area pohon,
│░ HMIF Periode 2025/2026                    ░░░│  scrim gelap → kontras aman)
│░                                [⏸ Jeda]   ░░░│
│░ ┌─ slip status (putih, garis perforasi) ─┐ ░░│  (COGITO .glb kanan bawah,
│  │ Pemilihan dibuka                         │   │   hanya jika file ada)
│  │ Senin, 1 Desember 2026, 08.00–15.00 WIB │   │
│  │ [ Masuk untuk memilih ]  (kuning)        │   │
│  └──────────────────────────────────────────┘   │
├───────────────────────────────────────────────┤
│ Surat suara                                   │  kertas putih
│ Kenali pasangan calon sebelum memilih.        │
│ Suara hanya bisa dikirim satu kali.           │
│ ┃ ⬢1          ┃ ⬢2          ┃                 │  garis tinta antar kolom
│ ┃ [poster 3:4]┃ [poster 3:4]┃                 │
│ ┃ Ketua  Nama ┃ Ketua  Nama ┃                 │
│ ┃ Wakil  Nama ┃ Wakil  Nama ┃                 │
│ ┃ Visi …      ┃ Visi …      ┃                 │
│ ┃ ▸ Misi (n)  ┃ ▸ Misi (n)  ┃  <details>      │
├───────────────────────────────────────────────┤
│ █ Hasil penghitungan  │ Paslon 1  ███████ 62  │  blok deep, asimetris
│ █ (teks status)       │ Paslon 2  ████    38  │  (bar CSS, bukan pie)
├───────────────────────────────────────────────┤
│ [logo HMIF] Himpunan Mahasiswa Informatika    │
│ Universitas Jember. Kendala login? CS Farda, │
│ CS Dewi (link WhatsApp)                       │
└───────────────────────────────────────────────┘
```

Rata kiri di semua bagian (bukan tengah), teks resmi dibaca dari kiri, dan memisahkan halaman ini dari template "semua di tengah". Mobile: kolom surat suara bertumpuk, slip status penuh lebar di bawah foto (tidak menutupi wajah di foto).

## Konten per keadaan (data nyata saja)

| Jadwal | Slip status | Bagian hasil |
|---|---|---|
| Belum diatur | "Jadwal pemilihan belum diumumkan panitia." Tombol `Masuk` biasa | "Hasil tampil setelah pemilihan ditutup." |
| Belum dibuka | "Pemilihan dibuka" + hari, tanggal, jam buka–tutup + "dalam N hari/jam" | sama |
| Dibuka | "Pemilihan sedang berlangsung, ditutup pukul HH.MM WIB." Tombol kuning `Masuk untuk memilih` (peserta login: `Buka surat suara` → `/vote`) | sama |
| Ditutup | "Pemilihan ditutup <tanggal jam>." Tombol `Lihat hasil` → `#hasil` | bar per paslon: jumlah suara + persen + total suara masuk; 0 suara → "Belum ada suara tercatat." |

- Tidak ada paslon → "Daftar pasangan calon belum diumumkan."
- Data hasil **tetap hanya dikirim ke view saat ditutup** (perilaku #2 dipertahankan, test disesuaikan).
- Kontak CS diambil dari template email yang sudah ada.

## Aset

- 3 foto bersama → WebP `1200w` & `2400w` (srcset) di `public/frontend/images/home/`. Foto pertama = `<img fetchpriority="high">`, bukan background CSS.
- Logo HMIF → WebP 320px. Logo Election Vote dipakai apa adanya di header.
- **COGITO**: Anda ekspor ke `public/frontend/3d/cogito.glb`. Ditampilkan dengan `<model-viewer>` (diputar oleh pengguna, **tanpa** auto-rotate). Jika file belum ada, elemen & script-nya tidak dirender sama sekali.

## Teknis

- `welcome.blade.php` ditulis ulang + `public/frontend/css/home.css` baru; tidak lagi memuat Bootstrap 3, jQuery, owl, animate, modernizr, Chart.js di halaman ini. Halaman lain tidak tersentuh.
- `HomeController` menyiapkan: status jadwal, teks tanggal (locale `id`), paslon + misi yang sudah dipecah (buang item kosong), dan `results` hanya saat ditutup.
- Aksesibilitas: landmark `header/main/footer`, satu `h1`, fokus terlihat, tombol jeda slider (wajib karena gerak > 5 detik), `prefers-reduced-motion` → slider diam, tap target ≥ 44px.
- Test: perbarui `ResultsVisibilityTest`; tambah test status per keadaan, empty state paslon, dan model-viewer hanya muncul jika `.glb` ada.

## Review terhadap pola AI default (frontend-design & antislop)

- Kertas surat suara dengan garis tinta + radius 0 bisa terbaca seperti pola "broadsheet garis tipis". **Direvisi:** garis 2px (bukan hairline), hanya pada surat suara (konten memang surat suara), radius dipakai pada slip & tombol sebagai hierarki.
- Ide awal "label kapital kecil di atas setiap judul" (mis. `PASLON 01`) **dibuang**: nomor sudah ada di heksagon.
- Ide awal "statistik pemilih terdaftar" di hero **dibuang**: tidak ada data pemilih terdaftar yang bisa dipertanggungjawabkan sebelum broadcast.
- Pie chart lama **diganti** bar berlabel langsung: lebih mudah dibaca dan tidak butuh legenda warna.

## Diserahkan ke pembuat (keputusan saya)

Ukuran pasti hero (≈ 88svh desktop, 62svh mobile), durasi crossfade (7 detik/foto, transisi 1,2 detik), teks persis di slip, dan susunan footer.
