# Jadwal pemilihan: kartu menjadi pita lebar penuh (design, short)

Status: disetujui user 2026-11-25. Melanjutkan `2026-11-24-homepage-redesign-design.md` (palet, font, antislop DURING, data nyata saja tetap berlaku).

## Intent
Kartu "Jadwal pemilihan" yang menumpang di bawah foto hero diganti menjadi pita (banner line) lebar penuh yang menjadi garis pemisah antara hero slider dan section "Surat suara".

## Yang diputuskan user
- Pita lebar penuh tepi ke tepi, tepat di antara hero dan "Surat suara"; isi mendatar satu baris di desktop.
- Warna opsi A: latar teal `#31BDAA`, teks gelap, tombol kuning tetap.
- Isi tetap mengikuti status jadwal admin (belum diumumkan / dibuka N lagi / sedang berlangsung + "Masuk untuk memilih" / ditutup + "Lihat hasil").
- Maskot COGITO: `.glb` user rusak, nanti diganti PNG di pekerjaan terpisah. Pekerjaan ini tidak mengubah logika maskot; slot `<model-viewer>` yang ada dipindah ke ujung kanan pita.

## Approaches
- **Restyle markup slip yang ada menjadi pita full-bleed (dipilih):** isi dan logika status tidak berubah, hanya pembungkus dan CSS.
- Pita sticky/fixed di atas layar: ditolak, user meminta pita di antara dua section, bukan elemen yang mengikuti scroll.

## Desain
- **Posisi:** langsung setelah hero (tanpa tumpang tindih, tanpa bayangan), sebelum `#paslon`. Lebar 100% viewport; isi di dalam `.container`.
- **Desktop (≥1080px):** satu baris: label "Jadwal pemilihan" + status (Archivo tebal) | waktu | tombol aksi (kanan). Tablet boleh dua baris. **HP:** bertumpuk, tetap pita lebar penuh.
- **Warna:** latar `--teal`; semua teks `--ink` (6.9:1) atau `--deep` (5.7:1). `--ink-soft` dan cincin fokus `--teal-ink` tidak boleh dipakai di atas teal (3.4:1 dan 2.7:1, gagal); cincin fokus di pita memakai `--ink`.
- **Pemisah:** garis tipis `--deep` di tepi atas pita sebagai "line" terhadap foto.
- **Tidak berubah:** teks dan logika status, hero, amplop, hasil, footer.

## Non-goals
Tidak ada ticker berjalan/animasi pita; tidak mengubah maskot ke PNG (pekerjaan berikutnya).

## Diserahkan ke builder
Padding, ukuran huruf, pembagian kolom per breakpoint, selama AA, overflowX 0 di 390px, dan tombol aksi tetap terlihat tanpa scroll di 1440×900 dan 1366×768 saat pemilihan dibuka.
