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
                "Buat akun &amp; kirim email" membuat akun untuk peserta yang belum terverifikasi lalu mengirim username dan
                password ke email kampus mereka. Peserta yang sudah punya akun dilewati.
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
                                            <form action="{{ url('/back-admin-logged/participant/email-backup', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="a-btn a-btn--ghost a-btn--sm">
                                                    Kirim ulang email
                                                </button>
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
