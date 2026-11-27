// Slider foto bersama di hero: crossfade tiap 7 detik.
// Tidak berjalan sama sekali jika pengguna memilih "kurangi gerakan".
(function () {
    const INTERVAL = 7000;

    document.addEventListener('DOMContentLoaded', function () {
        const slider = document.querySelector('[data-slider]');
        if (!slider) return;

        const slides = Array.from(slider.querySelectorAll('.hero__slide'));
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (slides.length < 2 || reduceMotion.matches) return;

        let current = 0;
        let timer = null;

        function show(next) {
            slides[current].classList.remove('is-active');
            current = next;
            slides[current].classList.add('is-active');
        }

        function start() {
            if (timer || document.hidden) return;
            timer = window.setInterval(function () {
                show((current + 1) % slides.length);
            }, INTERVAL);
        }

        function stop() {
            window.clearInterval(timer);
            timer = null;
        }

        // Hemat baterai: berhenti saat tab tidak terlihat.
        document.addEventListener('visibilitychange', function () {
            document.hidden ? stop() : start();
        });

        start();
    });
})();

// Navbar tetap: transparan di atas foto, solid begitu halaman digulir.
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const header = document.querySelector('[data-site-header]');
        if (!header) return;

        let queued = false;
        function sync() {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
            queued = false;
        }

        window.addEventListener('scroll', function () {
            if (queued) return;
            queued = true;
            window.requestAnimationFrame(sync);
        }, { passive: true });

        sync();
    });
})();

// Tutup surat hanya jika tekan DAN lepas terjadi di latar gelap, bukan saat
// menyeleksi teks yang berakhir di luar kertas atau mengklik tepi surat.
function closeOnBackdrop(letter) {
    let pressedOutside = false;
    function outsidePaper(event) {
        const box = letter.getBoundingClientRect();
        return event.target === letter && (event.clientX < box.left || event.clientX > box.right
            || event.clientY < box.top || event.clientY > box.bottom);
    }
    letter.addEventListener('pointerdown', function (event) {
        pressedOutside = outsidePaper(event);
    });
    letter.addEventListener('click', function (event) {
        if (pressedOutside && outsidePaper(event)) letter.close();
        pressedOutside = false;
    });
}

// Amplop paslon: tutup amplop terbuka dulu, baru surat (dialog) muncul.
// Terpisah dari slider agar tetap jalan saat "kurangi gerakan" aktif.
(function () {
    const FLAP_MS = 220;

    document.addEventListener('DOMContentLoaded', function () {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        document.querySelectorAll('[data-envelope]').forEach(function (envelope) {
            const letter = document.getElementById(envelope.getAttribute('commandfor'));
            if (!letter || typeof letter.showModal !== 'function') return;

            // JS mengambil alih dari atribut bawaan agar animasi sempat jalan.
            envelope.removeAttribute('command');
            envelope.removeAttribute('commandfor');

            envelope.addEventListener('click', function () {
                // Satu surat saja dalam satu waktu, termasuk saat dua amplop diketuk cepat.
                if (document.querySelector('.envelope.is-opening, .letter[open]')) return;
                envelope.classList.add('is-opening');
                // Poster mulai diunduh selama tutup amplop bergerak.
                letter.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
                    img.loading = 'eager';
                });
                window.setTimeout(function () {
                    letter.showModal();
                }, reduceMotion.matches ? 0 : FLAP_MS);
            });

            closeOnBackdrop(letter);

            letter.addEventListener('close', function () {
                envelope.classList.remove('is-opening');
                envelope.focus();
            });
        });
    });
})();

// Tombol "Visi & misi" di surat suara /vote: membuka surat paslon yang sama
// dengan amplop, tanpa animasi tutup amplop. Fokus kembali ke tombolnya.
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-letter-open]').forEach(function (button) {
            const letter = document.getElementById(button.dataset.letterOpen);
            if (!letter || typeof letter.showModal !== 'function') return;

            // JS mengambil alih dari atribut bawaan (dipakai bila JS tidak jalan).
            button.removeAttribute('command');
            button.removeAttribute('commandfor');

            button.addEventListener('click', function () {
                if (letter.open) return;
                letter.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
                    img.loading = 'eager';
                });
                letter.showModal();
            });
            closeOnBackdrop(letter);
            letter.addEventListener('close', function () {
                button.focus();
            });
        });
    });
})();

// Hitung mundur jadwal: dihitung ulang saat halaman dimuat dan saat pengunjung
// kembali ke tab. Tidak berdetik terus, jadi tidak ada gerak yang perlu dijeda.
// Memakai jam server (data-now) agar jam HP yang meleset tidak mengubah angka.
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const box = document.querySelector('[data-countdown]');
        if (!box) return;

        const target = Date.parse(box.dataset.countdown);
        const offset = Date.parse(box.dataset.now) - Date.now();
        const values = {
            days: box.querySelector('[data-unit="days"]'),
            hours: box.querySelector('[data-unit="hours"]'),
            minutes: box.querySelector('[data-unit="minutes"]'),
        };
        const pad = function (n) { return String(n).padStart(2, '0'); };

        function remainingMs() {
            return target - (Date.now() + offset);
        }

        // Saat waktunya tiba, muat ulang agar status & tombol mengikuti server
        // ("Masuk untuk memilih" muncul / hilang). Pengaman: paling sering sekali
        // per 15 detik, supaya jam yang sedikit meleset tidak memicu muat ulang beruntun.
        function reloadForNewStatus() {
            const key = 'countdown-reload:' + box.dataset.countdown;
            try {
                const last = Number(sessionStorage.getItem(key));
                if (last && Date.now() - last < 15000) return;
                sessionStorage.setItem(key, String(Date.now()));
            } catch (e) { /* tanpa storage: tetap muat ulang sekali */ }
            window.location.reload();
        }

        function update() {
            const ms = remainingMs();
            if (ms <= 0) {
                reloadForNewStatus();
                return;
            }
            const left = Math.floor(ms / 60000);
            values.days.textContent = Math.floor(left / 1440);
            values.hours.textContent = pad(Math.floor(left % 1440 / 60));
            values.minutes.textContent = pad(left % 60);
        }

        update();
        // Satu pengingat tepat di saat dibuka/ditutup (bukan hitungan berdetik).
        const ms = remainingMs();
        if (ms > 0 && ms < 2147483647) {
            window.setTimeout(reloadForNewStatus, ms + 1500);
        }
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) update();
        });
    });
})();
