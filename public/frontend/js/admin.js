// Panel admin: perilaku bersama untuk semua halaman. Tanpa pustaka luar.
(function () {
    'use strict';

    function $(selector, root) { return (root || document).querySelector(selector); }
    function $$(selector, root) { return Array.from((root || document).querySelectorAll(selector)); }

    // ---------- Laci menu (di bawah 1024px) ----------
    function setupDrawer() {
        const drawer = $('[data-drawer]');
        const openBtn = $('[data-drawer-open]');
        const overlay = $('[data-drawer-overlay]');
        if (!drawer || !openBtn) return;

        function open() {
            drawer.classList.add('is-open');
            overlay.hidden = false;
            openBtn.setAttribute('aria-expanded', 'true');
            const first = $('.a-nav__link', drawer);
            if (first) first.focus();
        }
        function close() {
            if (!drawer.classList.contains('is-open')) return;
            drawer.classList.remove('is-open');
            overlay.hidden = true;
            openBtn.setAttribute('aria-expanded', 'false');
            openBtn.focus();
        }

        openBtn.addEventListener('click', open);
        overlay.addEventListener('click', close);
        $$('[data-drawer-close]', drawer).forEach(function (b) { b.addEventListener('click', close); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    }

    // ---------- Pesan sukses / galat ----------
    function setupFlash() {
        $$('[data-flash-close]').forEach(function (btn) {
            btn.addEventListener('click', function () { btn.closest('.a-flash').remove(); });
        });
    }

    // ---------- Konfirmasi untuk form[data-confirm] ----------
    function setupConfirm() {
        const dialog = $('#a-confirm');
        const canDialog = dialog && typeof dialog.showModal === 'function';
        let pending = null;

        $$('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === '1') return; // sudah disetujui
                event.preventDefault();
                const message = form.dataset.confirm;
                if (!canDialog) {
                    if (window.confirm(message)) send(form);
                    return;
                }
                pending = form;
                $('[data-confirm-text]', dialog).textContent = message;
                const yes = $('[data-confirm-yes]', dialog);
                yes.textContent = form.dataset.confirmLabel || 'Ya, lanjutkan';
                dialog.showModal();
            });
        });

        function send(form) {
            form.dataset.confirmed = '1';
            const submit = $('[type="submit"]', form);
            if (submit) {
                submit.disabled = true;
                if (form.dataset.busy) submit.textContent = form.dataset.busy;
            }
            form.submit();
        }

        if (!canDialog) return;
        $('[data-confirm-no]', dialog).addEventListener('click', function () { dialog.close(); });
        $('[data-confirm-yes]', dialog).addEventListener('click', function () {
            const form = pending;
            pending = null;
            dialog.close();
            if (form) send(form);
        });
        dialog.addEventListener('close', function () { pending = null; });
    }

    // ---------- Dialog biasa (mis. impor Excel) ----------
    function setupDialogs() {
        $$('[data-dialog-open]').forEach(function (btn) {
            const dialog = $(btn.dataset.dialogOpen);
            if (!dialog || typeof dialog.showModal !== 'function') return;
            btn.addEventListener('click', function () { dialog.showModal(); });
            dialog.addEventListener('close', function () { btn.focus(); });
        });
        $$('dialog [data-dialog-close]').forEach(function (btn) {
            btn.addEventListener('click', function () { btn.closest('dialog').close(); });
        });
        $$('dialog[data-open-on-load]').forEach(function (dialog) {
            if (typeof dialog.showModal === 'function') dialog.showModal();
        });
    }

    // ---------- Tabel: cari & urutkan ----------
    function setupTables() {
        $$('[data-table-filter]').forEach(function (input) {
            const table = $(input.dataset.tableFilter);
            if (!table) return;
            const rows = $$('tbody tr', table);
            // Yang dicari hanya isi data, bukan teks tombol di kolom Aksi.
            const texts = rows.map(function (row) {
                return $$('td:not([data-label="Aksi"])', row).map(function (td) { return td.textContent; }).join(' ').toLowerCase();
            });
            const count = $('[data-table-count="' + input.dataset.tableFilter + '"]');
            const empty = $('[data-table-empty="' + input.dataset.tableFilter + '"]');

            function apply() {
                const q = input.value.trim().toLowerCase();
                let shown = 0;
                rows.forEach(function (row, i) {
                    const hit = !q || texts[i].includes(q);
                    row.hidden = !hit;
                    if (hit) shown++;
                });
                if (count) count.textContent = 'Menampilkan ' + shown + ' dari ' + rows.length;
                if (empty) empty.hidden = shown !== 0;
            }
            input.addEventListener('input', apply);
            apply();
        });

        $$('th [data-sort]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const th = btn.closest('th');
                const table = btn.closest('table');
                const index = Array.prototype.indexOf.call(th.parentNode.children, th);
                const asc = th.getAttribute('aria-sort') !== 'ascending';
                $$('thead th', table).forEach(function (h) { h.removeAttribute('aria-sort'); });
                th.setAttribute('aria-sort', asc ? 'ascending' : 'descending');

                const body = $('tbody', table);
                const rows = $$('tr', body);
                rows.sort(function (a, b) {
                    const x = (a.children[index] || {}).textContent.trim();
                    const y = (b.children[index] || {}).textContent.trim();
                    const nx = Number(x), ny = Number(y);
                    const cmp = (x !== '' && y !== '' && !isNaN(nx) && !isNaN(ny)) ? nx - ny : x.localeCompare(y, 'id', { numeric: true });
                    return asc ? cmp : -cmp;
                });
                rows.forEach(function (r) { body.appendChild(r); });
            });
        });

        // Penyaring daftar <select> (verifikasi): sembunyikan opsi yang tidak cocok.
        $$('[data-select-filter]').forEach(function (input) {
            const select = $(input.dataset.selectFilter);
            if (!select) return;
            input.addEventListener('input', function () {
                const q = input.value.trim().toLowerCase();
                let firstMatch = null;
                $$('option', select).forEach(function (opt) {
                    if (!opt.value) return;
                    const hit = !q || opt.textContent.toLowerCase().includes(q);
                    opt.hidden = !hit;
                    if (hit && !firstMatch) firstMatch = opt;
                });
                // Jangan biarkan peserta yang tersaring keluar tetap terpilih diam-diam.
                const current = select.selectedOptions[0];
                if (q && (!current || current.hidden || !current.value)) select.value = firstMatch ? firstMatch.value : '';
            });
        });
    }

    // ---------- Unggah gambar: pratinjau ----------
    function setupFilePreview() {
        $$('input[type="file"][data-file-preview]').forEach(function (input) {
            const scope = input.closest('[data-upload]') || document;
            const img = $('[data-preview]', scope);
            const empty = $('[data-preview-empty]', scope);
            const name = $('[data-file-name]', scope);
            input.addEventListener('change', function () {
                const file = input.files && input.files[0];
                if (!file) return;
                if (img) {
                    img.src = URL.createObjectURL(file);
                    img.hidden = false;
                }
                if (empty) empty.hidden = true;
                if (name) name.textContent = file.name;
            });
        });
    }

    // ---------- Acak ulang username/password ----------
    function setupRegenerate() {
        $$('[data-regenerate]').forEach(function (btn) {
            const target = $(btn.dataset.target);
            if (!target) return;
            btn.addEventListener('click', function () {
                btn.disabled = true;
                fetch(btn.dataset.regenerate, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (res) {
                        if (!res.ok) throw new Error(res.status);
                        return res.text();
                    })
                    .then(function (text) { target.value = text.trim(); })
                    .catch(function () { window.alert('Gagal membuat nilai baru. Muat ulang halaman lalu coba lagi.'); })
                    .finally(function () { btn.disabled = false; });
            });
        });
    }

    // ---------- Tampilkan / sembunyikan password ----------
    function setupReveal() {
        $$('[data-reveal]').forEach(function (btn) {
            const target = $(btn.dataset.reveal);
            if (!target) return;
            btn.hidden = false;
            btn.addEventListener('click', function () {
                let shown;
                if (target.tagName === 'INPUT') {
                    shown = target.type === 'password';
                    target.type = shown ? 'text' : 'password';
                } else {
                    shown = target.hidden;
                    target.hidden = !shown;
                    const mask = $(btn.dataset.mask || '');
                    if (mask) mask.hidden = shown;
                }
                btn.setAttribute('aria-pressed', String(shown));
                btn.lastChild.textContent = shown ? ' Sembunyikan' : ' Tampilkan';
            });
        });
    }

    // ---------- Kirim email akun: pilih email kampus atau cadangan ----------
    function setupSendEmail() {
        const dialog = $('#kirim-email');
        if (!dialog || typeof dialog.showModal !== 'function') return; // tanpa dialog: form baris terkirim biasa
        const form = $('[data-send-form]', dialog);
        const backupField = $('[data-send-backup]', dialog);
        const backupInput = $('#send-backup', dialog);
        let opener = null;

        function syncTarget(focus) {
            const backup = $('input[name="target"]:checked', form).value === 'backup';
            backupField.hidden = !backup;
            backupInput.required = backup;
            if (backup && focus) backupInput.focus();
        }
        $$('input[name="target"]', form).forEach(function (r) {
            r.addEventListener('change', function () { syncTarget(true); });
        });

        $$('form[data-send-email]').forEach(function (row) {
            row.addEventListener('submit', function (event) {
                event.preventDefault();
                opener = $('[type="submit"]', row);
                const isNew = row.dataset.new === '1';
                form.action = row.action;
                $('[data-send-id]', form).value = row.dataset.id;
                $('[data-send-title]', dialog).textContent = (isNew ? 'Buat akun & kirim ke ' : 'Kirim ulang email ke ') + row.dataset.name;
                $('[data-send-new]', dialog).hidden = !isNew;
                $('[data-send-campus]', dialog).textContent = row.dataset.campusEmail;
                backupInput.value = row.dataset.backupEmail || '';
                $('input[value="campus"]', form).checked = true;
                syncTarget(false);
                dialog.showModal();
            });
        });

        dialog.addEventListener('close', function () { if (opener) opener.focus(); });
        form.addEventListener('submit', function () {
            const submit = $('[type="submit"]', form);
            submit.disabled = true;
            submit.textContent = 'Memproses…';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupDrawer();
        setupFlash();
        setupConfirm();
        setupDialogs();
        setupTables();
        setupFilePreview();
        setupRegenerate();
        setupReveal();
        setupSendEmail();
    });
})();
