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
