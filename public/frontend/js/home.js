// Slider foto bersama di hero: crossfade tiap 7 detik, bisa dijeda.
// Tidak berjalan sama sekali jika pengguna memilih "kurangi gerakan".
(function () {
    const INTERVAL = 7000;

    document.addEventListener('DOMContentLoaded', function () {
        const slider = document.querySelector('[data-slider]');
        const toggle = document.querySelector('[data-slider-toggle]');
        if (!slider || !toggle) return;

        const slides = Array.from(slider.querySelectorAll('.hero__slide'));
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (slides.length < 2 || reduceMotion.matches) return;

        let current = 0;
        let timer = null;
        let pausedByUser = false;

        function show(next) {
            slides[current].classList.remove('is-active');
            current = next;
            slides[current].classList.add('is-active');
        }

        function start() {
            if (timer || pausedByUser || document.hidden) return;
            timer = window.setInterval(function () {
                show((current + 1) % slides.length);
            }, INTERVAL);
        }

        function stop() {
            window.clearInterval(timer);
            timer = null;
        }

        toggle.addEventListener('click', function () {
            pausedByUser = !pausedByUser;
            toggle.textContent = pausedByUser ? 'Putar foto' : 'Jeda foto';
            pausedByUser ? stop() : start();
        });

        // Hemat baterai: berhenti saat tab tidak terlihat.
        document.addEventListener('visibilitychange', function () {
            document.hidden ? stop() : start();
        });

        toggle.hidden = false;
        start();
    });
})();

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

            // Tutup hanya jika tekan DAN lepas terjadi di latar gelap, bukan saat
            // menyeleksi teks yang berakhir di luar kertas atau mengklik tepi surat.
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

            letter.addEventListener('close', function () {
                envelope.classList.remove('is-opening');
                envelope.focus();
            });
        });
    });
})();
