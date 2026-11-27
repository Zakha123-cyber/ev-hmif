@extends('admin.layouts.app')

@section('title', 'Ubah akun peserta')

@php $p = $getDetailParticipant; @endphp

@section('content')
    <section class="a-card">
        <div class="a-card__head">
            <h2 class="a-card__title">{{ $p->participant_name }}</h2>
            <span class="a-help">NIM {{ $p->nim }}</span>
        </div>
        <div class="a-card__body">
            <p class="a-lead">Mengubah username atau password langsung mengganti akun login peserta ini. Beri tahu peserta setelah menyimpan.</p>

            <form class="a-form" method="POST" action="{{ url('/back-admin-logged/participant-verification/update/' . $p->id) }}">
                @csrf
                @method('PUT')

                <div class="a-form__row">
                    <div class="a-field">
                        <label class="a-label" for="new_username">Username</label>
                        <div class="a-inputgroup">
                            <input class="a-input" id="new_username" name="username" type="text" readonly value="{{ old('username', $p->username_old) }}">
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
                            <input class="a-input" id="new_password" name="password" type="password" readonly value="{{ old('password', $p->password_old) }}">
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

                <div class="a-form__row a-form__row--2">
                    <div class="a-field">
                        <label class="a-label" for="whatsapp_number">Nomor WhatsApp</label>
                        <input class="a-input" id="whatsapp_number" name="whatsapp_number" type="tel" inputmode="numeric"
                            value="{{ old('whatsapp_number', $p->whatsapp_number) }}" @error('whatsapp_number') aria-invalid="true" @enderror>
                        @error('whatsapp_number')
                            <p class="a-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="a-field">
                        <label class="a-label" for="status">Status</label>
                        @php $status = old('status', $p->status); @endphp
                        <select class="a-input" id="status" name="status" @error('status') aria-invalid="true" @enderror>
                            <option value="verified" @selected($status === 'verified')>Terverifikasi</option>
                            <option value="not verified" @selected($status === 'not verified')>Belum terverifikasi</option>
                        </select>
                        @error('status')
                            <p class="a-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="a-form__foot">
                    <button type="submit" class="a-btn a-btn--accent">Simpan akun</button>
                    <a class="a-btn a-btn--ghost" href="{{ url('/back-admin-logged/participant-verification') }}">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
