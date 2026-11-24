# Redesign Halaman Awal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tulis ulang halaman `/` menjadi halaman pemilihan bergaya surat suara bertema teal–kuning HMIF, hanya dengan data nyata.

**Architecture:** `HomeController` menyiapkan view model (status jadwal, paslon + misi, hasil hanya saat ditutup, URL maskot bila ada). `welcome.blade.php` ditulis ulang tanpa Bootstrap/jQuery/Chart.js, dengan stylesheet baru `public/frontend/css/home.css` dan satu script kecil untuk slider. Aset foto/logo dikonversi ke WebP dan di-commit.

**Tech Stack:** Laravel 10, Blade, Pest, CSS murni (custom properties), JS murni, Google Fonts (Archivo, Plus Jakarta Sans), `<model-viewer>` (hanya jika `cogito.glb` ada), ImageMagick untuk konversi aset, Chrome headless untuk screenshot.

**Spec:** `docs/superpowers/specs/2026-11-24-homepage-redesign-design.md`

## Global Constraints

- Warna: `teal #31BDAA` (bidang/garis, bukan teks di atas putih), `teal-ink #1C6B63`, `deep #0E3532`, `yellow #FFE500` (hanya nomor heksagon + tombol aksi utama), `paper #FFFFFF`, `ink #122423`. Oranye `#f48c06` tidak dipakai.
- Huruf: Archivo (judul, nomor paslon, lebar expanded untuk nomor), Plus Jakarta Sans (teks). Skala 14/16/20/28/40/64 px. Baris teks ≤ 70 karakter.
- Radius: surat suara 0, slip status & tombol 6px. Bayangan hanya pada slip status.
- Teks UI: bahasa Indonesia, tanpa em dash (`—`), tanpa middle dot sebagai pemisah meta, tanpa label kapital ber-tracking, tanpa panah dekoratif, tanpa emoji.
- Data hasil hanya dikirim ke view saat status jadwal `closed` (perilaku bug #2).
- Gerak: hanya crossfade slider (7 dtk/foto, transisi 1,2 dtk) dengan tombol Jeda/Putar, dan `<details>` misi. `prefers-reduced-motion: reduce` → slider tidak berganti otomatis.
- Aksesibilitas: `<html lang="id">`, landmark `header/main/footer`, satu `h1`, fokus terlihat, tap target ≥ 44px, `cursor: pointer` pada kontrol, semua `<img>` punya `width`/`height` + `alt`, foto hero pertama `fetchpriority="high"` tidak lazy.
- Halaman lain (`/vote`, login, admin) tidak diubah.

## Review Focus

1. Misi berisi singkatan/titik ganda/spasi (mis. "S.Kom", "..", akhiran spasi) → tidak ada butir kosong. Test di Task 1 (`missionItems`).
2. Poster tidak ada di disk (`default.png` memang tidak ada) → tampil placeholder heksagon bernomor, bukan gambar rusak. Test di Task 3.
3. Pemilihan ditutup tapi 0 suara → tidak ada pembagian nol, tampil "Belum ada suara tercatat." Test di Task 1.
4. Pengguna login sebagai admin membuka `/` → tombol aksi menuju `/back-admin-logged/redirect-role` (bukan `/vote` yang 403 untuk admin). Test di Task 3.
5. 1 atau 3 paslon, nama/visi panjang, layar 390px → tidak ada overflow horizontal. Cek screenshot di Task 4.

---

## File Structure

- Modify `app/Models/Candidate.php`: `missionItems()`.
- Modify `app/Http/Controllers/HomeController.php`: view model.
- Rewrite `resources/views/welcome.blade.php`.
- Create `public/frontend/css/home.css`, `public/frontend/js/home.js`.
- Create `public/frontend/images/home/` (`foto-{1,2,3}-{1200,2400}.webp`, `logo-hmif-320.webp`).
- Create `public/frontend/3d/.gitkeep` (tempat `cogito.glb`).
- Tests: `tests/Feature/HomePageTest.php` (baru), `tests/Feature/ResultsVisibilityTest.php` (disesuaikan), `tests/Unit/CandidateMissionTest.php` (baru).

---

### Task 1: View model halaman awal

**Files:**
- Modify: `app/Models/Candidate.php`, `app/Http/Controllers/HomeController.php`
- Test: `tests/Unit/CandidateMissionTest.php`, `tests/Feature/HomePageTest.php`, `tests/Feature/ResultsVisibilityTest.php`

**Interfaces:**
- Produces: `Candidate::missionItems(): array<string>`; view vars `status` (string, konstanta `ElectionSetting::STATUS_*`), `setting` (ElectionSetting), `candidates` (Collection<Candidate> urut `candidate_pair`), `results` (`null` jika belum ditutup, selain itu `array<array{candidate: Candidate, votes: int, percent: int}>`), `totalVotes` (`int|null`), `mascotUrl` (`string|null`).

- [ ] **Step 1: Tulis test unit `missionItems`** (`uses(Tests\TestCase::class)` agar model bisa dibuat tanpa DB): `"Satu. Dua.  . Tiga"` → `['Satu.', 'Dua.', 'Tiga.']`; `"Lulusan S.Kom. Dua"` tetap dipecah per `'. '` sehingga hasilnya `['Lulusan S.Kom.', 'Dua.']`; string kosong → `[]`.
- [ ] **Step 2: Tulis test feature** di `HomePageTest`: `assertViewHas('status', 'not_set'|'upcoming'|'open'|'closed')` untuk tiap keadaan jadwal; `candidates` urut menurut `candidate_pair` (buat paslon 2 lalu 1); saat ditutup dengan suara 3:1 → `results[0]['votes'] === 3`, `percent === 75`, `totalVotes === 4`; ditutup tanpa suara → `totalVotes === 0` dan semua `percent === 0`; `mascotUrl` null jika `public_path('frontend/3d/cogito.glb')` tidak ada.
- [ ] **Step 3: Sesuaikan `ResultsVisibilityTest`**: tiga test "hides" memakai `assertViewHas('results', null)` + `assertDontSee('3 suara')`; test "shows" memakai `assertSee('3 suara')`. (Format "N suara" ditetapkan di Task 3; sementara test "shows" boleh merah sampai Task 3.)
- [ ] **Step 4: Jalankan, pastikan gagal**: `php artisan test tests/Unit/CandidateMissionTest.php tests/Feature/HomePageTest.php` → FAIL (method/var belum ada).
- [ ] **Step 5: Implementasi** `missionItems()` (pecah dengan regex `/\.\s+/` lalu `trim`, buang kosong, tambahkan titik di akhir tiap butir) dan `HomeController::index()` sesuai Interfaces. Persen dibulatkan `round()`; jika `totalVotes === 0` semua persen 0. `mascotUrl = asset('frontend/3d/cogito.glb')` hanya jika file ada.
- [ ] **Step 6: Jalankan Task 1 test** → PASS (kecuali test "shows" di `ResultsVisibilityTest` yang menunggu Task 3).
- [ ] **Step 7: Commit** `feat(home): prepare schedule, candidates and results for the new homepage`.

### Task 2: Aset gambar

**Files:**
- Create: `public/frontend/images/home/foto-{1,2,3}-{1200,2400}.webp`, `public/frontend/images/home/logo-hmif-320.webp`, `public/frontend/3d/.gitkeep`

- [ ] **Step 1: Konversi** dari `D:\laragon\www\election-vote-hmif\FOTOBERSAMA{1,2,3}.JPG` dengan `magick <src> -auto-orient -strip -resize {W}x -quality 72 <dst>.webp`, dan logo `HMIF LOGO (1).png` → 320px WebP (alpha dipertahankan).
- [ ] **Step 2: Verifikasi**: `magick identify` menunjukkan lebar 1200/2400 (rasio 6000:3376 → tinggi 675/1350), logo 320×320; tiap foto 2400w < 600 KB.
- [ ] **Step 3: Commit** `chore(home): add optimized group photos and HMIF logo`.

### Task 3: Halaman baru (Blade + CSS + JS)

**Files:**
- Rewrite: `resources/views/welcome.blade.php`
- Create: `public/frontend/css/home.css`, `public/frontend/js/home.js`
- Test: `tests/Feature/HomePageTest.php`

**Interfaces:**
- Consumes: view vars dari Task 1.
- Produces: anchor `#paslon`, `#hasil`; tombol slider `[data-slider-toggle]` dengan `aria-pressed`.

- [ ] **Step 1: Tulis test** (tambahkan ke `HomePageTest`):
  - not_set → `assertSee('Jadwal pemilihan belum diumumkan panitia.')`.
  - upcoming (jadwal 1 Des 2026 08.00–15.00, sekarang 30 Nov) → `assertSee('Senin, 1 Desember 2026')`, `assertSee('08.00')`, `assertSee('15.00 WIB')`; dengan sekarang 30 Nov 08.00 → `assertSee('1 hari lagi')` (teks "Dibuka {diff} lagi", `diffForHumans` locale `id`, absolut, 1 bagian).
  - open → `assertSee('Pemilihan sedang berlangsung')` dan tamu melihat link `/login` berteks `Masuk untuk memilih`.
  - closed → `assertSee('Pemilihan ditutup')`, `assertSee('href="#hasil"', false)`, dan `assertSee('3 suara')` untuk hasil 3:1; ditutup tanpa suara → `assertSee('Belum ada suara tercatat.')`.
  - admin login → `assertSee('/back-admin-logged/redirect-role', false)`.
  - tanpa paslon → `assertSee('Daftar pasangan calon belum diumumkan.')`.
  - poster tidak ada di disk → `assertDontSee('assets/upload/kandidat/default.png', false)`.
  - misi tampil sebagai `<details>` berisi tiap butir `missionItems()`.
  - `assertDontSee('Chart.min.js', false)`, `assertDontSee('model-viewer', false)` saat `.glb` tidak ada, `assertSee('data-slider-toggle', false)`, `assertSee('Periode 2025/2026')`, `assertSee('wa.me/6283823093973', false)`.
- [ ] **Step 2: Jalankan** → FAIL.
- [ ] **Step 3: Tulis `welcome.blade.php`** sesuai layout spec (font: `preconnect` + Google Fonts `Archivo:wdth,wght@62..125,500..900` & `Plus+Jakarta+Sans:wght@400;600` dengan `display=swap`): header (logo EV + Masuk/Lanjutkan+Keluar), hero (3 `<img>` srcset, `h1`, tombol jeda, slip status per keadaan, `<model-viewer camera-controls>` + script modul `@google/model-viewer@3.5.0` dari jsDelivr hanya jika `mascotUrl`), `main` → `#paslon` (grid surat suara, heksagon nomor, poster `aspect-ratio: 3/4` atau placeholder heksagon jika `!file_exists(public_path('assets/upload/kandidat/'.$poster))`, label Ketua/Wakil, visi, `<details>` misi), `#hasil` (bar `<ol>` berlabel langsung, jumlah "N suara", persen, total), footer (logo HMIF, nama himpunan, CS Farda/Dewi `https://wa.me/...`). Tanggal: `->locale('id')->translatedFormat('l, j F Y')`, jam `H.i`.
- [ ] **Step 4: Tulis `home.css`** dengan token di `:root` (Global Constraints), mobile-first, breakpoint 720px & 1080px; grid surat suara `repeat(auto-fit, minmax(260px, 1fr))` dengan garis 2px `ink`; `:focus-visible` outline 3px `yellow` di latar gelap / `teal-ink` di latar terang; `@media (prefers-reduced-motion: reduce)`.
- [ ] **Step 5: Tulis `home.js`** (defer): ganti kelas aktif foto tiap 7 detik, tombol toggle `aria-pressed` + label "Jeda slider"/"Putar slider", tidak berjalan jika reduced motion, berhenti saat tab tersembunyi.
- [ ] **Step 6: Jalankan seluruh suite** `php artisan test` → semua PASS.
- [ ] **Step 7: Commit** `feat(home): ballot-style homepage redesign`.

### Task 4: Verifikasi visual & Delivery Gate

- [ ] **Step 1: Siapkan data**: jalankan `php artisan serve`; screenshot dengan Chrome headless (`--headless=new --window-size=1440,900` dan `390,844`, `--screenshot`) untuk keadaan jadwal belum dibuka, dibuka, dan ditutup (ubah jadwal lewat tinker, kembalikan setelahnya), termasuk 3 paslon dengan nama panjang (data sementara, dihapus setelahnya).
- [ ] **Step 2: Kritik screenshot** terhadap spec & Review Focus #5 (overflow, kontras teks di atas foto, fokus, hierarki); perbaiki di `home.css`/Blade; ulangi screenshot.
- [ ] **Step 3: Cek kontras** pasangan warna teks yang dipakai (hitung rasio WCAG dengan skrip kecil di scratchpad) ≥ 4.5:1 teks normal.
- [ ] **Step 4: Laporan Delivery Gate antislop** (Blok 1–4, PASS/FAIL dengan bukti) di ringkasan akhir; kembalikan data lokal ke keadaan semula.
- [ ] **Step 5: Commit** perbaikan visual (jika ada) `fix(home): visual review adjustments`.
