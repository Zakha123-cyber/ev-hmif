# Halaman /vote: surat suara yang dicoblos

Tanggal: 2026-11-27. Status: disetujui pengguna 2026-11-27.

## Maksud

`/vote` adalah tempat peserta benar-benar memberikan suara. Sekarang ketiga tampilannya (dibuka, belum dibuka,
ditutup) masih Bootstrap 4 lama dengan konfirmasi SweetAlert, dan tidak menyambung dengan halaman awal yang
baru (surat suara, amplop, motif heksagon). Pengguna ingin halaman ini dipercantik dengan memanfaatkan skill
frontend-design dan antislop.

Yang dipilih pengguna: **mencoblos surat suara**, yaitu ketuk kolom paslon sampai muncul bekas coblosan,
lalu konfirmasi bergaya surat. Setelah terkirim, surat dilipat dan masuk kotak suara, lalu tampil tanda terima.

## Tujuan

- Ketiga tampilan `/vote` memakai bahasa visual halaman awal: warna, huruf, navbar, heksagon, dan tombol.
- Memilih terasa seperti mencoblos surat suara, tapi tetap jelas dan aman:
  - pilihan masih bisa diganti sebelum dikirim;
  - ada konfirmasi sebelum suara dikirim;
  - suara tidak bisa terkirim dua kali.
- Setelah memilih, ada tanda terima yang menenangkan: suara sudah tercatat beserta waktunya, dan kapan hasil
  diumumkan.

## Bukan tujuan

- Mengubah aturan memilih di server (validasi, kunci baris, jadwal). Satu-satunya perubahan di controller
  adalah data tambahan untuk tampilan (lihat Rancangan).
- Halaman login dan panel admin.

## Anti-tujuan

- Tanda terima **tidak menyebut paslon yang dipilih**. Suara bersifat rahasia, dan layar HP mudah terlihat
  orang lain.
- Tidak ada tombol "matikan animasi". Gerak hanya berhenti lewat pengaturan sistem `prefers-reduced-motion`
  (keputusan pengguna sebelumnya).
- Animasi tidak boleh menghalangi suara terkirim. Kalau JS mati, form tetap terkirim langsung.

## Pendekatan yang dipertimbangkan

- **A. Mencoblos surat suara (dipilih).** Paling menyambung dengan tema amplop dan surat, dan terasa seperti
  pemilu sungguhan. Kerjanya paling banyak, dan animasinya perlu diuji di HP.
- **B. Kartu paslon dengan tombol "Pilih" dan dialog konfirmasi sendiri.** Lebih cepat dibuat, tapi kurang
  berkarakter.

## Rancangan

### Kerangka bersama

- Satu layout Blade untuk ketiga tampilan, berisi:
  - `<head>`: font Archivo + Plus Jakarta Sans, `home.css`, dan `vote.css` baru;
  - navbar seperti halaman awal tetapi **selalu solid** (tidak ada foto di belakangnya), dengan tombol
    "Keluar";
  - footer yang sama dengan halaman awal (kontak panitia).
- Bootstrap, jQuery, SweetAlert, `animation-custom.css`, dan `vote.css` lama tidak dipakai lagi. `vote.css`
  ditulis ulang. Kedua file CSS lama hanya dipakai oleh tiga view ini.
- Periode tidak lagi diketik di view, tetapi diambil dari `config('election.period')` = `2025/2026`. Ini
  sekaligus membereskan tulisan "2024/2025" yang keliru. Halaman awal tidak diubah.

### Dibuka, belum memilih

```
  SURAT SUARA                                   (judul + periode)
  Halo, <nama>. Ketuk foto paslon pilihanmu, lalu kirim.

 ┌──────────────────────────────────────────────────────┐
 │ [logo HMIF]  SURAT SUARA                             │  ← kop
 │              Pemilihan Ketua & Wakil Ketua HMIF      │
 ├──────────────────────────┬───────────────────────────┤
 │        ⬡ 1               │         ⬡ 2               │
 │   ┌──────────────┐       │    ┌──────────────┐       │
 │   │   poster     │       │    │   poster ✸   │ ← bekas coblos
 │   └──────────────┘       │    └──────────────┘       │
 │   Nama ketua             │    Nama ketua             │
 │   Nama wakil             │    Nama wakil             │
 │   [ Visi & misi ]        │    [ Visi & misi ]        │
 └──────────────────────────┴───────────────────────────┘
              [ Kirim suara ]  (kuning)
```

- Satu `<form>` berisi `<input type="radio" name="candidate_id">` per paslon di dalam `<label>` kolom.
  Radio disembunyikan secara visual tetapi tetap bisa difokus. Keyboard (Tab dan panah) serta pembaca layar
  bekerja dengan sendirinya.
- Kolom yang dipilih mendapat bekas coblosan di atas poster dan garis tepi tebal. Ganti pilihan cukup dengan
  mengetuk kolom lain. Di HP kolom ditumpuk ke bawah.
- "Kirim suara" tanpa pilihan memunculkan pesan di bawah tombol: "Pilih salah satu paslon dulu." Tombol tidak
  dibuat disabled. Validasi server tetap ada.
- Informasi paslon (permintaan pengguna 2026-11-27): tiap kolom punya tombol "Visi & misi" di bawah nama,
  **di luar** `<label>`, sehingga mengetuknya tidak ikut mencoblos. Tombol membuka surat paslon yang sama
  dengan halaman awal (kop, poster, nama, visi, misi bernomor, "Tutup"). Markup surat dipindah ke partial
  Blade bersama, dipakai halaman awal dan `/vote`. Perilaku dialognya juga sama: tutup lewat latar, fokus
  kembali ke tombol. Alasan: pemilih bisa menimbang tanpa meninggalkan surat suara, dan surat suara tetap
  ringkas sehingga tombol "Kirim suara" tidak terdorong jauh ke bawah.
- Dialog konfirmasi berupa `<dialog>` bergaya kertas:
  - judul "Kirim suara untuk Paslon N?";
  - nama pasangan;
  - catatan "Setelah dikirim, pilihanmu tidak bisa diubah.";
  - tombol "Ya, kirim suara" (kuning) dan "Periksa lagi".
- Setelah dikonfirmasi:
  - tombol berubah menjadi "Mengirim…", dan klik berikutnya diabaikan;
  - surat dilipat dan turun ke celah kotak suara (sekitar 0,8 detik), lalu form dikirim;
  - dengan `prefers-reduced-motion`, form langsung dikirim tanpa animasi.

### Sudah memilih (dibuka atau ditutup)

```
        ┌─────────┐
        │ ▄▄▄▄▄▄▄ │  ← kotak suara (CSS), surat terlipat di celah
        └─────────┘
   Suaramu sudah tercatat
   Kamis, 27 November 2026 pukul 14.05 WIB
   Hasil diumumkan setelah pemilihan ditutup <tanggal>.   / [Lihat hasil] bila sudah ditutup
   [Kembali ke halaman awal]
```

- Waktu diambil dari `counts.created_at` milik user. Controller mengirim `votedAt`. Paslon yang dipilih tidak
  ditampilkan.
- Tepat setelah mengirim, controller menambahkan flash `just_voted`. Tanda terima lalu memutar animasi surat
  masuk kotak satu kali. Kunjungan berikutnya tampil diam.
- Kasus lama: status `voted` tetapi tanpa baris `counts`, misalnya admin. Waktu tidak ditampilkan, dan tidak
  terjadi error.

### Belum dibuka atau jadwal belum ada

- Judul "Pemilihan belum dibuka", tanggal buka, dan hitung mundur hex yang sama dengan pita jadwal di halaman
  awal (memakai ulang markup `data-countdown` dan JS hitung mundur di `home.js`). Saat waktunya tiba, halaman
  dimuat ulang dan surat suara muncul.
- Bila jadwal belum ada: "Jadwal belum diumumkan".
- Di bawahnya, bagian "Kenali paslon" berisi amplop yang sama dengan halaman awal (memakai ulang komponen
  amplop dan surat), supaya pemilih bisa membaca visi dan misi sambil menunggu.

### Ditutup, tidak memilih

- "Pemilihan sudah ditutup" dan "Kamu tidak memberikan suara pada pemilihan ini.", dengan tombol "Lihat hasil"
  ke `/#hasil`.

## Batasan

- Laravel 10 Blade, CSS dan JS biasa tanpa build step, sama seperti halaman awal.
- Kontras AA, target sentuh 44px atau lebih, tanpa geser horizontal di lebar 320px.
- Tes Pest hanya memakai DB `election_vote_testing`. Satu commit per perubahan, tidak di-push.
- Tes yang sudah ada (`assertViewIs('vote_page' | 'vote_prepare' | 'vote_closed')`) tetap lolos. Nama view
  tidak diganti.

## Diserahkan ke pembangun

- Bentuk persis bekas coblosan (SVG lubang bergerigi atau bintang) dan kotak suara CSS.
- Durasi dan easing animasi lipat (sekitar 0,8 detik).
- Penamaan partial atau layout Blade.
