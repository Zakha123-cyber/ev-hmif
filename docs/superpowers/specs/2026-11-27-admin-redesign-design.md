# Redesign panel admin

Tanggal: 2026-11-27. Status: disetujui pengguna 2026-11-27.

## Maksud

Panel admin dipakai panitia, terutama dari laptop, untuk menyiapkan dan memantau pemilihan: jadwal dan periode, paslon,
peserta, verifikasi akun, dan hasil suara. Pengguna minta panel ini "sebagus mungkin tapi tetap gampang dibaca, mudah
digunakan dan dipahami", responsif, dan setiap fitur diuji agar tidak ada yang rusak.

Kondisi sekarang:
- Font Awesome **Pro** (kit berlisensi), sehingga banyak tombol yang hanya berupa ikon kemungkinan tampil kosong.
- Pustaka tumpang tindih: jQuery dua versi, DataTables, Flowbite, Selectize, ApexCharts, Chart.js.
- Bahasa campur Inggris dan Indonesia.
- Data palsu: kartu "Halaman Dilihat" dan "Pengunjung Baru" berisi angka acak.
- Beberapa bug (lihat "Perbaikan yang ikut").

## Tujuan

- Satu kerangka admin baru dengan bahasa visual situs publik, tetapi lebih tenang karena ini alat kerja.
  - Warna: teal #31BDAA, hijau tua #0E3532, teal-ink #1C6B63, kuning hanya untuk aksi utama.
  - Huruf: Archivo untuk judul, Plus Jakarta Sans untuk teks.
- Semua teks berbahasa Indonesia.
- Setiap tombol aksi punya teks yang jelas ("Ubah", "Kirim ulang email", "Hapus"), bukan hanya ikon.
- Responsif:
  - Laptop menjadi prioritas.
  - Di bawah 1024px, sidebar menjadi laci yang dibuka dengan tombol Menu.
  - Di bawah 720px, baris tabel menjadi kartu bertumpuk.
- Tanpa jQuery, DataTables, Flowbite, Selectize, ApexCharts, Chart.js, maupun Font Awesome. Cukup CSS dan JS biasa
  (`admin.css`, `admin.js`) dengan ikon SVG inline buatan sendiri.
- Semua fitur, URL, method, nama field, dan validasi tetap sama.

## Bukan tujuan

- Mengubah alur bisnis, route, atau aturan validasi (kecuali bug yang disebut di bawah).
- Halaman baru atau fitur baru di luar yang sudah ada. Satu pengecualian: tombol "Acak ulang" username/password di
  halaman Verifikasi, memakai endpoint yang sudah ada.

## Anti-tujuan

- Data palsu: kartu trafik acak dihapus, tidak diganti angka karangan.
- Tombol berupa ikon saja tanpa nama.
- Aksi berbahaya tanpa konfirmasi: hapus, dan kirim email massal.
- Password terlihat tanpa diminta.

## Pendekatan

- **A. Reskin:** menimpa tampilan lewat CSS tanpa mengubah markup Tailwind lama. Cepat, tapi tetap membawa pustaka lama,
  ikon kosong, dan tabel DataTables. Ditolak.
- **B. Bangun ulang semua view admin (dipilih).** Menggunakan komponen sendiri, dan route/controller tetap. Paling rapi dan
  ringan. Risikonya fitur hilang, yang ditangkal oleh inventaris lengkap plus tes server dan uji klik di browser.

## Rancangan

### Kerangka

```
┌──────────────┬───────────────────────────────────────────────┐
│ [logo] Panel │  Judul halaman            ● Sedang dibuka     │ ← topbar: status pemilihan, nama admin, Keluar
│ admin 25/26  ├───────────────────────────────────────────────┤
│              │  [pesan sukses/galat yang bisa ditutup]       │
│ Ringkasan    │                                               │
│ PERSIAPAN    │  isi halaman                                  │
│  Jadwal      │                                               │
│  Paslon      │                                               │
│  Peserta     │                                               │
│  Verifikasi  │                                               │
│ PEMILIHAN    │                                               │
│  Hasil suara │                                               │
└──────────────┴───────────────────────────────────────────────┘
```

- **Sidebar:** hijau tua, berisi logo HMIF, "Panel admin", dan periode (`periodLabel()`).
- **Menu:**
  - Ringkasan
  - Persiapan: Jadwal & periode, Paslon, Peserta, Verifikasi akun
  - Pemilihan: Hasil suara
  - Menu aktif ditandai dengan latar dan `aria-current="page"`, berdasarkan segment URL seperti sekarang.
- **Topbar:**
  - judul halaman (`@section('title')`);
  - chip status pemilihan (belum diatur, belum dibuka, sedang dibuka, sudah ditutup) yang menautkan ke halaman Jadwal;
  - nama admin (`details?->participant_name ?? username`, tidak error bila admin tanpa data peserta);
  - tombol "Keluar" (form POST logout);
  - tautan "Lihat situs".
- **Pesan flash:** `session('status')` (sukses) dan `session('error')` (galat).
  - Keduanya bisa tampil bersamaan.
  - Teksnya tetap utuh. Ada tes yang membaca teks ini.
  - Bisa ditutup.
  - Galat memakai `role="alert"`.

### Komponen (`public/frontend/css/admin.css`, `public/frontend/js/admin.js`)

- **Kartu dan judul seksi.**
- **Tombol:**
  - utama (teal-ink, teks putih);
  - aksen (kuning; hanya untuk satu aksi terpenting per halaman, misalnya Simpan);
  - netral;
  - bahaya (merah).
  - Tinggi 44px atau lebih, fokus terlihat.
- **Field:**
  - label terlihat, petunjuk, dan pesan galat per field (`@error`);
  - field yang salah diberi `aria-invalid` dan garis merah;
  - semua nilai memakai `old()`.
- **Label status (badge):**
  - Terverifikasi (hijau) / Belum terverifikasi (kuning tua).
  - Sudah memilih / Belum memilih.
  - Status jadwal.
- **Tabel:**
  - Kotak pencarian yang menyaring baris secara langsung, dengan teks "Menampilkan X dari Y".
  - Pengurutan lewat tombol di judul kolom, dengan `aria-sort`.
  - Di bawah 720px, setiap baris menjadi kartu dengan label kolom (`data-label`).
- **Konfirmasi:** form dengan `data-confirm="..."` membuka `<dialog>` konfirmasi bergaya dengan tombol "Ya, lanjutkan" /
  "Batal". Tanpa dukungan dialog, form memakai `confirm()` bawaan.
- **Dialog impor Excel:** `<dialog>` dengan tombol Batal `type="button"` dan galat `file` yang terlihat.
- **Unggah gambar:** area pilih file berlabel, nama file, dan pratinjau. Berlaku untuk poster paslon dan foto peserta.

### Halaman

| Halaman | Isi |
|---|---|
| Ringkasan | Kartu angka nyata: peserta, sudah memilih, belum memilih, paslon, admin. Bilah partisipasi (sudah memilih / total). Kartu status jadwal + tautan. Hasil sementara per paslon berupa bilah CSS (bukan Chart.js). |
| Hasil suara | Bilah per paslon (jumlah + persen) dan partisipasi. Paslon dengan 0 suara tetap tampil. |
| Paslon | Kartu per paslon: poster, nomor heksagon, nama, visi, misi (daftar bernomor dari `missionItems()`, di-escape), tombol Ubah dan Hapus (dengan konfirmasi). Kosong: ajakan menambah paslon. |
| Tambah/ubah paslon | Form satu kolom; pratinjau poster di samping (desktop). |
| Peserta | Toolbar: Tambah peserta, Impor Excel, Buat akun & kirim email (konfirmasi). Tabel: Nama, NIM, WhatsApp, Status, Aksi (Ubah, Kirim ulang email, Hapus). |
| Tambah/ubah peserta | Form; status pakai `old()`; WhatsApp `old('whatsapp_number', '62')`; judul halaman ubah benar. |
| Verifikasi akun | Kartu "Buat akun peserta": cari peserta (input penyaring + `<select name="nim">` berisi id peserta), username & password (readonly) dengan tombol Acak ulang, WhatsApp. Tabel peserta terverifikasi dengan aksi Ubah akun dan Info akun. |
| Ubah akun | Username/password dengan tombol Acak ulang (`fetch` ke `/back-admin-logged/generate-username` dan `generate-password`, string URL harus tampil apa adanya), tombol tampilkan password, WhatsApp, status. |
| Info akun | Detail akun; password tersembunyi dengan tombol Tampilkan; tautan Kirim WhatsApp dan Cetak kartu. |
| Cetak (PDF) | Tetap DomPDF, CSS inline. Periode dari `periodLabel()` (bukan "2022/2023"). |
| Jadwal & periode | Form periode + waktu buka/tutup; chip status. |

### Perbaikan yang ikut

1. Ringkasan dan Hasil suara mengurangi `- 2` di atas `ElectionStats`, yang sudah mengecualikan admin, sehingga angka
   bisa salah atau negatif. Pengurangan itu dihapus.
2. Sapaan ringkasan memakai `Auth::user()->name`, yang tidak ada sehingga kosong. Diganti nama admin.
3. Misi paslon dirender tanpa escape (`{!! !!}`, celah XSS). Diganti escape plus daftar dari `missionItems()`.
4. Tombol Batal di dialog impor ikut mengirim form, dan galat `file` tidak tampil.
5. WhatsApp dan status di form tambah peserta tidak memakai `old()`.
6. Judul halaman ubah peserta tertulis "Tambah".
7. Pesan sukses saat mengubah data tertulis "Berhasil menambah data.". Diganti "Berhasil memperbarui data." (paslon
   dan peserta).
8. Teks WhatsApp dan catatan PDF memakai tahun "2022/2023". Diganti `periodLabel()`.
9. Pratinjau foto default peserta menunjuk `participant/default.png`, padahal default tersimpan `avatar.png`.
10. Status pada ubah verifikasi tidak divalidasi. Ditambah `in:verified,not verified`.

### Batasan dari tes yang ada

- Nama view `admin.pages.election-schedule.edit` tetap dipakai (`assertViewIs`). Variabel `countVoted`, `countNotVoted`,
  dan `countParticipant` tetap ada di ringkasan dan hasil suara.
- Halaman ubah dan info dibuka lewat **POST**, jadi tombolnya tetap form dengan `@csrf`.
- Field `nim` di verifikasi berisi **id peserta**.
- String `/back-admin-logged/generate-username` dan `/back-admin-logged/generate-password` harus tampil di halaman ubah
  verifikasi, dan `/generateNew` tidak boleh.
- Teks flash `status` dan `error` dirender utuh.

## Diserahkan ke pembangun

- Bentuk ikon SVG, jarak, dan detail tipografi.
- Bentuk ringkasan di HP.
- Penamaan kelas CSS.
