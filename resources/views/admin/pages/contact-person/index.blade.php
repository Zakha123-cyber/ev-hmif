@extends('admin.layouts.app')

@section('title', 'Kontak panitia')

@section('content')
    <section class="a-card" aria-labelledby="daftar-kontak">
        <div class="a-card__head">
            <h2 id="daftar-kontak" class="a-card__title">Daftar kontak</h2>
            <span class="a-help">{{ $contacts->count() }} kontak</span>
        </div>
        <div class="a-card__body">
            <p class="a-lead">
                Kontak WhatsApp panitia untuk peserta yang kesulitan login atau belum menerima email akun.
                Tampil di footer situs, halaman login, dan email akun, sesuai urutan di bawah.
            </p>

            @if ($contacts->isEmpty())
                <p class="a-empty">Belum ada kontak. Selama kosong, bagian "hubungi panitia" tidak ditampilkan di situs dan email.</p>
            @else
                <div class="a-tablewrap">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th scope="col">Urutan</th>
                                <th scope="col">Nama</th>
                                <th scope="col">WhatsApp</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contacts as $contact)
                                <tr>
                                    <td data-label="Urutan">{{ $contact->sort }}</td>
                                    <td data-label="Nama"><strong>{{ $contact->name }}</strong></td>
                                    <td data-label="WhatsApp"><a href="{{ $contact->waLink() }}" target="_blank" rel="noopener">{{ $contact->displayPhone() }}</a></td>
                                    <td data-label="Aksi">
                                        <div class="a-actions">
                                            <a class="a-btn a-btn--primary a-btn--sm" href="{{ url('/back-admin-logged/contact-person/' . $contact->id . '/edit') }}">Ubah<span class="visually-hidden"> {{ $contact->name }}</span></a>
                                            <form method="POST" action="{{ url('/back-admin-logged/contact-person/' . $contact->id) }}"
                                                data-confirm="Hapus kontak {{ $contact->name }} ({{ $contact->displayPhone() }})?" data-confirm-label="Ya, hapus kontak">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="a-btn a-btn--danger a-btn--sm">Hapus<span class="visually-hidden"> {{ $contact->name }}</span></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <section class="a-card" aria-labelledby="tambah-kontak">
        <div class="a-card__head">
            <h2 id="tambah-kontak" class="a-card__title">Tambah kontak</h2>
        </div>
        <div class="a-card__body">
            <form class="a-form" method="POST" action="{{ url('/back-admin-logged/contact-person') }}">
                @csrf
                @include('admin.pages.contact-person._fields', ['contact' => null, 'nextSort' => ($contacts->max('sort') ?? 0) + 1])
                <div class="a-form__foot">
                    <button type="submit" class="a-btn a-btn--accent">
                        @include('admin.components.icon', ['name' => 'plus'])
                        Tambah kontak
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
