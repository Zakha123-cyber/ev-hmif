# To-Do Perbaikan Bug — Election Vote HMIF

Hasil audit kode 24 Nov 2026. Temuan berasal dari pembacaan kode, belum semuanya dibuktikan lewat pengujian.
Urutan pengerjaan: **Kritis → Tinggi → Sedang → Rendah**. Setiap item kritis wajib punya feature test (Pest) sebelum ditandai selesai.

> ⚠️ Dokumen ini menjelaskan celah keamanan. Simpan repo sebagai **private** sampai item Kritis & Tinggi selesai.

## Keputusan yang perlu diambil dulu

- [x] **Sumber jadwal pemilihan** → diatur dinamis lewat panel admin (menu *Jadwal Pemilihan*, tabel `election_settings`). Dipakai oleh item #1, #2, dan #15.
- [x] **Kebijakan password peserta tersimpan plain text** (`participants.password_old`) → **dibiarkan** setelah email terkirim (untuk kirim ulang/cetak kartu). (item #9)

---

## 🔴 Kritis — mempengaruhi keabsahan hasil pemilihan

### [x] #1 Suara tetap bisa dikirim saat pemilihan "ditutup"
- **Lokasi:** `routes/web.php:33` (view `/vote` di-hardcode `vote_closed`), `routes/web.php:50` (`POST /vote-paslon` tanpa cek waktu)
- **Masalah:** Penutupan hanya di tampilan. Peserta yang login masih bisa POST langsung ke `/vote-paslon`.
- **Rencana fix:**
  - Buat `config/election.php` berisi jadwal (sumber sesuai keputusan di atas).
  - Buat middleware `EnsureVotingOpen` dan pasang di `POST /vote-paslon`.
  - Kembalikan `/vote` ke `VoteController@vote_page` yang memilih view `vote_prepare` / `vote_page` / `vote_closed` dari jadwal yang sama.
- **Selesai jika:** POST sebelum/sesudah jadwal ditolak (403/redirect) dan tidak membuat baris `counts`; test mencakup ketiga kondisi waktu.
- **✅ Selesai (24 Nov 2026):** model `ElectionSetting`, middleware `voting.open` (`EnsureVotingOpen`), `VoteController@vote_page`, halaman admin `/back-admin-logged/election-schedule`. Jika jadwal belum diatur → pemilihan dianggap **belum dibuka**. Test: `tests/Feature/VotingScheduleTest.php` (12 test).
  - Deploy: jalankan `php artisan migrate` di server, lalu atur jadwal di panel admin.

### [x] #2 Hasil suara bocor sebelum pemilihan selesai
- **Lokasi:** `app/Http/Controllers/HomeController.php`, `resources/views/welcome.blade.php:206-207`
- **Masalah:** Cek jam 15:30 hanya menyembunyikan `<canvas>`; `value_candidates` selalu tercetak di JS halaman.
- **Rencana fix:** Hitung & kirim data hasil **hanya** jika pemilihan sudah selesai (cek di controller, bukan di view). Bungkus `<script>` chart di dalam kondisi yang sama.
- **Selesai jika:** View source halaman `/` sebelum jadwal selesai tidak memuat angka suara.
- **✅ Selesai (24 Nov 2026):** `HomeController` hanya menghitung & mengirim hasil saat status jadwal `closed`; chart dan `<script>`-nya dibungkus `@if ($results_visible)`. Cek jam 15:30 di `welcome.blade.php` (chart & tombol "Pemilihan Selesai") diganti jadwal admin. Test: `tests/Feature/ResultsVisibilityTest.php` (4 test). Cek 15:30 di `auth/login.blade.php` masih ada → item #15.

### [x] #3 Satu orang bisa punya lebih dari satu akun → memilih berkali-kali
- **Lokasi:** migration `participants` (nim tidak unique), `ParticipantController::sendVerified` (`:269-305`), `ParticipantController::broadcast`, form `participant-verification/list.blade.php` (opsi status "not verified")
- **Masalah:**
  - Import Excel dua kali → peserta dengan NIM sama dobel.
  - `sendVerified` membuat `User` tanpa cek apakah peserta sudah punya akun.
  - Jika admin memilih status "not verified" saat verifikasi manual, akun sudah dibuat tetapi broadcast nanti membuat akun kedua.
- **Rencana fix:**
  - Bersihkan data duplikat, lalu migration: `unique` pada `participants.nim` dan `users.participant_id`.
  - `sendVerified`: tolak jika peserta sudah punya user; status selalu di-set `verified` (hapus opsi "not verified" di form).
  - `broadcast`: hanya proses peserta yang **belum punya user** (`whereDoesntHave('user')`).
- **Selesai jika:** Tidak mungkin membuat 2 user untuk 1 peserta / 1 NIM lewat jalur mana pun; test untuk import ganda, verifikasi ulang, dan verifikasi manual + broadcast.
- **✅ Selesai (24 Nov 2026):**
  - Migration `add_unique_constraints_to_participants_and_users`: unique `participants.nim` & `users.participant_id`. **Migrasi berhenti dengan daftar duplikat (tanpa menghapus data) jika data lama masih dobel** → bersihkan manual lalu migrate ulang. Rollback sudah diuji.
  - Import Excel melewati NIM yang sudah ada (termasuk baris ganda di file yang sama).
  - Tambah/edit peserta: validasi NIM unik.
  - Verifikasi manual: ditolak jika peserta sudah punya akun; status selalu `verified`; disimpan dalam transaksi. Dropdown status di form dihapus.
  - Broadcast: hanya peserta tanpa akun (`whereDoesntHave('user')`) — menutup juga jalur "admin mengedit status kembali ke not verified".
  - Test: `tests/Feature/ParticipantAccountsTest.php` (10 test). Test broadcast masih memakai GET → sesuaikan saat mengerjakan #7.
  - Deploy: **sebelum** `php artisan migrate` di server, cek duplikat: `SELECT nim, COUNT(*) FROM participants GROUP BY nim HAVING COUNT(*) > 1;`

### [x] #4 Menghapus kandidat/peserta ikut menghapus suara
- **Lokasi:** `database/migrations/2022_10_23_193938_create_counts_table.php:22-23` (`cascadeOnDelete`)
- **Rencana fix:**
  - Migration baru: ubah FK `counts` menjadi `restrictOnDelete`.
  - Controller `destroy` kandidat/peserta: tolak dengan pesan jelas jika sudah ada suara terkait.
- **Selesai jika:** Menghapus kandidat yang punya suara gagal dengan pesan error, jumlah `counts` tidak berubah.
- **✅ Selesai (24 Nov 2026):** migration `restrict_vote_deletion_on_counts_table` (FK `counts.candidate_id` & `counts.user_id` → RESTRICT; rollback diuji). `CandidateController::destroy` & `ParticipantController::destroy` menolak dengan pesan jika ada suara terkait. Test: `tests/Feature/VotePreservationTest.php` (6 test).
  - ⚠️ Konsekuensi: kandidat/peserta dari pemilihan sebelumnya yang sudah punya suara **tidak bisa dihapus lewat panel**. Untuk memulai periode baru perlu cara reset suara yang disengaja (lihat catatan "Fitur lanjutan" di bawah).

### [x] #5 Proses voting tidak atomik & tanpa validasi
- **Lokasi:** `app/Http/Controllers/VoteController.php:32-50`
- **Masalah:** Status `voted` disimpan sebelum suara tercatat; `candidate_id` tidak divalidasi; tidak ada unique constraint → request paralel bisa menghasilkan 2 suara.
- **Rencana fix:**
  - Validasi `candidate_id => required|integer|exists:candidates,id`.
  - Migration: `unique` pada `counts.user_id`.
  - Bungkus dalam `DB::transaction` + `lockForUpdate()` pada user; buat `Count` dulu, baru set status `voted`. Tangani duplicate-key sebagai "sudah memilih".
- **Selesai jika:** Test: kandidat tidak valid ditolak tanpa mengubah status; dua submit beruntun tetap 1 baris `counts`.
- **✅ Selesai (24 Nov 2026):** validasi `exists:candidates,id`; `DB::transaction` + `lockForUpdate()` pada user, suara disimpan dulu baru status `voted`; `UniqueConstraintViolationException` dianggap "sudah memilih". Migration `add_unique_user_id_to_counts_table` (berhenti dengan daftar duplikat jika ada suara ganda; rollback diuji). Test: `tests/Feature/VoteIntegrityTest.php` (5 test).
  - Deploy: sebelum migrate di server, cek `SELECT user_id, COUNT(*) FROM counts GROUP BY user_id HAVING COUNT(*) > 1;`

---

## 🟠 Tinggi — keamanan

### [ ] #6 Production berjalan dengan mode debug
- **Lokasi:** `.env` di server (cPanel) — **bukan perubahan kode**
- **Rencana fix:** `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`, `SESSION_SECURE_COOKIE=true`. Deploy vendor dengan `composer install --no-dev --optimize-autoloader`. Jalankan `php artisan config:cache`.
- **Selesai jika:** Error di production menampilkan halaman 500 generik tanpa stack trace.
- **📄 Panduan dibuat (24 Nov 2026):** `docs/DEPLOY.md` berisi konfigurasi `.env` production + urutan deploy lengkap (termasuk migrasi #1–#5). **Item ini baru dicentang setelah `.env` di server cPanel benar-benar diubah** — tidak bisa dilakukan dari repo.

### [ ] #7 Broadcast email dipicu lewat GET
- **Lokasi:** `routes/web.php:65`, tombol di `admin/pages/participant/list.blade.php`
- **Rencana fix:** Ubah ke `POST` + `@csrf` + konfirmasi; cegah eksekusi paralel (mis. `Cache::lock('broadcast')`).
- **Selesai jika:** `GET /back-admin-logged/participant/broadcast` mengembalikan 405.

### [ ] #8 Upload file tanpa validasi tipe
- **Lokasi:** `CandidateController.php:72, 133`, `ParticipantController.php:127, 200`
- **Rencana fix:** Validasi `image|mimes:jpg,jpeg,png,webp|max:5120`; simpan dengan `hashName()` (bukan nama asli); tambahkan `.htaccess` di `public/assets/upload/` yang menolak eksekusi PHP.
- **Selesai jika:** Upload `.php` / file non-gambar ditolak validasi.

### [ ] #9 Penanganan kredensial
- **Lokasi:** `database/seeders/UserSeeder.php`, `GeneratorController.php:17`, kolom `participants.password_old`
- **Rencana fix:**
  - Password admin seeder ambil dari `.env` (mis. `ADMIN_SEED_PASSWORD`), bukan hardcode. **Ganti password admin di production.**
  - Generator pakai `random_int()` / `Str::random()`, panjang minimal 8.
  - `password_old`: dibiarkan (sesuai keputusan di atas).

---

## 🟡 Sedang — fitur rusak

### [ ] #10 Broadcast berhenti di tengah saat ada error
- **Lokasi:** `ParticipantController.php:56-95`
- **Masalah:** `catch (Carbon\Exceptions\Exception)` salah kelas → error tidak tertangkap & transaksi tidak di-rollback; email dikirim sinkron dalam loop (rawan timeout); ada `echo` sisa debug.
- **Rencana fix:** `catch (\Throwable)`, transaksi per peserta, hapus `echo`, kumpulkan ringkasan berhasil/gagal. Pertimbangkan antrian (`QUEUE_CONNECTION=database` + cron `queue:work --stop-when-empty` di cPanel).

### [ ] #11 Import Excel tanpa validasi
- **Lokasi:** `app/Imports/ImportParticipant.php`
- **Rencana fix:** Pakai `WithHeadingRow` (atau `WithStartRow`), `WithValidation` (nim wajib & unik, status ∈ enum), lewati duplikat, jalankan dalam transaksi. Dokumentasikan format kolom Excel.

### [ ] #12 Generator username/password
- **Lokasi:** `GeneratorController.php`, `routes/web.php:30-31`
- **Masalah:** Cek keunikan tidak berfungsi (bandingkan string dengan model, hasil rekursi tidak di-return, password dicek ke kolom username); route terbuka untuk publik.
- **Rencana fix:** Loop `do { ... } while (User::where('username', $u)->exists())`; jadikan service class biasa; hapus kedua route publik (atau pindah ke grup admin).

### [ ] #13 Fitur email backup membaca kolom yang tidak ada
- **Lokasi:** `ParticipantController::email_backup` (`:143`)
- **Rencana fix:** Pilih salah satu: tambah migration kolom `email_backup` + input di form peserta, **atau** hapus fitur & tombolnya.

### [ ] #14 Redirect setelah login bisa error
- **Lokasi:** `app/Http/Controllers/RedirectRoleController.php:16, 22`
- **Masalah:** User tanpa role → `pluck('name')[0]` error; redirect ke `GET /logout` padahal logout hanya POST.
- **Rencana fix:** Pakai `hasRole()`; user tanpa role → logout via `Auth::logout()` + redirect ke login dengan pesan.

---

## ⚪ Rendah — perapian

### [ ] #15 Logika waktu & statistik
- Cek 15:30 di `welcome.blade.php` dan `auth/login.blade.php` berlaku setiap hari → ganti dengan jadwal dari #1.
- Dashboard & live count: `countVoted` ikut menghitung admin → filter hanya role `Participant`.

### [ ] #16 Kode Breeze yang tidak terpakai
- Route `register`, `forgot-password`, `reset-password`, `ProfileController`, dan test bawaan Breeze mengacu ke kolom `name`/`email` yang sudah dihapus.
- **Rencana fix:** Hapus route/controller/view/test yang tidak dipakai; ganti dengan test fitur nyata (login, vote, admin).

---

## Fitur lanjutan (bukan bug)

- [ ] **Reset / arsip periode pemilihan**: setelah #4, suara tidak bisa hilang lewat hapus kandidat/peserta. Perlu tombol admin yang disengaja (dengan konfirmasi ganda, hanya saat jadwal `closed`/`not_set`) untuk mengarsipkan/mengosongkan `counts`, akun peserta, dan jadwal sebelum periode baru. Sementara itu: lakukan manual via database (backup dulu).

## Setelah semua selesai

- [ ] `php artisan test` hijau
- [ ] Uji manual alur penuh di lokal: import → broadcast (`MAIL_MAILER=log`) → login peserta → vote → live count
- [ ] Update `deploy/` & catatan deploy, lalu deploy ke staging/production
