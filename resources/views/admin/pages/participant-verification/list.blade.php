@extends('admin.layouts.app')

@section('title', 'Verifikasi akun')

@section('content')
    <section class="a-card" aria-labelledby="buat-akun">
        <div class="a-card__head">
            <h2 id="buat-akun" class="a-card__title">Buat akun peserta</h2>
            <span class="a-badge a-badge--warn">{{ $getParticipant->count() }} menunggu</span>
        </div>
        <div class="a-card__body">
            <p class="a-lead">
                Untuk peserta yang belum punya akun. Username dan password dibuat otomatis; tekan "Acak ulang" bila ingin yang lain.
                Setelah disimpan, peserta berstatus terverifikasi dan bisa login.
            </p>

            @if ($getParticipant->isEmpty())
                <p class="a-empty">Semua peserta sudah punya akun.</p>
            @else
                <form class="a-form" method="POST" action="{{ url('/back-admin-logged/participant-verification/verified') }}">
                    @csrf

                    <div class="a-field">
                        <label class="a-label" for="pilih-peserta">Peserta</label>
                        <input class="a-input" type="search" placeholder="Ketik NIM atau nama untuk menyaring"
                            data-select-filter="#pilih-peserta" aria-label="Saring daftar peserta">
                        <select class="a-input" id="pilih-peserta" name="nim" @error('nim') aria-invalid="true" @enderror>
                            <option value="">Pilih peserta</option>
                            @foreach ($getParticipant as $item)
                                <option value="{{ $item->id }}" @selected(old('nim') == $item->id)>{{ $item->nim }} - {{ $item->participant_name }}</option>
                            @endforeach
                        </select>
                        @error('nim')
                            <p class="a-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="a-form__row">
                        <div class="a-field">
                            <label class="a-label" for="new_username">Username</label>
                            <div class="a-inputgroup">
                                <input class="a-input" id="new_username" name="username" type="text" readonly value="{{ old('username', $getRandUsername) }}">
                                <button type="button" class="a-btn a-btn--ghost" data-regenerate="{{ url('/back-admin-logged/generate-username') }}" data-target="#new_username">
                                    @include('admin.components.icon', ['name' => 'refresh'])
                                    Acak ulang
                                </button>
                            </div>
                            @error('username')
                                <p class="a-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="a-field">
                            <label class="a-label" for="new_password">Password</label>
                            <div class="a-inputgroup">
                                <input class="a-input" id="new_password" name="password" type="password" readonly value="{{ old('password', $getRandPassword) }}">
                                <button type="button" class="a-btn a-btn--ghost" data-reveal="#new_password" aria-pressed="false" hidden>
                                    @include('admin.components.icon', ['name' => 'eye'])
                                    Tampilkan
                                </button>
                                <button type="button" class="a-btn a-btn--ghost" data-regenerate="{{ url('/back-admin-logged/generate-password') }}" data-target="#new_password">
                                    @include('admin.components.icon', ['name' => 'refresh'])
                                    Acak ulang
                                </button>
                            </div>
                            @error('password')
                                <p class="a-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="a-field" style="max-width: 320px">
                        <label class="a-label" for="whatsapp_number">Nomor WhatsApp</label>
                        <input class="a-input" id="whatsapp_number" name="whatsapp_number" type="tel" inputmode="numeric"
                            value="{{ old('whatsapp_number', '62') }}" @error('whatsapp_number') aria-invalid="true" @enderror>
                        @error('whatsapp_number')
                            <p class="a-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="a-form__foot">
                        <button type="submit" class="a-btn a-btn--accent">Buat akun</button>
                    </div>
                </form>
            @endif
        </div>
    </section>

    <section class="a-card" aria-labelledby="terverifikasi-judul">
        <div class="a-card__head">
            <h2 id="terverifikasi-judul" class="a-card__title">Peserta terverifikasi</h2>
            <p class="a-count" data-table-count="#terverifikasi">{{ $getParticpantVerified->count() }} peserta</p>
        </div>
        <div class="a-card__body">
            @if ($getParticpantVerified->isEmpty())
                <p class="a-empty">Belum ada peserta terverifikasi.</p>
            @else
                <div class="a-toolbar">
                    <label class="a-search">
                        <span class="visually-hidden">Cari peserta terverifikasi</span>
                        @include('admin.components.icon', ['name' => 'search'])
                        <input class="a-input" type="search" placeholder="Cari nama, NIM, atau WhatsApp" data-table-filter="#terverifikasi">
                    </label>
                </div>
                <div class="a-tablewrap">
                    <table class="a-table" id="terverifikasi">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col" aria-sort="ascending"><button type="button" class="a-sort" data-sort>Nama</button></th>
                                <th scope="col"><button type="button" class="a-sort" data-sort>NIM</button></th>
                                <th scope="col">WhatsApp</th>
                                <th scope="col">Username</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($getParticpantVerified->sortBy('participant_name', SORT_NATURAL | SORT_FLAG_CASE) as $item)
                                <tr>
                                    <td class="a-table__num" data-label="No">{{ $loop->iteration }}</td>
                                    <td data-label="Nama"><strong>{{ $item->participant_name }}</strong></td>
                                    <td data-label="NIM">{{ $item->nim }}</td>
                                    <td data-label="WhatsApp">{{ $item->whatsapp_number ?: '-' }}</td>
                                    <td data-label="Username">{{ $item->username_old ?: '-' }}</td>
                                    <td data-label="Aksi">
                                        <div class="a-actions">
                                            <a class="a-btn a-btn--primary a-btn--sm" href="{{ url('/back-admin-logged/participant-verification/edit/' . $item->id) }}">Ubah akun<span class="visually-hidden"> {{ $item->participant_name }}</span></a>
                                            <form action="{{ url('/back-admin-logged/participant-verification/info', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="a-btn a-btn--ghost a-btn--sm">Info akun</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="a-empty" data-table-empty="#terverifikasi" hidden>Tidak ada peserta yang cocok dengan pencarian.</p>
            @endif
        </div>
    </section>
@endsection
