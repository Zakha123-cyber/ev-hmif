# Surat suara: amplop paslon + modal surat (design, short)

Status: disetujui user 2026-11-25. Melanjutkan `2026-11-24-homepage-redesign-design.md`; semua aturan di sana (palet, font, antislop DURING, data nyata saja) tetap berlaku.

## Intent
Section `#paslon` dibuat lebih bernuansa pemilu: paslon tampil sebagai amplop tertutup, isinya dibaca saat amplop dibuka, dalam modal berbentuk surat resmi.

## Yang diputuskan user
- Judul "Surat suara" dan deskripsinya rata tengah.
- Di bawahnya grid amplop, satu per paslon. Depan amplop **hanya** bertuliskan "Paslon N" plus segel heksagon bernomor (opsi A): nama calon tidak tampil di amplop.
- Klik amplop membuka modal berdesain surat yang menampilkan **foto/poster paslon** beserta info paslon.
- Tidak ada tombol untuk mematikan animasi. Animasi selalu jalan; satu-satunya pengecualian adalah pengaturan OS `prefers-reduced-motion` (aksesibilitas), yang sudah dihormati halaman ini.

## Approaches
- **`<dialog>` native + showModal (dipilih):** Esc, focus trap, backdrop, dan semantik modal dari browser; JS kecil di home.js.
- Modal `:target` (link `#paslon-1`): ditolak, mengotori URL/Back dan tidak mengunci fokus.
- Expand di tempat (`<details>`): ditolak, bukan modal seperti permintaan user.

## Desain
- **Intro:** h2 + paragraf rata tengah, lebar baca maks ~60ch. Teks: "Buka amplop untuk mengenal tiap pasangan calon. Suara hanya bisa dikirim satu kali dan tidak dapat diubah."
- **Amplop (`<button>`):** kertas putih, garis tutup amplop (segitiga) berwarna teal-ink, segel heksagon kuning bernomor di ujung tutup, tulisan besar "Paslon N" (Archivo), petunjuk kecil "Buka amplop". Nama aksesibel: "Buka amplop pasangan calon nomor N". Grid: 1 kolom di HP, 2 kolom ≥720px, 3 kolom ≥1080px bila paslon 3. Jumlah amplop mengikuti data.
- **Gerak (MOTION 2):** hover/focus → tutup amplop sedikit terangkat; klik → tutup terbuka lalu surat naik keluar (~300ms total) baru modal tampil. `prefers-reduced-motion: reduce` → tanpa transform, modal langsung tampil.
- **Surat (`<dialog>` per paslon):** kop surat (logo HMIF + "Himpunan Mahasiswa Informatika" + garis ganda), judul "Pasangan Calon Nomor Urut N", poster (atau placeholder "Foto pasangan calon belum tersedia"), dl Ketua / Wakil ketua, Visi, Misi sebagai `<ol>` terbuka penuh (dari `Candidate::missionItems()`). Tombol "Tutup" di atas dan bawah; Esc dan klik backdrop menutup; fokus kembali ke amplop. Maks 90svh, gulir di dalam; HP lebar penuh dengan gutter 16px. Scroll halaman dikunci saat modal terbuka.
- **Tanpa JS:** tombol amplop memakai `commandfor`/`command="show-modal"` sehingga browser modern tetap bisa membuka modal walau home.js gagal (keputusan builder).
- **Tidak berubah:** hero, slip, hasil penghitungan, footer, empty state "Daftar pasangan calon belum diumumkan."

## Non-goals / anti-goals
- Bukan efek 3D berat atau animasi panjang; bukan tombol "matikan animasi".
- Tidak menambah data karangan (tanda tangan palsu, nomor surat palsu).

## Diserahkan ke builder
Ukuran/proporsi amplop, detail kop surat, easing, dan susunan CSS, selama lolos WCAG AA, overflowX 0 di 390px, dan fokus terlihat.
