# Admin Panel Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild every admin view on a new shell and component set: Indonesian, readable, responsive, free of the old libraries, with every feature, route, field name and validation unchanged, plus the bug fixes listed in the spec.

**Architecture:**
- **Shell.** New `admin/layouts/app.blade.php` with partials `admin/components/sidebar` and `admin/components/topbar`.
- **Assets.** `public/frontend/css/admin.css` and `public/frontend/js/admin.js` provide the shell, drawer, flash close, confirm dialog, table filter/sort, file preview and credential regenerate. Pages load `home.css` first for tokens and fonts.
- **Pages.** Each page is a Blade view using only those components.
- **Charts.** Replaced by CSS bars.

**Tech Stack:** Laravel 10 Blade, plain CSS/JS, Pest 2, DomPDF (print view), puppeteer-core scratchpad checks.

**Spec:** `docs/superpowers/specs/2026-11-27-admin-redesign-design.md`

## Global Constraints

- **Workflow.**
  - Tests run only on DB `election_vote_testing`.
  - One commit per task. Never push. Never touch `.env`.
- **Routes and data must not change.**
  - Routes, methods, URLs, field names, and validation rules stay as they are (except the spec's fix list).
  - Edit and info pages are opened by **POST** forms with `@csrf`.
  - The verification `nim` field carries the **participant id**.
- **Names that tests depend on.**
  - View names stay; `admin.pages.election-schedule.edit` is asserted.
  - Dashboard and live-count views keep the variables `countVoted`, `countNotVoted`, `countParticipant`.
- **Strings that tests depend on.**
  - The verification edit page must contain the literal strings `/back-admin-logged/generate-username` and `/back-admin-logged/generate-password`, and must not contain `/generateNew`.
  - Flash `session('status')` / `session('error')` text is rendered verbatim (escaped). Both can show at once.
- **Removed libraries.** No Font Awesome, jQuery, DataTables, Flowbite, Selectize, ApexCharts or Chart.js on admin pages. The old `assets/css-admin/style.css` and `assets/js-admin/scripts.js` are not loaded.
- **Copy and accessibility.**
  - Indonesian copy, no em dash.
  - Every action button has visible text.
  - Delete and broadcast use `data-confirm`.
  - Targets of 44px or more, visible focus, AA contrast.
  - No horizontal page scroll at 360px. Tables stack below 720px.
- **Admin name.** Use `auth()->user()->details?->participant_name ?: auth()->user()->username`. It must not crash when an admin has no participant.

## Review Focus

1. **Drawer on 360–1023px.** It opens and closes with the button, Esc and the overlay. Focus moves into the drawer and back to the button.
2. **Confirm dialog.** "Batal" must not submit. Enter on "Ya, lanjutkan" submits exactly once. Forms without `data-confirm` are unaffected.
3. **Table filter with zero matches.** Shows "Tidak ada yang cocok" and the count reads 0.
4. **Import dialog.** Batal does not submit. A missing file shows the `file` error after the redirect, by reopening the dialog when `$errors->has('file')`.
5. **Admin with no participant record.** Topbar and dashboard render.

---

### Task 1: Shell, components, flash, schedule page

**Files:**
- Rewrite: `resources/views/admin/layouts/app.blade.php`, `resources/views/admin/components/sidebar.blade.php`
- Create: `resources/views/admin/components/topbar.blade.php`, `resources/views/admin/components/icon.blade.php` (inline SVG by name)
- Delete: `resources/views/admin/components/navbar.blade.php`
- Create: `public/frontend/css/admin.css`, `public/frontend/js/admin.js`
- Rewrite: `resources/views/admin/pages/election-schedule/edit.blade.php`
- Test: `tests/Feature/AdminShellTest.php`

**Interfaces:**
- **Layout sections.**
  - `@section('title')` holds the page title, used in `<title>` and the topbar h1.
  - `@section('actions')` is optional: buttons placed right of the title.
  - `@section('content')`, plus `@push('scripts')` for page JS.
  - The layout loads fonts, `home.css`, `admin.css` and `admin.js` (defer), all with `?v=filemtime`.
- **Sidebar.**
  - Items: `[slug, label, icon]` = `dashboard` Ringkasan; group "Persiapan" with `election-schedule` "Jadwal & periode", `candidate` Paslon, `participant` Peserta, `participant-verification` "Verifikasi akun"; group "Pemilihan" with `live-count` "Hasil suara".
  - Active item when `request()->segment(2) === slug`; it gets `aria-current="page"`.
- **Topbar.** Contains:
  - the menu button `[data-drawer-open]` (hidden at 1024px and up);
  - the h1;
  - the status chip from `ElectionSetting::current()->status()`, linking to the schedule page;
  - the admin name;
  - a "Lihat situs" link to `/`;
  - the logout form with button "Keluar".
- **Flash.** Status renders as `.a-flash.a-flash--ok` (`role="status"`); error renders as `.a-flash.a-flash--err` (`role="alert"`). Each has a "Tutup" button `[data-flash-close]`.
- **CSS classes** (in `admin.css`):
  - Containers: `.a-card`, `.a-card__head`, `.a-card__body`.
  - Buttons: `.a-btn` with modifiers `--primary`, `--accent`, `--ghost`, `--danger`, `--sm`.
  - Forms: `.a-field`, `.a-label`, `.a-input`, `.a-help`, `.a-error`.
  - Badges: `.a-badge` with modifiers `--ok`, `--warn`, `--muted`, `--danger`.
  - Tables: `.a-table` (with `[data-label]` cells for stacking), `.a-toolbar`.
  - Empty state: `.a-empty`.
- **`admin.js` behaviours.**
  - Drawer: `[data-drawer-open]` / `[data-drawer]`, closes on Esc, overlay click and link click.
  - Flash close.
  - `form[data-confirm]`: uses a shared `<dialog id="a-confirm">` rendered in the layout, with "Ya, lanjutkan" and "Batal". Falls back to `confirm()`.
  - `[data-table-filter="<tableId>"]`: input filtering `tbody tr` by text, updates `[data-table-count]`, shows `[data-table-empty]`.
  - `th button[data-sort]`: sorts rows (numbers numerically) and toggles `aria-sort`.
  - `[data-file-preview]`: the file input sets `img[data-preview]` src and `[data-file-name]` text.
  - `[data-regenerate="url"][data-target="#id"]`: fetches the URL (text) into the target input.
  - `[data-reveal="#id"]`: toggles a password input's type, with "Tampilkan" / "Sembunyikan".
  - `dialog[data-open-on-load]`: shown with `showModal()` on load.
  - `[data-dialog-open="#id"]` opens a dialog; `[data-dialog-close]` closes it.

- [ ] **Step 1: Write the tests** in `AdminShellTest`. Admin via `makeUserWithRole('Admin','admin1')`.
  - GET `/back-admin-logged/election-schedule` contains:
    - "Ringkasan", "Jadwal &amp; periode", "Verifikasi akun", "Hasil suara", "Keluar";
    - `aria-current="page"` on the schedule link (assert the link HTML contains both `election-schedule` and `aria-current`);
    - the `admin.css` link.
  - The same page does NOT contain `kit-pro.fontawesome`, `jquery`, `datatables`, `flowbite`, `apexcharts`, `chart.js`, `css-admin/style.css`.
  - Session `['status' => 'Tersimpan A', 'error' => 'Gagal B']`: both texts render.
  - An admin whose participant record is deleted (`$admin->details()->delete()` or `participant_id` null) can GET `/dashboard` (200) and sees their username.
  - The existing `VotingScheduleTest` still passes (`assertViewIs`).
- [ ] Step 2: Run `php artisan test --filter=AdminShellTest`. Expected: FAIL.
- [ ] Step 3: Implement the shell, `admin.css`, `admin.js` and the schedule page.
  - Schedule page:
    - status chip;
    - period field;
    - two datetime fields side by side (stacked below 720px);
    - help text;
    - Simpan button (accent);
    - every `@error` kept.
- [ ] Step 4: Run `php artisan test`. Expected: PASS.
- [ ] Step 5: Commit `feat(admin): new shell, components and schedule page`.

### Task 2: Ringkasan and Hasil suara

**Files:**
- Modify: `app/Http/Controllers/Admin/DashboardController.php`, `app/Http/Controllers/Admin/CountController.php`
- Rewrite: `resources/views/admin/pages/dashboard/dashboard.blade.php`, `resources/views/admin/pages/count/list.blade.php`
- Test: `tests/Feature/AdminDashboardTest.php`

**Interfaces:**
- Both controllers additionally pass `results`, a collection over **all** candidates ordered by `candidate_pair`. Each item is `['candidate' => Candidate, 'votes' => int, 'percent' => int]`, where percent is relative to total votes.
- Both pass `totalVotes`. Dashboard also passes `setting`. Existing variables keep their names.
- Views:
  - **Ringkasan:**
    - Stat cards, all real numbers: Peserta = `countParticipant`, Sudah memilih = `countVoted`, Belum memilih = `countNotVoted`, Paslon, Admin.
    - Participation bar: voted / (voted + notVoted), with percent and "X dari Y".
    - Schedule card: status, open/close times, link.
    - "Hasil sementara": CSS bars.
    - Greeting with the admin name.
  - **Hasil suara:** large bars per pair (name, votes, percent), total, participation bar.

- [ ] **Step 1: Write the tests.**
  - Dashboard with 3 non-admin participants with accounts (1 voted) plus 1 admin shows the text "1 dari 3" for participation.
  - The dashboard does not contain `num-4`, `Halaman Dilihat` or `Pengunjung Baru`.
  - The dashboard shows "Halo, admin1" for an admin without a participant name (username fallback).
  - A candidate with 0 votes appears on `/live-count` with "0 suara".
  - The existing `assertViewHas` tests still pass.
- [ ] Step 2: Run `--filter=AdminDashboardTest`. Expected: FAIL.
- [ ] Step 3: Implement. The `- 2` disappears because the views no longer use it.
- [ ] Step 4: Run `php artisan test`. Expected: PASS.
- [ ] Step 5: Commit `feat(admin): real-numbers dashboard and results page`.

### Task 3: Paslon

**Files:**
- Rewrite: `resources/views/admin/pages/candidate/{list,add,edit}.blade.php`
- Modify: `app/Http/Controllers/Admin/CandidateController.php` (update flash text "Berhasil memperbarui data.")
- Test: `tests/Feature/AdminCandidateTest.php`

**Interfaces:**
- **List.**
  - Card per candidate: poster (or placeholder), hex number, names, visi, misi as `<ol>` from `missionItems()` (escaped).
  - Actions:
    - "Ubah": POST form to `candidate/edit/{id}`.
    - "Hapus": DELETE form to `candidate/delete/{id}` with `data-confirm="Hapus Paslon N? Tindakan ini tidak bisa dibatalkan."`.
  - Page action "Tambah paslon" goes to `/candidate/add`.
  - Empty state when there are no candidates.
- **Add/edit.**
  - Fields `candidate_pair` (number), `leader_name`, `vice_leader_name`, `vision` (textarea), `mission` (textarea, with help text: "Pisahkan tiap misi dengan titik."), `poster` (file, `accept="image/jpeg,image/png,image/webp"`, `data-file-preview`).
  - Preview image.
  - All fields use `old()`. Edit uses `@method('put')` to `candidate/update/{id}`.

- [ ] **Step 1: Write the tests.**
  - `/candidate` with a candidate whose mission is `<script>alert(1)</script>. Misi dua.` contains the escaped `&lt;script&gt;` and never the raw `<script>alert(1)</script>`.
  - The list shows "Ubah" and "Hapus" and has `data-confirm`.
  - A PUT update flashes "Berhasil memperbarui data.".
  - `UploadValidationTest` and `VotePreservationTest` still pass.
- [ ] Step 2: Run. Expected: FAIL.
- [ ] Step 3: Implement.
- [ ] Step 4: Run `php artisan test`. Expected: PASS.
- [ ] Step 5: Commit `feat(admin): candidate pages; escape mission text`.

### Task 4: Peserta

**Files:**
- Rewrite: `resources/views/admin/pages/participant/{list,add,edit}.blade.php`
- Modify: `ParticipantController.php` (participant update flash "Berhasil memperbarui data.")
- Test: `tests/Feature/AdminParticipantTest.php`

**Interfaces:**
- **List.**
  - Page actions:
    - "Tambah peserta" link.
    - "Impor Excel" button `[data-dialog-open="#import"]`.
    - Broadcast form (POST `participant/broadcast`) with `data-confirm="Buat akun dan kirim email ke semua peserta yang belum punya akun?"`, button "Buat akun & kirim email".
  - Filter input, then the table `#peserta` with columns: No, Nama, NIM, WhatsApp, Status (badge "Terverifikasi" / "Belum terverifikasi"), Aksi.
  - Row actions:
    - "Ubah": POST `participant/edit/{id}`.
    - "Kirim ulang email": POST `participant/email-backup/{id}`.
    - "Hapus": DELETE with `data-confirm`.
- **Import dialog.**
  - `<dialog id="import">` containing the form: POST `participant/import`, multipart, `file` input with `accept=".xlsx,.xls,.csv"`, the help text, and `@error('file')`.
  - Buttons "Impor" (submit) and "Batal" (`type=button`, `data-dialog-close`).
  - The dialog gets `data-open-on-load` when `$errors->has('file')`.
- **Add/edit.**
  - Fields `participant_name`, `nim`, `whatsapp_number` (`old('whatsapp_number', '62')` on add), `email_backup` (email), `status` select with `old()` (options `not verified` → "Belum terverifikasi", `verified` → "Terverifikasi"), `image` (file preview, default `asset('assets/upload/participant/avatar.png')` when it exists, else no image).
  - Edit title: "Ubah peserta".

- [ ] **Step 1: Write the tests.**
  - The list contains "Impor Excel", `id="import"`, the broadcast `data-confirm`, "Kirim ulang email", and the badge text.
  - POST import without a file, then GET `/participant`, shows the file error inside the dialog and `data-open-on-load`.
  - POST store with an invalid email keeps the `whatsapp_number` and `status` values (old input visible in the add page after redirect).
  - The edit page title is "Ubah peserta".
  - A PUT update flashes "Berhasil memperbarui data.".
  - The existing Broadcast, Import, EmailBackup and ParticipantAccounts tests pass.
- [ ] Step 2: Run. Expected: FAIL.
- [ ] Step 3: Implement.
- [ ] Step 4: Run `php artisan test`. Expected: PASS.
- [ ] Step 5: Commit `feat(admin): participant pages with search, import dialog and confirmations`.

### Task 5: Verifikasi akun, info, cetak

**Files:**
- Rewrite: `resources/views/admin/pages/participant-verification/{list,edit,info,print}.blade.php`
- Modify: `ParticipantController::updateVerification` (add `'status' => 'required|in:verified,not verified'`)
- Test: `tests/Feature/AdminVerificationTest.php`

**Interfaces:**
- **List.**
  - Card "Buat akun peserta": POST `participant-verification/verified`. It contains:
    - a filter input `[data-select-filter="#pilih-peserta"]` that hides options not matching;
    - `<select id="pilih-peserta" name="nim">` with options of value = participant id and text `"{nim} - {name}"`;
    - `username` and `password` (readonly), each with an "Acak ulang" button `data-regenerate="{{ url('/back-admin-logged/generate-username') }}"` / `generate-password`;
    - `whatsapp_number` with `old('whatsapp_number','62')`.
  - Verified table `#terverifikasi` with filter. Actions:
    - "Ubah akun": POST `participant-verification/edit/{id}`.
    - "Info akun": POST `participant-verification/info/{id}`.
- **Edit.**
  - The read-only participant identity as text (no disabled select).
  - `username` (readonly) with Acak ulang (`data-regenerate` = the generate-username URL).
  - `password` (readonly, type password) with Acak ulang plus Tampilkan.
  - `whatsapp_number`, and `status` select.
  - PUT `participant-verification/update/{id}`.
- **Info.**
  - Detail list. The password is masked, with a reveal control that shows `password_old` in a hidden span toggled by a button.
  - "Kirim WhatsApp" link: same template text, but the year comes from `ElectionSetting::current()->periodLabel()`. `target="_blank" rel="noopener"`.
  - "Cetak kartu" link to `participant-verification/info/print/{id}` (`target="_blank"`).
- **Print.** DomPDF-safe inline CSS; title "Kartu akun pemilih"; same rows; note with `periodLabel()`.

- [ ] **Step 1: Write the tests.**
  - The verification edit page contains both generate URLs and not `/generateNew` (the existing `CredentialGeneratorTest` passes).
  - The list contains `name="nim"` options whose values are ids, and "Acak ulang".
  - The info page contains `periodLabel` text "2025/2026" (default) and not "2022/2023".
  - GET print streams a PDF (status 200, `Content-Type` contains `pdf`).
  - PUT update with an invalid status gives `assertSessionHasErrors('status')`.
- [ ] Step 2: Run. Expected: FAIL.
- [ ] Step 3: Implement.
- [ ] Step 4: Run `php artisan test`. Expected: PASS.
- [ ] Step 5: Commit `feat(admin): verification pages; period-aware WhatsApp text and card`.

### Task 6: Browser click-through and cleanup

- [ ] **Step 1: Write `admin.mjs`** (scratchpad; server :8010; seeded admin and data). For every admin page at 1366 and 390:
  - no console errors;
  - no horizontal overflow;
  - drawer open/close (390) with Esc and the overlay;
  - confirm dialog: Batal does not submit; Ya submits once (on candidate delete with a disposable candidate);
  - import dialog open/close;
  - table filter counts and the empty message;
  - sorting by NIM;
  - Acak ulang fills the fields;
  - password reveal toggles;
  - file preview updates;
  - flash close.
- [ ] Step 2: Run it and fix findings (each fix committed with its test).
- [ ] Step 3: Screenshots of dashboard, participants, verification and schedule at 1366 and 390. Review them against antislop and fix.
- [ ] Step 4: Final reviewer (fresh, most capable model), then a RED→GREEN fix pass.
