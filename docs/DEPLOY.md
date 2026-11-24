# Panduan Deploy ke cPanel

Struktur di server:

```
/home/hmifunej/election-vote-backend/      ← isi repo ini (tanpa folder public/)
/home/hmifunej/public_html/.../election-vote/  ← document root subdomain = isi public/
```

## 1. Konfigurasi `.env` production

Wajib di server (**jangan** salin `.env` lokal mentah-mentah):

```env
APP_ENV=production
APP_DEBUG=false          # true = stack trace, source code & query SQL tampil ke publik
APP_URL=https://election-vote.hmifunej.id
LOG_LEVEL=error
SESSION_SECURE_COOKIE=true
```

Isi juga kredensial DB & SMTP production.

Cek hasilnya: buka URL yang tidak ada / picu error → harus tampil halaman error generik tanpa stack trace.

## 2. Upload kode

1. Upload isi repo ke `/home/hmifunej/election-vote-backend` **kecuali** `public/`.
2. Upload isi `public/` ke document root subdomain, lalu timpa:
   - `deploy/cpanel-index.php` → `index.php`
   - `deploy/cpanel.htaccess` → `.htaccess` (berisi handler PHP 8.3 — server wajib PHP ≥ 8.3)
3. Jangan timpa folder upload yang sudah ada di server (`assets/upload/`, `files/`).

## 3. Dependensi (via Terminal cPanel)

```bash
cd ~/election-vote-backend
composer install --no-dev --optimize-autoloader
```

## 4. Database

**Backup database dulu.** Lalu cek data ganda — migrasi akan berhenti (tanpa menghapus data) jika masih ada:

```sql
SELECT nim, COUNT(*) FROM participants GROUP BY nim HAVING COUNT(*) > 1;
SELECT participant_id, COUNT(*) FROM users GROUP BY participant_id HAVING COUNT(*) > 1;
SELECT user_id, COUNT(*) FROM counts GROUP BY user_id HAVING COUNT(*) > 1;
```

Bersihkan duplikat secara manual bila ada, lalu:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. Setelah deploy

- [ ] Login admin → menu **Jadwal Pemilihan** → atur waktu buka & tutup (tanpa jadwal, pemilihan dianggap belum dibuka).
- [ ] Ganti password akun admin bawaan.
- [ ] Uji login peserta & halaman `/vote` (status sesuai jadwal).
- [ ] Pastikan halaman `/` tidak menampilkan hasil sebelum jadwal ditutup.
