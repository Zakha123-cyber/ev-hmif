@extends('layouts.vote')

@php
    $voted = $user->status === 'voted';
    $end = $setting->vote_end?->copy()->locale('id');
@endphp

@section('title', $voted ? 'Suaramu sudah tercatat' : 'Pemilihan sudah ditutup')

@section('content')
    @if ($voted)
        @include('partials.vote-receipt', ['votedAt' => $votedAt, 'setting' => $setting, 'justVoted' => $justVoted])
    @else
        <section class="vote-hero vote-hero--center" aria-labelledby="judul">
            <div class="container vote-hero__inner">
                <h1 id="judul" class="vote-hero__title">Pemilihan sudah ditutup</h1>
                <p class="vote-hero__lead">
                    Kamu tidak memberikan suara pada pemilihan ini.
                    @if ($end)
                        Pemilihan ditutup {{ $end->translatedFormat('l, j F Y') }} pukul {{ $end->format('H.i') }} WIB.
                    @endif
                </p>
                <p class="vote-hero__actions">
                    <a class="btn btn--primary" href="{{ url('/') }}#hasil">Lihat hasil</a>
                </p>
            </div>
        </section>

        @php $cogito = \App\Support\CogitoLines::vote('closed', $setting, $user); @endphp
        @include('partials.cogito', ['pose' => 'salam', 'variant' => 'corner', 'key' => $cogito['key'], 'lines' => $cogito['lines']])
    @endif
@endsection
