@extends('admin.layouts.app')

@section('title', 'Jadwal & periode')

@php
    [$statusLabel, $statusTone] = [
        'not_set' => ['Belum diatur', 'muted'],
        'upcoming' => ['Belum dibuka', 'warn'],
        'open' => ['Sedang dibuka', 'ok'],
        'closed' => ['Sudah ditutup', 'danger'],
    ][$setting->status()];
@endphp

@section('content')
    <section class="a-card" aria-labelledby="jadwal-judul">
        <div class="a-card__head">
            <h2 id="jadwal-judul" class="a-card__title">Periode dan waktu pemilihan</h2>
            <span class="a-badge a-badge--{{ $statusTone }}">{{ $statusLabel }}</span>
        </div>
        <div class="a-card__body">
            <p class="a-lead">
                Peserta hanya bisa mengirim suara di antara waktu dibuka dan waktu ditutup (WIB). Selama jadwal belum diatur,
                pemilihan dianggap belum dibuka. Periode tampil di halaman awal, halaman memilih, halaman login, dan email pengumuman.
            </p>

            <form class="a-form" method="POST" action="{{ url('/back-admin-logged/election-schedule') }}">
                @csrf
                @method('PUT')

                <div class="a-field">
                    <label class="a-label" for="period">Periode kepengurusan</label>
                    <input class="a-input" style="max-width: 220px" type="text" id="period" name="period" inputmode="numeric"
                        placeholder="{{ config('election.period') }}" aria-describedby="period-help"
                        value="{{ old('period', $setting->period ?: config('election.period')) }}"
                        @error('period') aria-invalid="true" @enderror>
                    <p class="a-help" id="period-help">Dua tahun berurutan, misalnya 2025/2026.</p>
                    @error('period')
                        <p class="a-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="a-form__row a-form__row--2">
                    <div class="a-field">
                        <label class="a-label" for="vote_start">Waktu dibuka</label>
                        <input class="a-input" type="datetime-local" id="vote_start" name="vote_start"
                            value="{{ old('vote_start', $setting->vote_start?->format('Y-m-d\TH:i')) }}"
                            @error('vote_start') aria-invalid="true" @enderror>
                        @error('vote_start')
                            <p class="a-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="a-field">
                        <label class="a-label" for="vote_end">Waktu ditutup</label>
                        <input class="a-input" type="datetime-local" id="vote_end" name="vote_end"
                            value="{{ old('vote_end', $setting->vote_end?->format('Y-m-d\TH:i')) }}"
                            @error('vote_end') aria-invalid="true" @enderror>
                        @error('vote_end')
                            <p class="a-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="a-form__foot">
                    <button type="submit" class="a-btn a-btn--accent">Simpan periode dan jadwal</button>
                </div>
            </form>
        </div>
    </section>
@endsection
