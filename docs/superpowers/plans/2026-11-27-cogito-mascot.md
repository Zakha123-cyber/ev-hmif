# COGITO Mascot Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show the animated COGITO mascot on the homepage and every `/vote` state. The mascot has a speech bubble with greeting and guidance lines that follow the schedule and the visitor's state.

**Architecture:**
- **Assets.** The user's two black-background GIFs become transparent animated WebPs, each with a still frame for reduced motion.
- **Line logic.** One PHP class decides the lines and the optional action for each page and state, so it can be unit-tested.
- **Shared component.** One Blade partial renders the mascot in a `corner` (fixed) or `inline` variant.
- **Behaviour.** A small IIFE in `home.js` drives the bubble: Lanjut, Tutup, tapping the figure, and sessionStorage. `home.js` is already loaded on every page involved.
- **Cleanup.** The dead 3D `<model-viewer>` slot is removed.

**Tech Stack:** Laravel 10 Blade, plain CSS/JS, Pest 2, ImageMagick (asset conversion), puppeteer-core (scratchpad checks).

**Spec:** `docs/superpowers/specs/2026-11-27-cogito-mascot-design.md`

## Global Constraints

- **Repo rules.**
  - Tests run only on DB `election_vote_testing`.
  - One commit per task, never push.
  - Never touch or print `.env`.
- **Motion and copy.**
  - No pause or "turn off animation" button.
  - Under `prefers-reduced-motion: reduce` the mascot is a still frame and the bubble does not animate.
  - Bubble text never changes on its own, only on Lanjut or a tap on the figure.
  - No em dash. Dates come from real data, formatted `l, j F` plus ` pukul H.i WIB` in locale `id`.
- **Layout and accessibility.**
  - AA contrast, targets of 44px or more, no horizontal scroll at 320px.
  - In the corner variant the mascot never covers a `<dialog>`: the bubble is hidden while any `dialog[open]` exists.
- **No overlap on the ballot.** On the open ballot the mascot is `inline` in the teal hero and never `fixed`, so it cannot cover "Kirim suara" or the ballot-box animation.
- **Copy.** Lines and actions are exactly the spec's tables.

## Review Focus

1. **The "Tutup" choice.** Tutup must persist across reload in the same tab (sessionStorage, per page key) and must not hide the figure. Tapping the figure reopens the bubble. Covered by T4.
2. **Collision on phones.** At 320–390px the corner mascot and bubble must not cover the homepage "Masuk untuk memilih" band button when that button is at the bottom of the viewport. Verify the bubble can be closed, and that the figure (80px) leaves the band button tappable. Covered by T4 (targets) and the visual pass.
3. **A logged-in voter on the homepage after voting.** Lines must not reveal the chosen candidate. Covered by T2.
4. **No JS.** The first line is visible and readable, and there are no dead Lanjut or Tutup buttons, because they render `hidden` and JS unhides them. Covered by T2.
5. **Missing data.** An open state with no `vote_end`, or a user with no participant name, must not error. The name falls back to the username. Covered by T2.

---

### Task 1: Transparent COGITO assets

**Files:**
- Create:
  - `public/frontend/images/cogito/{salam,salute}.webp` (animated, transparent)
  - `public/frontend/images/cogito/{salam,salute}-still.webp`
- Move: the two GIFs from the parent folder to `resources/mascot/` under their original names (sources, not served)
- Test: `tests/Feature/CogitoAssetsTest.php`

**Interfaces:**
- Produces: the files listed above. Each WebP is 437x388, and each animated WebP is under 250KB.

- [ ] Step 1: Write a test. For each of the four WebPs: it exists, `getimagesize` returns `[437, 388]`, and the mime type is `image/webp`. The two animated files are under 250000 bytes.
- [ ] Step 2: Run `php artisan test --filter=CogitoAssetsTest`. Expected: FAIL.
- [ ] Step 3: Convert.
  - Per frame (`-coalesce`): `-alpha set -fuzz 8% -fill none` with a flood fill from all four corners.
  - Encode as WebP with alpha (lossy quality ~80, `-define webp:alpha-quality=100`). Keep the GIF delays, loop forever.
  - Use frame 0 for the still. Strip metadata.
  - Look at one frame on white and on `#0E3532`.
- [ ] Step 4: Run the test. Expected: PASS.
- [ ] Step 5: Commit `feat(cogito): transparent animated mascot assets`.

### Task 2: Line logic, shared partial, homepage, 3D slot removal

**Files:**
- Create:
  - `app/Support/CogitoLines.php`
  - `resources/views/partials/cogito.blade.php`
- Modify:
  - `resources/views/welcome.blade.php`
  - `app/Http/Controllers/HomeController.php` (remove `mascotUrl`)
  - `config/election.php` (remove `mascot_path`)
  - `public/frontend/css/home.css` (remove `.mascot`, add `.cogito*`)
  - `public/frontend/js/home.js` (bubble IIFE)
  - `tests/Feature/HomePageTest.php` (drop the 3D tests)
- Test: `tests/Feature/CogitoTest.php`

**Interfaces:**
- Produces:
  - **`App\Support\CogitoLines::home(ElectionSetting $s, ?User $u): array{lines: string[], action: ?array{label:string, href:string}}`.** The table branches are:
    - `!$u` gives the guest lines.
    - An Admin gives the lines of the current status, with action Panel admin to `url('/back-admin-logged/redirect-role')` instead of Masuk.
    - A Participant with status voted gives the "Terima kasih…" line.
    - A Participant who has not voted, while open, gives the "Halo, {nama}!…" line with action Buka surat suara to `url('/vote')`.
  - **`CogitoLines::vote(string $page, ElectionSetting $s, User $u): array{...}`**, with `$page` one of `prepare|ballot|closed|receipt`.
  - **Name rule.** `{nama}` is `$u->details?->participant_name ?: $u->username`.
  - **Bubble trigger.** `@include('partials.cogito', ['pose' => 'salam'|'salute', 'variant' => 'corner'|'inline', 'key' => string, 'lines' => [...], 'action' => ?array])` renders:
    - `<aside class="cogito cogito--{variant}" data-cogito data-cogito-key="{key}" aria-label="Pesan dari COGITO">`
    - The bubble `.cogito__bubble` holds `<p data-cogito-line>{first line}</p>` and `<script type="application/json" data-cogito-lines>[…]</script>`.
    - The action link `a.btn.btn--primary[data-cogito-action]` is rendered `hidden` when there is more than one line.
    - `button[data-cogito-next]` (Lanjut) and `button[data-cogito-close]` (Tutup) are both rendered `hidden`.
    - `button.cogito__figure[data-cogito-toggle][aria-label="Bicara dengan COGITO"]` contains a `<picture>`: a still `<source media="(prefers-reduced-motion: reduce)">`, then `<img alt="" width="437" height="388">`.
  - **JS behaviour.**
    - On DOMContentLoaded: unhide Lanjut and Tutup, and put the action back behind the last line.
    - If `sessionStorage['cogito-closed:'+key]` is set, hide the bubble. Otherwise show it after 900ms (0 under reduced motion).
    - Lanjut, or a tap on the figure while the bubble is open, advances to the next line.
    - On the last line, Lanjut is hidden and the action is shown.
    - A tap on the figure while the bubble is closed reopens it at line 1 and removes the key.
    - Tutup or Esc hides the bubble and sets the key.
    - A `MutationObserver` or `toggle` listener hides the bubble while any `dialog[open]` exists.

- [ ] **Step 1: Write the tests in `CogitoTest`.** All are feature tests on `/`, plus a unit-style test calling `CogitoLines::home`.
  - **Not set.** Assert the three lines in order via `assertSeeInOrder` on the JSON-escaped text, using `assertSee('Jadwal pemilihan belum diumumkan. Akun memilih dikirim ke email kampusmu.')`. Assert no `data-cogito-action`.
  - **Upcoming** (`setSchedule('2026-12-01 08:00','2026-12-01 15:00')`, travel to `2026-11-30 10:00`). Assert `Pemilihan dibuka Selasa, 1 Desember pukul 08.00 WIB.`
  - **Open, guest.** Assert `Pemilihan sedang berlangsung sampai Selasa, 1 Desember pukul 15.00 WIB.`, the action `Masuk untuk memilih`, and its href `route('login')`.
  - **Open, participant not voted** (`makeUserWithRole('Participant','voter1')`). Assert `Halo, voter1! Surat suaramu sudah menunggu.` and the action `Buka surat suara` to `url('/vote')`.
  - **Voted participant.** Assert `Terima kasih sudah memilih!`. Assert no candidate name: `assertDontSee('Ketua 1')` inside the `[data-cogito]` element, parsed with DOMXPath.
  - **Closed.** Assert `Pemilihan sudah ditutup. Terima kasih untuk semua yang memilih!` and the action `Lihat hasil` with href `#hasil`.
  - **Admin, open.** Assert the action `Panel admin` and no `Masuk untuk memilih` inside the cogito element.
  - **No JS markup.**
    - Lanjut and Tutup carry the `hidden` attribute.
    - The first line is inside `<p data-cogito-line>`.
    - The img has `width="437" height="388"`, and the still source has `media="(prefers-reduced-motion: reduce)"`.
  - **3D slot removed.** `/` does not contain `model-viewer`, and `config('election.mascot_path')` is null.
- [ ] Step 2: Run `php artisan test --filter=CogitoTest`. Expected: FAIL.
- [ ] Step 3: Implement the class, the partial, the CSS corner variant, the JS IIFE, and the welcome include (corner, `salam`, key `home`). Remove the 3D code and its HomePageTest cases.
  - Corner CSS: `position: fixed; right: 16px; bottom: 16px; z-index: 25`.
  - Figure width: 120px, or 80px below 720px.
  - Bubble: max 280px wide (220px on phones), above-left of the figure, with a tail.
- [ ] Step 4: Run `php artisan test`. Expected: all PASS.
- [ ] Step 5: Commit `feat(cogito): homepage mascot with greeting and guidance bubble`.

### Task 3: COGITO on every /vote state

**Files:**
- Modify:
  - `resources/views/vote_prepare.blade.php`
  - `resources/views/vote_closed.blade.php`
  - `resources/views/vote_page.blade.php`
  - `resources/views/partials/vote-receipt.blade.php`
  - `public/frontend/css/vote.css` (inline variant in the hero and in the receipt)
- Test: `tests/Feature/VotePageTest.php`

**Interfaces:**
- Consumes: `CogitoLines::vote`, `partials.cogito` (T2).
- Produces:
  - **prepare:** corner, salam, key `vote-prepare`.
  - **closed, not voted:** corner, salam, key `vote-closed`.
  - **ballot:** inline inside `.vote-hero--ballot .vote-hero__inner`, salam, key `vote-ballot`.
  - **receipt:** inline beside `.receipt__art`, salute, key `vote-receipt`, single line.

- [ ] **Step 1: Write the tests.**
  - **prepare, upcoming.** `Surat suara muncul di sini saat pemilihan dibuka Selasa, 1 Desember pukul 08.00 WIB.` appears, and `cogito--corner` is present.
  - **prepare, not set.** `Jadwal pemilihan belum diumumkan panitia.`
  - **ballot.** `Halo, voter1! Ketuk foto paslon pilihanmu.` appears. `cogito--inline` sits inside the hero: DOMXPath `//section[contains(@class,"vote-hero--ballot")]//aside[@data-cogito]` returns 1 node. There is no `cogito--corner`.
  - **closed, not voted.** `Hasil penghitungan ada di halaman awal.` and the action `Lihat hasil`.
  - **receipt.** `Terima kasih sudah memilih!` appears, the image src contains `cogito/salute`, and `assertDontSee('Ketua 1')`.
- [ ] Step 2: Run `--filter=VotePageTest`. Expected: the new tests FAIL.
- [ ] Step 3: Implement the includes and the inline CSS.
  - **Ballot hero:** grid with the text on the left and COGITO on the right. On phones the figure is 72px, next to the title, and the bubble goes below it, full width.
  - **Receipt:** figure 120px to the right of the box, and the bubble above it.
- [ ] Step 4: Run `php artisan test`. Expected: all PASS.
- [ ] Step 5: Commit `feat(cogito): mascot on every /vote state`.

### Task 4: Browser behaviour and visual pass

**Files:**
- Test: scratchpad `shots/cogito.mjs`. Server :8010, DB testing, seeded with `seed-state.php` and `seed-voter.php`.

- [ ] **Step 1: Write `cogito.mjs`.** Checks:
  1. **Homepage, open, guest.** The bubble is hidden at load and visible by 1500ms, and line 1 is the first spec line.
  2. **Lanjut.** Lanjut shows line 2, then line 3. On line 3 Lanjut is hidden and "Masuk untuk memilih" is visible with href `/login`.
  3. **Tutup persists.** Tutup hides the bubble. After a reload it stays hidden while the figure stays visible. A tap on the figure reopens it at line 1.
  4. **Esc** closes the bubble.
  5. **Hidden behind an open letter.** Opening an envelope letter hides the bubble. Closing the letter brings it back, unless it was closed by the user.
  6. **Reduced motion.** The `currentSrc` of the img ends with `-still.webp`.
  7. **Widths.** At 320, 390, 768 and 1366 there is no horizontal overflow, and the figure and bubble buttons are 44px or more.
  8. **Ballot.** On `/vote` (open, logged-in voter) the cogito element is not fixed (computed `position` is not `fixed`). The "Kirim suara" button is not covered: `elementFromPoint` at its centre is the button or inside it.
  9. **Console.** No console errors.
- [ ] Step 2: Run `node cogito.mjs`. All checks should pass. Run `vote.mjs` and `envelope.mjs` again to confirm there are no regressions.
- [ ] Step 3: Take screenshots at 390 and 1366: homepage open (bubble), ballot hero, receipt, prepare. Review them against antislop, and fix any findings.
- [ ] Step 4: Commit any fixes as `fix(cogito): …`. Then run the final whole-branch review with a fresh reviewer, followed by the RED→GREEN fix pass.
