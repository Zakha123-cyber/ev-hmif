@extends('admin.layouts.app')

@section('title', 'Peserta')

@section('actions')
    <a class="a-btn a-btn--ghost" href="{{ url('/back-admin-logged/participant/add') }}">
        @include('admin.components.icon', ['name' => 'plus'])
        Tambah peserta
    </a>
    <button type="button" class="a-btn a-btn--ghost" data-dialog-open="#import">
        @include('admin.components.icon', ['name' => 'upload'])
        Impor Excel
    </button>
    <form method="POST" action="{{ url('/back-admin-logged/participant/broadcast') }}"
        data-confirm="Buat akun dan kirim email ke semua peserta yang belum punya akun?"
        data-confirm-label="Ya, buat akun dan kirim" data-busy="Mengirim email…">
        @csrf
        <button type="submit" class="a-btn a-btn--accent">
            @include('admin.components.icon', ['name' => 'mail'])
            Buat akun &amp; kirim email
        </button>
    </form>
@endsection

@section('content')
    <section class="a-card" aria-labelledby="daftar">
        <div class="a-card__head">
            <h2 id="daftar" class="a-card__title">Daftar peserta</h2>
            <p class="a-count" data-table-count="#peserta">{{ $dataParticipant->count() }} peserta</p>
        </div>
        <div class="a-card__body">
            <p class="a-lead">
                "Buat akun &amp; kirim email" membuat akun untuk semua peserta yang belum punya akun lalu mengirim username dan
                password ke email kampus mereka. Untuk satu orang saja, pakai tombol di barisnya. Email cadangan ikut
                dikirimi bila diisi.
            </p>

            @if ($dataParticipant->isEmpty())
                <p class="a-empty">Belum ada peserta. Tambahkan satu per satu atau impor dari Excel.</p>
            @else
                <div class="a-toolbar">
                    <label class="a-search">
                        <span class="visually-hidden">Cari peserta</span>
                        @include('admin.components.icon', ['name' => 'search'])
                        <input class="a-input" type="search" placeholder="Cari nama, NIM, atau WhatsApp" data-table-filter="#peserta">
                    </label>
                </div>

                <div class="a-tablewrap">
                    <table class="a-table" id="peserta">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col" aria-sort="ascending"><button type="button" class="a-sort" data-sort>Nama</button></th>
                                <th scope="col"><button type="button" class="a-sort" data-sort>NIM</button></th>
                                <th scope="col">WhatsApp</th>
                                <th scope="col"><button type="button" class="a-sort" data-sort>Status</button></th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataParticipant->sortBy('participant_name', SORT_NATURAL | SORT_FLAG_CASE) as $item)
                                <tr>
                                    <td class="a-table__num" data-label="No">{{ $loop->iteration }}</td>
                                    <td data-label="Nama"><strong>{{ $item->participant_name }}</strong></td>
                                    <td data-label="NIM">{{ $item->nim }}</td>
                                    <td data-label="WhatsApp">{{ $item->whatsapp_number ?: '-' }}</td>
                                    <td data-label="Status">
                                        @if ($item->status === 'verified')
                                            <span class="a-badge a-badge--ok">Terverifikasi</span>
                                        @else
                                            <span class="a-badge a-badge--warn">Belum terverifikasi</span>
                                        @endif
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="a-actions">
                                            <a class="a-btn a-btn--primary a-btn--sm" href="{{ url('/back-admin-logged/participant/edit/' . $item->id) }}">Ubah<span class="visually-hidden"> {{ $item->participant_name }}</span></a>
                                            {{-- Tanpa JS: langsung kirim ke email kampus (+ cadangan bila ada).
                                                 Dengan JS: membuka dialog pilihan tujuan (#kirim-email). --}}
                                            <form action="{{ url('/back-admin-logged/participant/email-backup', $item->id) }}" method="POST"
                                                data-send-email data-id="{{ $item->id }}" data-name="{{ $item->participant_name }}"
                                                data-campus-email="{{ $item->campusEmail() }}" data-backup-email="{{ $item->email_backup }}"
                                                data-new="{{ $item->user ? '0' : '1' }}">
                                                @csrf
                                                <button type="submit" class="a-btn a-btn--ghost a-btn--sm">{{ $item->user ? 'Kirim ulang email' : 'Buat akun & kirim' }}<span class="visually-hidden"> {{ $item->participant_name }}</span></button>
                                            </form>
                                            <form action="{{ url('/back-admin-logged/participant/delete', $item->id) }}" method="POST"
                                                data-confirm="Hapus peserta {{ $item->participant_name }} ({{ $item->nim }})? Akunnya ikut terhapus."
                                                data-confirm-label="Ya, hapus peserta">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="a-btn a-btn--danger a-btn--sm">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="a-empty" data-table-empty="#peserta" hidden>Tidak ada peserta yang cocok dengan pencarian.</p>
            @endif
        </div>
    </section>

    {{-- Pilih tujuan email akun (diisi admin.js dari baris yang diklik; terbuka lagi bila ada galat) --}}
    @php
        $sendFor = old('participant_id') ? $dataParticipant->firstWhere('id', (int) old('participant_id')) : null;
    @endphp
    <dialog class="a-dialog" id="kirim-email" aria-labelledby="kirim-judul" @if ($sendFor && $errors->has('email_backup')) data-open-on-load @endif>
        <h2 id="kirim-judul" class="a-dialog__title" data-send-title>{{ $sendFor ? (($sendFor->user ? 'Kirim ulang email ke ' : 'Buat akun & kirim ke ') . $sendFor->participant_name) : 'Kirim email akun' }}</h2>
        <form method="POST" action="{{ $sendFor ? url('/back-admin-logged/participant/email-backup', $sendFor->id) : '' }}" class="a-form" data-send-form>
            @csrf
            <input type="hidden" name="participant_id" value="{{ $sendFor?->id }}" data-send-id>
            <p class="a-help" data-send-new @unless ($sendFor && ! $sendFor->user) hidden @endunless>
                Peserta ini belum punya akun. Akun dibuat dulu, lalu username dan password dikirim ke alamat yang dipilih.
            </p>

            <fieldset class="a-choice">
                <legend class="a-label">Kirim ke</legend>
                <label class="a-radio">
                    <input type="radio" name="target" value="campus" @checked(old('target', 'campus') === 'campus')>
                    <span><strong>Email kampus</strong><span class="a-help" data-send-campus>{{ $sendFor?->campusEmail() }}</span></span>
                </label>
                <label class="a-radio">
                    <input type="radio" name="target" value="backup" @checked(old('target') === 'backup')>
                    <span><strong>Email cadangan</strong><span class="a-help">Email pribadi peserta, misalnya Gmail.</span></span>
                </label>
                <div class="a-field" data-send-backup @unless (old('target') === 'backup') hidden @endunless>
                    <label class="a-label" for="send-backup">Alamat email cadangan</label>
                    <input class="a-input" id="send-backup" name="email_backup" type="email" autocomplete="off"
                        value="{{ old('email_backup', $sendFor?->email_backup) }}" aria-describedby="send-backup-help"
                        @error('email_backup') aria-invalid="true" @enderror>
                    <p class="a-help" id="send-backup-help">Alamat ini ikut disimpan ke data peserta.</p>
                    @error('email_backup')
                        <p class="a-error">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <div class="a-dialog__actions">
                <button type="button" class="a-btn a-btn--ghost" data-dialog-close>Batal</button>
                <button type="submit" class="a-btn a-btn--primary">
                    @include('admin.components.icon', ['name' => 'mail'])
                    Kirim email
                </button>
            </div>
        </form>
    </dialog>

    {{-- Impor Excel --}}
    <dialog class="a-dialog" id="import" aria-labelledby="import-judul" @error('file') data-open-on-load @enderror>
        <h2 id="import-judul" class="a-dialog__title">Impor peserta dari Excel</h2>
        <form method="POST" action="{{ url('/back-admin-logged/participant/import') }}" enctype="multipart/form-data" class="a-form">
            @csrf
            <div class="a-field">
                <label class="a-label" for="file">File .xlsx, .xls, atau .csv</label>
                <input class="a-input" id="file" type="file" name="file" accept=".xlsx,.xls,.csv" aria-describedby="file-help"
                    @error('file') aria-invalid="true" @enderror>
                <p class="a-help" id="file-help">
                    Kolom A berisi nama, kolom B berisi NIM (angka); keduanya wajib diisi. Baris judul boleh ada. NIM yang sudah terdaftar dilewati.
                    Semua peserta masuk sebagai belum terverifikasi.
                </p>
                @error('file')
                    <p class="a-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="a-dialog__actions">
                <button type="button" class="a-btn a-btn--ghost" data-dialog-close>Batal</button>
                <button type="submit" class="a-btn a-btn--primary">
                    @include('admin.components.icon', ['name' => 'upload'])
                    Impor
                </button>
            </div>
        </form>
    </dialog>
@endsection
