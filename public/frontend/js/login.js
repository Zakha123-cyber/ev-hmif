// Halaman login: tombol tampilkan/sembunyikan password, dan tombol Masuk tidak
// bisa ditekan dua kali saat formulir sedang dikirim.
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('[data-login-form]');
        if (!form) return;

        const password = form.querySelector('#password');
        const toggle = form.querySelector('[data-password-toggle]');
        const submit = form.querySelector('[data-login-submit]');

        // Tombol hanya muncul bila JS jalan (tanpa JS ia tidak berguna).
        toggle.hidden = false;
        toggle.addEventListener('click', function () {
            const show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.textContent = show ? 'Sembunyikan' : 'Tampilkan';
            toggle.setAttribute('aria-pressed', String(show));
            password.focus();
        });

        form.addEventListener('submit', function (event) {
            if (submit.disabled) {
                event.preventDefault();
                return;
            }
            // Password dikembalikan tersembunyi sebelum halaman berganti.
            password.type = 'password';
            submit.disabled = true;
            submit.textContent = 'Memeriksa…';
        });

        // Kembali lewat tombol Back: pulihkan tombol yang sempat dikunci.
        window.addEventListener('pageshow', function () {
            submit.disabled = false;
            submit.textContent = 'Masuk';
        });
    });
})();
