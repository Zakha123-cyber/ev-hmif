# Amplop Paslon + Modal Surat Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ganti kolom paslon di section `#paslon` dengan amplop tertutup ("Paslon N" + segel) yang membuka modal `<dialog>` berbentuk surat berisi poster, nama, visi, dan misi.

**Architecture:** Blade merender satu `<button class="envelope">` + satu `<dialog class="letter">` per paslon. Tombol memakai `commandfor`/`command="show-modal"` sebagai jalur tanpa JS; `home.js` (IIFE baru, terpisah dari slider) mengambil alih untuk animasi tutup amplop, tutup lewat backdrop, dan pengembalian fokus. CSS di `home.css`, aturan `.ballot*` lama dihapus.

**Tech Stack:** Laravel 10 Blade, Pest 2, CSS native (`<dialog>`, `:has()`), vanilla JS, verifikasi visual puppeteer-core@23.11.1 di scratchpad.

**Spec:** `docs/superpowers/specs/2026-11-25-envelope-ballot-design.md` (melanjutkan `docs/superpowers/specs/2026-11-24-homepage-redesign-design.md`)

## Global Constraints

- Depan amplop hanya "Paslon N" + segel heksagon bernomor + petunjuk "Buka amplop"; **nama calon tidak boleh ada di dalam `<button class="envelope">`**.
- Surat wajib menampilkan poster paslon (atau placeholder "Foto pasangan calon belum tersedia").
- Tidak ada tombol untuk mematikan animasi; hanya `prefers-reduced-motion: reduce` yang menghilangkan gerak.
- Intro: h2 "Surat suara" + "Buka amplop untuk mengenal tiap pasangan calon. Suara hanya bisa dikirim satu kali dan tidak dapat diubah." rata tengah.
- Token warna yang ada saja (`--teal`, `--teal-ink`, `--deep`, `--yellow`, `--paper`, `--ink`, `--ink-soft`); WCAG AA; fokus terlihat; tanpa em dash di teks UI; tanpa data karangan.
- Tes hanya di DB `election_vote_testing` (phpunit.xml); jangan push.

## Review Focus

1. Safari tidak memfokuskan `<button>` saat diklik → fokus harus dikembalikan eksplisit ke amplop saat surat ditutup.
2. Browser tanpa dukungan Invoker Commands dan JS gagal → amplop tidak bisa dibuka; terima (JS normal selalu jalan), tapi JS wajib menghapus atribut `command`/`commandfor` agar modal tidak terbuka dua kali/tanpa animasi.
3. `prefers-reduced-motion` di slider memanggil `return` lebih awal → logika amplop harus di IIFE sendiri supaya tetap jalan.
4. Misi/visi panjang atau poster tinggi di HP 390px → surat bergulir di dalam (maks 90svh), halaman di belakang tidak ikut bergulir, overflowX 0.
5. Klik di dalam kertas surat (bukan backdrop) tidak boleh menutup modal.

---

### Task 1: Markup amplop + surat

**Files:**
- Modify: `resources/views/welcome.blade.php:119-173` (section `#paslon`)
- Test: `tests/Feature/HomePageTest.php` (blok `// --- Surat suara ---` dan tes screen reader di akhir file)

**Interfaces:**
- Produces (dipakai Task 2): `<ol class="envelopes envelopes--{min(count,3)}">` > `<li>` berisi
  - `<button type="button" class="envelope" data-envelope commandfor="surat-{id}" command="show-modal">` dengan anak `span.envelope__flap[aria-hidden]`, `span.hex-number.envelope__seal[aria-hidden]` (angka), `span.envelope__label` ("Paslon N"), `span.envelope__hint` ("Buka amplop").
  - `<dialog id="surat-{id}" class="letter" aria-labelledby="surat-{id}-judul">` > `div.letter__paper` berisi `form[method=dialog]` + `button.letter__close` "Tutup" (atas), `header.letter__kop` (img `logo-hmif-320.webp` alt="" + "Himpunan Mahasiswa Informatika" / "Universitas Jember"), `h3#surat-{id}-judul` "Pasangan Calon Nomor Urut N", `img.letter__poster` (loading="lazy") atau `div.letter__poster.letter__placeholder`, `dl.letter__names`, `h4` "Visi" + `p.letter__vision`, `h4` "Misi" + `ol.letter__mission` (hanya jika ada butir), `form[method=dialog]` + `button.letter__close` "Tutup" (bawah).
  - `{id}` = `$candidate->id`.

- [ ] **Step 1: Ganti/ tambah tes di blok Surat suara**

Hapus tes `lists every mission item inside an expandable section` dan `announces the ballot number to screen readers as real text`; ubah tes placeholder agar memeriksa `letter__placeholder`. Tambah helper dan tes:

```php
function envelopeMarkup(string $html, int $pair): string
{
    preg_match_all('#<button[^>]*class="envelope"[^>]*>.*?</button>#s', $html, $m);
    return collect($m[0])->first(fn ($b) => str_contains($b, "Paslon {$pair}<")) ?? '';
}

it('centers an intro that tells visitors to open the envelopes', function () {
    $this->get('/')->assertSee('Buka amplop untuk mengenal tiap pasangan calon.');
});

it('shows one sealed envelope per candidate without their names', function () {
    makeCandidate(1);
    makeCandidate(2);
    $html = $this->get('/')->getContent();

    expect(substr_count($html, 'class="envelope"'))->toBe(2);
    expect(envelopeMarkup($html, 1))->toContain('Paslon 1')->toContain('Buka amplop')
        ->not->toContain('Ketua 1')->not->toContain('Wakil 1');
});

it('links each envelope to its own letter dialog', function () {
    $candidate = makeCandidate(1);

    $this->get('/')
        ->assertSee('commandfor="surat-' . $candidate->id . '" command="show-modal"', false)
        ->assertSee('<dialog id="surat-' . $candidate->id . '" class="letter"', false);
});

it('writes the letter with a letterhead, title, names, vision and numbered missions', function () {
    $candidate = makeCandidate(1);
    $candidate->forceFill(['mission' => 'Membangun himpunan. Mendukung anggota.'])->save();

    $this->get('/')->assertSeeInOrder([
        'letter__kop', 'Himpunan Mahasiswa Informatika', 'Pasangan Calon Nomor Urut 1',
        'Ketua', 'Ketua 1', 'Wakil', 'Wakil 1', 'Visi', 'Misi',
        '<ol class="letter__mission">', 'Membangun himpunan.', 'Mendukung anggota.',
    ], false);
});

it('shows the candidate poster inside the letter when the file exists', function () {
    $file = '__test-poster.png';
    $path = public_path('assets/upload/kandidat/' . $file);
    file_put_contents($path, 'png');
    makeCandidate(1)->forceFill(['poster' => $file])->save();

    try {
        $this->get('/')
            ->assertSee('class="letter__poster" src="' . asset('assets/upload/kandidat/' . $file) . '"', false)
            ->assertDontSee('letter__placeholder', false);
    } finally {
        unlink($path);
    }
});

it('lets visitors close the letter with a button', function () {
    makeCandidate(1);

    $this->get('/')->assertSee('<form method="dialog">', false)->assertSee('Tutup');
});
```

Tes `shows the candidate names, roles and vision` tetap; `assertDontSee('aria-label="Nomor urut', false)` dipindah ke tes "sealed envelope" (`->assertDontSee` via `expect($html)->not->toContain('aria-label="Nomor urut')`).

- [ ] **Step 2: Jalankan, pastikan merah**

Run: `php artisan test --filter=HomePageTest`
Expected: FAIL pada tes intro, envelope, dialog, letterhead, poster, close (markup belum ada).

- [ ] **Step 3: Ubah section `#paslon` di `welcome.blade.php`** sesuai Interfaces di atas; intro memakai salinan Global Constraints; empty state tetap. Poster: `width="600" height="800"`, alt tetap "Poster pasangan calon nomor N, {ketua} dan {wakil}". Segel dan flap `aria-hidden="true"` sehingga nama aksesibel tombol = "Paslon N Buka amplop".

- [ ] **Step 4: Jalankan, pastikan hijau**

Run: `php artisan test`
Expected: semua PASS (jumlah = 141 − 2 dihapus + 6 baru = 145).

- [ ] **Step 5: Commit**

```bash
git add resources/views/welcome.blade.php tests/Feature/HomePageTest.php
git commit -m "feat(home): candidate envelopes with letter dialogs"
```

---

### Task 2: Gaya amplop/surat + perilaku JS

**Files:**
- Modify: `public/frontend/css/home.css` (ganti aturan `.ballot*` + breakpoint `.ballot--*`; `.hex-number` dipertahankan, margin dipindah ke konteksnya)
- Modify: `public/frontend/js/home.js` (IIFE kedua)
- Verifikasi: `<scratchpad>/shots/envelope.mjs` (baru), `<scratchpad>/shots/states.mjs` (perbarui daftar selector), `check.mjs`, `tab.mjs`

**Interfaces:**
- Consumes: markup Task 1 (`[data-envelope]`, `commandfor`, `dialog.letter`, `.letter__paper`, `.envelope__flap`).
- Produces: kelas state `.envelope.is-opening`; konstanta JS `FLAP_MS = 220`.

- [ ] **Step 1: Tulis `envelope.mjs`** (puppeteer, viewport 1440x900 dan 390x844, server `http://127.0.0.1:8010/`). Cetak satu baris `PASS|FAIL <cek>` untuk:
  1. `section#paslon .ballot-section__intro` `text-align` = center.
  2. Klik amplop pertama → setelah ≤600ms `dialog.letter[open]` ada dan `getComputedStyle(document.documentElement).overflow` = hidden.
  3. Dialog terbuka berisi `img.letter__poster` yang `complete && naturalWidth > 0` atau `.letter__placeholder`.
  4. Klik di tengah `.letter__paper` → dialog tetap terbuka.
  5. Tekan Escape → dialog tertutup dan `document.activeElement` adalah amplop pertama.
  6. Buka lagi, klik backdrop (titik 5,5 viewport) → dialog tertutup.
  7. Klik tombol "Tutup" bawah → tertutup.
  8. Di 390px dengan surat terbuka: `dialog.scrollHeight > dialog.clientHeight` boleh, tapi `document.documentElement.scrollWidth - innerWidth` = 0 dan lebar dialog ≤ 390 − 32.
  9. Dengan `page.emulateMediaFeatures([{name:'prefers-reduced-motion', value:'reduce'}])`: klik amplop → dialog terbuka dalam ≤50ms.
  10. Atribut `command` tidak ada lagi di amplop setelah DOMContentLoaded (JS mengambil alih).
  11. Nol console error / request gagal.

  Perbarui `states.mjs`: ganti cek `.ballot__mission summary:*` dengan `.envelope:hover` (berisi `transform` atau `box-shadow`), `.envelope:active`, `.letter__close:hover` (berisi `background`), `.letter__close:active`.

- [ ] **Step 2: Jalankan, pastikan merah**

Siapkan server: `php artisan serve --port=8010` (DB dev, hanya GET; jika tabel candidates kosong, pakai `DB_DATABASE=election_vote_testing` dan buat 2 paslon dengan poster nyata dari `public/assets/upload/kandidat/` via tinker).
Run: `node envelope.mjs` dan `node states.mjs ../../…/home.css`
Expected: FAIL setidaknya cek 1, 2 (tanpa JS animasi masih bisa buka via commandfor, tapi overflow belum hidden), 6, 10, dan cek states baru.

- [ ] **Step 3: CSS** — keputusan yang dikunci:
  - Intro: `text-align: center`, `margin-inline: auto`, paragraf `max-width: 60ch`, `margin-inline: auto`.
  - `.envelopes`: grid, `gap: 24px`, 1 kolom; ≥720px `.envelopes--2, .envelopes--3` 2 kolom; ≥1080px `.envelopes--3` 3 kolom; `.envelopes--1` satu amplop `max-width: 420px` di tengah.
  - `.envelope`: `aspect-ratio: 10 / 7`, latar `--paper`, `border: 2px solid var(--teal-ink)`, `border-radius: var(--radius)`, `perspective: 800px`, konten label di sepertiga bawah; label "Paslon N" Archivo 800 `clamp(28px, 5vw, 40px)` `--ink`; hint 14px 600 `--teal-ink` bergaris bawah.
  - `.envelope__flap`: absolut di atas, tinggi ~58%, `clip-path: polygon(0 0, 100% 0, 50% 100%)`, latar `--teal`, `transform-origin: top`, `transition: transform 220ms ease-out`. Segel `.envelope__seal` (hex kuning 56x64) diposisikan di ujung flap, di atas flap (z-index).
  - Hover/focus-visible: flap `rotateX(24deg)` + amplop `translateY(-3px)`; active: `translateY(1px)`; `.is-opening` flap `rotateX(180deg)`; focus-visible outline 3px `--teal-ink` offset 4px.
  - `.letter`: `width: min(640px, 100% - 32px)`, `max-height: 90svh`, `padding: 0`, `border: 0`, `border-top: 6px solid var(--teal)`, `overflow: auto`, latar `--paper`; `::backdrop` `rgb(14 53 50 / .72)`; `.letter[open]` animasi `letter-rise` 260ms (translateY(24px)+opacity 0 → 0/1).
  - `html:has(.letter[open]) { overflow: hidden; }`.
  - Kop: flex, logo 56px, nama tebal + "Universitas Jember" `--ink-soft`, `border-bottom: 3px double var(--ink)`.
  - Poster surat: `width: 100%`, `max-width: 360px`, `aspect-ratio: 3/4`, `object-fit: cover`, tengah; placeholder memakai gaya placeholder lama.
  - `.letter__close`: gaya tombol tenang dengan `:hover` (background) dan `:active`.
  - `@media (prefers-reduced-motion: reduce)`: tanpa transition/animation/transform untuk `.envelope*` dan `.letter`.
  - Hapus semua aturan `.ballot`, `.ballot__*`, `.ballot--*` yang tak dipakai lagi (`.ballot-section*` dan `.ballot__empty` tetap).

- [ ] **Step 4: JS** — IIFE kedua di `home.js` pada `DOMContentLoaded`:
  - Untuk tiap `[data-envelope]`: cari dialog via `commandfor`, hapus atribut `command`/`commandfor`.
  - Klik: tambah `.is-opening`, lalu `dialog.showModal()` setelah `FLAP_MS` (0 jika `prefers-reduced-motion: reduce` cocok saat klik).
  - `dialog` event `click` dengan `event.target === dialog` → `dialog.close()` (kertas mengisi dialog, jadi klik di dalam kertas tak pernah menarget dialog).
  - `dialog` event `close` → hapus `.is-opening`, `button.focus()`.

- [ ] **Step 5: Jalankan, pastikan hijau**

Run: `node envelope.mjs`, `node states.mjs <home.css>`, `node check.mjs closed`, `node tab.mjs`, dan `php artisan test`
Expected: semua PASS; overflowX 0 di 390/768/1000/1440; tab order skip link → logo → Masuk → Jeda foto → CTA → amplop 1 → amplop 2; suite 145 passed. Cek kontras pasangan baru dengan `mcp__plugin_antislop_antislop-contrast__check_contrast` (label ink di paper, hint teal-ink di paper, angka ink di yellow): semua ≥ 4.5. Lihat screenshot amplop tertutup + surat terbuka di desktop & 390px.

- [ ] **Step 6: Commit**

```bash
git add public/frontend/css/home.css public/frontend/js/home.js
git commit -m "feat(home): envelope and letter styling with open animation"
```
