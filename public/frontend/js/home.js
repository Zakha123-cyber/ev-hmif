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

// COGITO: balon bergiliran. Satu kalimat tampil ~7 detik, balon sembunyi ~4 detik,
// lalu kalimat acak berikutnya. Kalimat pertama selalu yang paling sesuai keadaan.
// Berhenti sementara saat pointer/fokus di balon, saat surat/konfirmasi terbuka,
// dan saat tab tidak terlihat. "Tutup" (atau Esc) hanya menyembunyikan kalimat ini;
// setelah jeda sedikit lebih lama COGITO muncul lagi dengan kalimat baru.
// Mengetuk COGITO langsung menampilkan kalimat lain.
(function () {
    const FIRST_MS = 900;
    const SHOW_MS = 7000;
    const GAP_MS = 4000;
    const CLOSED_GAP_MS = 8000;

    function shuffled(list) {
        const copy = list.slice();
        for (let i = copy.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [copy[i], copy[j]] = [copy[j], copy[i]];
        }
        return copy;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-cogito]').forEach(function (box) {
            const bubble = box.querySelector('[data-cogito-bubble]');
            const lineEl = box.querySelector('[data-cogito-line]');
            const action = box.querySelector('[data-cogito-action]');
            const close = box.querySelector('[data-cogito-close]');
            const figure = box.querySelector('[data-cogito-toggle]');
            let lines;
            try {
                lines = JSON.parse(box.querySelector('[data-cogito-lines]').textContent);
            } catch (e) {
                return; // tetap tampil kalimat pertama dari server
            }

            // Urutan: kalimat pertama dulu, sisanya acak; setelah habis diacak ulang
            // tanpa mengulang kalimat yang baru saja tampil.
            let order = [0].concat(shuffled(lines.map(function (_, i) { return i; }).slice(1)));
            let pos = 0;
            let timer = null;
            let hovering = false;
            let focused = false;

            function muted() {
                return !!document.querySelector('dialog[open]');
            }
            function paused() {
                return hovering || focused || muted() || document.hidden;
            }

            function render(index) {
                const line = lines[index];
                lineEl.textContent = line.text;
                if (line.action) {
                    action.textContent = line.action.label;
                    action.href = line.action.href;
                    action.hidden = false;
                } else {
                    action.hidden = true;
                }
            }

            function advance() {
                const previous = order[pos];
                pos++;
                if (pos >= order.length) {
                    order = shuffled(order);
                    if (order[0] === previous && order.length > 1) order.push(order.shift());
                    pos = 0;
                }
            }

            function schedule(fn, ms) {
                window.clearTimeout(timer);
                timer = paused() ? null : window.setTimeout(fn, ms);
            }

            // Animasi keluar (kebalikan animasi muncul), lalu baru disembunyikan.
            const OUT_MS = 180;
            let leaving = null;
            function hideBubble() {
                if (bubble.hidden) return;
                const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (reduce) {
                    bubble.hidden = true;
                    return;
                }
                bubble.classList.add('is-leaving');
                window.clearTimeout(leaving);
                leaving = window.setTimeout(function () {
                    bubble.classList.remove('is-leaving');
                    bubble.hidden = true;
                    leaving = null;
                }, OUT_MS);
            }
            function cancelLeaving() {
                window.clearTimeout(leaving);
                leaving = null;
                bubble.classList.remove('is-leaving');
            }

            function show() {
                cancelLeaving();
                render(order[pos]);
                bubble.hidden = false;
                schedule(rest, SHOW_MS);
            }

            function rest() {
                // Jangan sembunyikan kalimat yang sedang dibaca/difokus.
                if (focused || hovering) return;
                hideBubble();
                advance();
                schedule(show, GAP_MS);
            }

            // Setelah jeda (hover/fokus/surat/tab) berakhir, lanjutkan dari keadaan sekarang.
            function resume() {
                if (paused()) {
                    window.clearTimeout(timer);
                    timer = null;
                    return;
                }
                if (!timer) schedule(bubble.hidden ? show : rest, bubble.hidden ? GAP_MS : SHOW_MS);
            }

            function shut() {
                // Pointer berada di atas balon saat Tutup diklik; balon hilang tanpa
                // pointerleave, jadi lepaskan tahanannya di sini.
                hovering = false;
                focused = false;
                hideBubble();
                advance();
                figure.focus();
                schedule(show, CLOSED_GAP_MS);
            }

            close.hidden = false;
            render(0);
            bubble.hidden = true;
            bubble.classList.add('is-ready');

            schedule(show, FIRST_MS);

            close.addEventListener('click', shut);
            box.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !bubble.hidden) shut();
            });
            figure.addEventListener('click', function () {
                if (!bubble.hidden) advance();
                show();
            });

            bubble.addEventListener('pointerenter', function () { hovering = true; resume(); });
            bubble.addEventListener('pointerleave', function () { hovering = false; resume(); });
            // Hanya fokus keyboard yang menahan giliran; klik mouse pada COGITO tidak.
            box.addEventListener('focusin', function (event) {
                focused = bubble.contains(event.target) && event.target.matches(':focus-visible');
                resume();
            });
            box.addEventListener('focusout', function (event) {
                if (box.contains(event.relatedTarget)) return;
                focused = false;
                resume();
            });
            document.addEventListener('visibilitychange', resume);

            // Surat paslon / konfirmasi terbuka: balon disembunyikan dan giliran berhenti.
            new MutationObserver(function () {
                box.classList.toggle('is-muted', muted());
                resume();
            }).observe(document.body, { subtree: true, attributes: true, attributeFilter: ['open'] });
        });
    });
})();
