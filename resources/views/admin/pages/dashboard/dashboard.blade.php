@extends('admin.layouts.app')

@section('title', 'Ringkasan')

@php
    $admin = auth()->user();
    $adminName = $admin->details?->participant_name ?: $admin->username;
    $withAccount = $countVoted + $countNotVoted;
    $turnout = $withAccount > 0 ? (int) round($countVoted / $withAccount * 100) : 0;
    $status = $setting->status();
    [$statusLabel, $statusTone] = [
        'not_set' => ['Belum diatur', 'muted'],
        'upcoming' => ['Belum dibuka', 'warn'],
        'open' => ['Sedang dibuka', 'ok'],
        'closed' => ['Sudah ditutup', 'danger'],
    ][$status];
    $when = fn ($date) => $date?->copy()->locale('id')->translatedFormat('l, j F Y') . ' pukul ' . $date?->format('H.i') . ' WIB';
@endphp

@section('content')
    <p class="a-lead">Halo, {{ $adminName }}. Ini keadaan pemilihan periode {{ $setting->periodLabel() }} saat ini.</p>

    <div class="a-stats">
        <div class="a-stat">
            <p class="a-stat__value">{{ $countParticipant }}</p>
            <p class="a-stat__label">Peserta terdaftar</p>
        </div>
        <div class="a-stat">
            <p class="a-stat__value">{{ $withAccount }}</p>
            <p class="a-stat__label">Sudah punya akun</p>
        </div>
        <div class="a-stat">
            <p class="a-stat__value">{{ $countVoted }}</p>
            <p class="a-stat__label">Sudah memilih</p>
        </div>
        <div class="a-stat">
            <p class="a-stat__value">{{ $countNotVoted }}</p>
            <p class="a-stat__label">Belum memilih</p>
        </div>
        <div class="a-stat">
            <p class="a-stat__value">{{ $countCandidate }}</p>
            <p class="a-stat__label">Paslon</p>
        </div>
        <div class="a-stat">
            <p class="a-stat__value">{{ $countAdmin }}</p>
            <p class="a-stat__label">Admin</p>
        </div>
    </div>

    <div class="a-grid-2">
        <section class="a-card" aria-labelledby="partisipasi">
            <div class="a-card__head">
                <h2 id="partisipasi" class="a-card__title">Partisipasi</h2>
                <span class="a-badge a-badge--muted">{{ $turnout }}%</span>
            </div>
            <div class="a-card__body">
                <p class="a-stat__value">{{ $countVoted }} dari {{ $withAccount }}</p>
                <p class="a-help" style="margin-bottom: 14px">pemilih yang sudah punya akun telah memberikan suara.</p>
                <div class="a-bar" role="img" aria-label="{{ $turnout }} persen sudah memilih"><span style="width: {{ $turnout }}%"></span></div>
            </div>
        </section>

        <section class="a-card" aria-labelledby="jadwal">
            <div class="a-card__head">
                <h2 id="jadwal" class="a-card__title">Jadwal</h2>
                <span class="a-badge a-badge--{{ $statusTone }}">{{ $statusLabel }}</span>
            </div>
            <div class="a-card__body">
                @if ($setting->vote_start && $setting->vote_end)
                    <dl class="a-dl">
                        <div><dt>Dibuka</dt><dd>{{ $when($setting->vote_start) }}</dd></div>
                        <div><dt>Ditutup</dt><dd>{{ $when($setting->vote_end) }}</dd></div>
                    </dl>
                @else
                    <p class="a-help">Jadwal belum diatur, jadi pemilihan belum bisa dibuka.</p>
                @endif
                <p style="margin: 16px 0 0">
                    <a class="a-btn a-btn--ghost a-btn--sm" href="{{ url('/back-admin-logged/election-schedule') }}">
                        @include('admin.components.icon', ['name' => 'calendar'])
                        Atur jadwal
                    </a>
                </p>
            </div>
        </section>
    </div>

    <section class="a-card" aria-labelledby="hasil">
        <div class="a-card__head">
            <h2 id="hasil" class="a-card__title">Hasil sementara</h2>
            <a class="a-btn a-btn--ghost a-btn--sm" href="{{ url('/back-admin-logged/live-count') }}">
                @include('admin.components.icon', ['name' => 'chart'])
                Lihat hasil suara
            </a>
        </div>
        <div class="a-card__body">
            @include('admin.components.tally', ['results' => $results])
            <p class="a-help" style="margin-top: 16px">Total {{ $totalVotes }} suara masuk. Hasil ini hanya terlihat oleh admin sampai pemilihan ditutup.</p>
        </div>
    </section>
@endsection
