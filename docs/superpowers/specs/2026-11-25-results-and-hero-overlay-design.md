# Hasil penghitungan terang + overlay hero lebih tipis (design, short)

Status: disetujui user 2026-11-25 ("oke silahkan"). Melanjutkan `2026-11-24-homepage-redesign-design.md`.

## Intent
- Section "Hasil penghitungan" terlalu mirip footer (keduanya `--deep`).
- Overlay teal di foto hero terlalu tebal sehingga foto tertutup.

## Keputusan
- **Hasil:** latar teal sangat muda `#E8F4F2` (beda dari "Surat suara" putih dan footer gelap); judul + "Total N suara masuk." rata tengah; papan putih (garis atas teal 6px) berisi baris per paslon: segel heksagon kuning bernomor (sama dengan amplop), "Paslon N" + nama, persentase besar + jumlah suara, bar `--teal-ink` di atas trek `#CFE3E0` (teal biasa hanya 2.1:1 di latar terang). Pesan sebelum ditutup / tanpa suara di kotak putih bergaris putus-putus. **Tanpa penanda pemenang** (pilihan user; penetapan resmi urusan panitia).
- **Overlay hero:** bukan lagi lapisan gelap selebar layar setinggi 300px (84-92%). Desktop/tablet: gelap 74→66% hanya di blok judul kiri atas, memudar ke kanan dan ke bawah (mask), plus gradasi tipis 40% di atas untuk logo/menu. HP: gradasi dari atas 74→66% sampai 250px. Latar tombol "Masuk" di atas foto 55%→70%.
- Kontras terukur (teks putih vs foto+overlay, 3 foto, 1920x870/1440x900/1366x657/390x844): judul & periode min 4.91; tombol Masuk min 4.71 sebelum latarnya dipertebal.

## Revisi: judul hero di tengah (user: "terlalu keatas, coba buat center")
- Tablet & desktop (≥720px): judul ditengahkan vertikal di area foto (antara bawah header 84px dan bawah hero), tetap rata kiri; tombol "Jeda foto" absolut di pojok kanan bawah. HP tetap di atas (tengah foto HP berisi barisan orang).
- Overlay ikut pindah: elips gelap 900x400 berpusat di belakang judul (74→66→30→0%) + gradasi tipis 40% di atas. Kontras terukur min 5.18 (1920x870, 1440x900, 1366x657, 390x844).

## Revisi: navbar tetap & tanpa tombol jeda (permintaan user)
- Navbar `position: fixed`: transparan di atas foto saat di puncak halaman, solid `--deep` dan lebih ringkas (logo 40px) begitu digulir > 8px. Tanpa JS selalu solid (kelas `js` dipasang di `<head>` sebelum render). `scroll-padding-top: 72px` agar #paslon/#hasil tidak tertutup.
- Tombol "Jeda foto" dihapus atas keputusan user, meski WCAG 2.2.2 meminta kontrol jeda untuk gerak otomatis > 5 detik. Slider tetap tidak berjalan dengan `prefers-reduced-motion` dan berhenti saat tab tersembunyi.
