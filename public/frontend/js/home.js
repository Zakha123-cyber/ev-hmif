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

// COGITO: balon sapaan/arahan. Teks hanya berganti oleh aksi pengunjung (Lanjut
// atau mengetuk COGITO), tidak pernah sendiri. "Tutup" diingat selama sesi tab
// ini per halaman; mengetuk COGITO membukanya lagi dari kalimat pertama.
(function () {
    const DELAY_MS = 900;

    document.addEventListener('DOMContentLoaded', function () {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        document.querySelectorAll('[data-cogito]').forEach(function (box) {
            const bubble = box.querySelector('[data-cogito-bubble]');
            const lineEl = box.querySelector('[data-cogito-line]');
            const next = box.querySelector('[data-cogito-next]');
            const close = box.querySelector('[data-cogito-close]');
            const action = box.querySelector('[data-cogito-action]');
            const figure = box.querySelector('[data-cogito-toggle]');
            const storeKey = 'cogito-closed:' + box.dataset.cogitoKey;
            let lines;
            try {
                lines = JSON.parse(box.querySelector('[data-cogito-lines]').textContent);
            } catch (e) {
                return; // tetap tampil kalimat pertama dari server
            }
            let index = 0;

            function remembered() {
                try { return sessionStorage.getItem(storeKey) === '1'; } catch (e) { return false; }
            }
            function remember(closed) {
                try {
                    if (closed) sessionStorage.setItem(storeKey, '1');
                    else sessionStorage.removeItem(storeKey);
                } catch (e) { /* tanpa storage: hanya untuk halaman ini */ }
            }

            function render() {
                const last = index === lines.length - 1;
                lineEl.textContent = lines[index];
                next.hidden = last;
                if (action) action.hidden = !last;
            }
            function advance() {
                if (index >= lines.length - 1) return;
                const hadFocus = document.activeElement === next;
                index++;
                render();
                // Lanjut hilang di kalimat terakhir: pindahkan fokus ke aksi/Tutup.
                if (hadFocus && next.hidden) (action || close).focus();
            }
            function open() {
                index = 0;
                render();
                bubble.hidden = false;
            }
            function shut() {
                bubble.hidden = true;
                remember(true);
                figure.focus();
            }

            // Pembaca layar mendengar kalimat baru setelah Lanjut ditekan.
            lineEl.setAttribute('aria-live', 'polite');
            close.hidden = false;
            render();
            bubble.hidden = true;
            bubble.classList.add('is-ready');
            if (!remembered()) {
                window.setTimeout(open, reduceMotion.matches ? 0 : DELAY_MS);
            }

            next.addEventListener('click', advance);
            close.addEventListener('click', shut);
            figure.addEventListener('click', function () {
                if (bubble.hidden) {
                    remember(false);
                    open();
                } else {
                    advance();
                }
            });
            box.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !bubble.hidden) shut();
            });

            // Surat paslon / konfirmasi terbuka: balon disembunyikan agar tidak bertumpuk.
            function sync() {
                box.classList.toggle('is-muted', !!document.querySelector('dialog[open]'));
            }
            new MutationObserver(sync).observe(document.body, { subtree: true, attributes: true, attributeFilter: ['open'] });
        });
    });
})();
