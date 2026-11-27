// Surat suara /vote: coblos (radio), konfirmasi bergaya surat, lalu surat dilipat
// masuk kotak suara sebelum form dikirim. Tanpa JS, form tetap terkirim langsung
// dan server yang memvalidasi.
(function () {
    const FOLD_MS = 800;
    const EMPTY_MESSAGE = 'Pilih salah satu paslon dulu.';

    // Halaman dibuka dari tombol Kembali (bfcache): muat ulang agar tidak menampilkan
    // surat suara dengan tombol "Mengirim…" yang macet. Server menentukan tampilannya.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted && document.querySelector('[data-ballot]')) window.location.reload();
    });

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('[data-ballot]');
        if (!form) return;

        const radios = Array.from(form.querySelectorAll('.ballot__radio'));
        const message = form.querySelector('[data-ballot-message]');
        const submit = form.querySelector('[data-ballot-submit]');
        const paper = form.querySelector('.ballot__paper');
        const confirm = document.getElementById('konfirmasi');
        const canConfirm = confirm && typeof confirm.showModal === 'function';
        const yes = confirm && confirm.querySelector('[data-confirm-yes]');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let sending = false;

        function chosen() {
            return radios.find(function (radio) { return radio.checked; });
        }

        function say(text) {
            // Dikosongkan dulu agar pesan yang sama diumumkan lagi oleh pembaca layar.
            message.textContent = '';
            window.setTimeout(function () { message.textContent = text; }, 50);
        }

        function lock() {
            sending = true;
            [submit, yes].forEach(function (button) {
                if (!button) return;
                button.disabled = true;
                button.textContent = 'Mengirim…';
            });
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () { message.textContent = ''; });
        });

        form.addEventListener('submit', function (event) {
            if (sending) {
                event.preventDefault();
                return;
            }
            const radio = chosen();
            if (!radio) {
                event.preventDefault();
                say(EMPTY_MESSAGE);
                radios[0].focus();
                return;
            }
            if (!canConfirm) {
                lock();
                return; // browser lama: kirim langsung
            }
            event.preventDefault();
            confirm.querySelector('[data-confirm-title]').textContent = 'Kirim suara untuk Paslon ' + radio.dataset.pair + '?';
            confirm.querySelector('[data-confirm-names]').textContent = radio.dataset.names;
            confirm.querySelector('[data-confirm-number]').textContent = radio.dataset.pair;
            confirm.showModal();
        });

        if (!canConfirm) return;

        confirm.querySelector('[data-confirm-no]').addEventListener('click', function () {
            confirm.close();
        });

        // "Periksa lagi" atau Escape: kembali ke kolom yang dicoblos.
        confirm.addEventListener('close', function () {
            if (!sending && chosen()) chosen().focus();
        });

        yes.addEventListener('click', function () {
            if (sending) return;
            // Yang dikirim harus paslon yang baru saja dikonfirmasi: selama animasi,
            // surat suara dibuat inert (radio tidak bisa diganti lewat panah, tapi
            // tetap ikut terkirim) dan pilihan ditegaskan lagi tepat sebelum submit.
            const picked = chosen();
            lock();
            if (paper) paper.inert = true;
            confirm.close();

            function submitPicked() {
                picked.checked = true;
                form.submit();
            }

            if (reduceMotion.matches || !paper) {
                submitPicked();
                return;
            }

            // Arahkan lipatan ke celah kotak suara (tinggi 104px) di tengah bawah layar.
            const box = paper.getBoundingClientRect();
            const slotY = window.innerHeight - 104 + 18;
            form.style.setProperty('--drop-x', (window.innerWidth / 2 - (box.left + box.width / 2)) + 'px');
            form.style.setProperty('--drop-y', (slotY - box.bottom) + 'px');
            form.classList.add('is-sending');

            let sent = false;
            function send() {
                if (sent) return;
                sent = true;
                submitPicked();
            }
            paper.addEventListener('animationend', send, { once: true });
            window.setTimeout(send, FOLD_MS + 150);
        });
    });
})();
