# Pita Jadwal Pemilihan Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ubah kartu "Jadwal pemilihan" menjadi pita teal lebar penuh di antara hero dan section "Surat suara".

**Architecture:** Pembungkus `div.container.slip-row` diganti `div.slip-band` (full-bleed) > `div.container.slip-band__inner` berisi `section.slip` yang sama + slot maskot. Logika `@switch($status)` tidak disentuh. CSS `.slip-row`/`.slip` lama diganti aturan pita.

**Tech Stack:** Laravel 10 Blade, Pest 2, CSS native, verifikasi puppeteer-core@23.11.1 di scratchpad.

**Spec:** `docs/superpowers/specs/2026-11-25-schedule-band-design.md`

## Global Constraints

- Latar pita `--teal` (#31BDAA); teks hanya `--ink` atau `--deep`; fokus di pita `outline-color: var(--ink)`; tanpa `box-shadow` dan tanpa margin negatif.
- Kelas `.slip`, `.slip__label`, `.slip__status`, `.slip__when` dan semua teks status tetap (dipakai tes & skrip).
- Tanpa animasi pita; tanpa em dash di teks UI.
- Tes hanya di DB `election_vote_testing`; jangan push.

## Review Focus

1. Status "upcoming" beda hari (dua baris waktu) dan status "not set" (teks panjang) di 390px → pita membungkus tanpa overflow horizontal.
2. Tombol aksi (status open) tetap terlihat tanpa scroll di 1440×900 dan 1366×768.
3. Pengguna login (tombol "Buka surat suara"/"Panel admin") → tombol sama ukurannya dan muat di baris pita.
4. File maskot ada → `<model-viewer>` di ujung kanan pita tidak mendorong pita jadi sangat tinggi di HP.
5. Skip link "Langsung ke daftar pasangan calon" tetap melompati pita ke `#paslon`.

---

### Task 1: Pita jadwal

**Files:**
- Modify: `resources/views/welcome.blade.php` (blok `div.container.slip-row`)
- Modify: `public/frontend/css/home.css` (blok `/* Slip status */`, `.slip-row`/`.slip` di media ≥720px, `.mascot`)
- Test: `tests/Feature/HomePageTest.php` (blok Kerangka halaman)
- Verifikasi: `<scratchpad>/shots/band.mjs` (baru)

**Interfaces:**
- Produces: `<div class="slip-band">` > `<div class="container slip-band__inner">` > `section.slip[aria-labelledby=jadwal]` + `model-viewer.mascot` (opsional).

- [ ] **Step 1: Tes gagal**

```php
it('shows the schedule as a full-width band between the hero and the ballot', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain('<div class="slip-band">')
        ->not->toContain('slip-row');
    expect(strpos($html, 'class="hero"'))->toBeLessThan(strpos($html, 'class="slip-band"'));
    expect(strpos($html, 'class="slip-band"'))->toBeLessThan(strpos($html, 'id="paslon"'));
});
```

Run: `php artisan test --filter="full-width band"` → Expected: FAIL.

- [ ] **Step 2: `band.mjs`** — server `DB_DATABASE=election_vote_testing php artisan serve --port=8010` dengan 2 paslon + jadwal open (seed via tinker, env DB testing). Cetak `PASS|FAIL`:
  1. `.slip-band` left = 0 dan width = `document.documentElement.clientWidth` (390, 768, 1366, 1440).
  2. `.slip-band` top = bottom `.hero` (±1px).
  3. `getComputedStyle(.slip-band).backgroundColor` = `rgb(49, 189, 170)`; `.slip` tanpa box-shadow.
  4. Di ≥1080px: `.slip__status`, `.slip__when`, dan `.btn` di `.slip` berada pada satu baris (top bertumpang tindih).
  5. Tombol aksi bottom ≤ innerHeight di 1440×900 dan 1366×768.
  6. overflowX = 0 di 390 dan 768.
  7. Warna teks `.slip__label`, `.slip__when` = ink atau deep.
  Lalu ulangi 1, 6 untuk status upcoming beda hari dan not set.
  Run sebelum implementasi → Expected: FAIL (cek 1–4).

- [ ] **Step 3: Markup + CSS** sesuai Interfaces dan Global Constraints. `.slip-band { background: var(--teal); border-top: 4px solid var(--deep); }`; `.slip` tanpa max-width/padding kartu/radius/bayangan; ≥1080px `.slip` grid `auto 1fr auto` (label+status | waktu | tombol), `align-items: center`; `.slip .btn { margin-top: 0 }` di baris itu; `.slip-band :focus-visible { outline-color: var(--ink); }`. Maskot: `.slip-band__inner` grid `1fr auto` di ≥720px.

- [ ] **Step 4: Hijau** — `php artisan test` (149 passed) dan `node band.mjs` semua PASS; `node envelope.mjs`, `node tab.mjs` tetap PASS; screenshot pita di 1440 dan 390 untuk tiap status, dilihat.

- [ ] **Step 5: Commit** `feat(home): schedule as a full-width teal band`
