@extends('layouts.vote')

@php
    $start = $setting->vote_start?->copy()->locale('id');
    $scheduled = $start && $start->isFuture();
@endphp

@section('title', $scheduled ? 'Pemilihan belum dibuka' : 'Jadwal belum diumumkan')

@section('content')
    <section class="vote-hero" aria-labelledby="judul">
        <div class="container vote-hero__inner">
            @if ($scheduled)
                <h1 id="judul" class="vote-hero__title">Pemilihan belum dibuka</h1>
                <p class="vote-hero__lead">
                    Pemilihan Ketua dan Wakil Ketua HMIF periode {{ config('election.period') }} dibuka
                    <strong>{{ $start->translatedFormat('l, j F Y') }}</strong> pukul {{ $start->format('H.i') }} WIB.
                    Surat suara muncul di halaman ini begitu waktunya tiba.
                </p>
                @include('partials.countdown', ['target' => $start, 'caption' => 'Dibuka dalam'])
            @else
                <h1 id="judul" class="vote-hero__title">Jadwal belum diumumkan</h1>
                <p class="vote-hero__lead">
                    Panitia belum menetapkan waktu pemilihan Ketua dan Wakil Ketua HMIF periode {{ config('election.period') }}.
                    Surat suara muncul di halaman ini saat pemilihan dibuka.
                </p>
            @endif
        </div>
    </section>

    @if ($candidates->isNotEmpty())
        <section class="vote-section" aria-labelledby="kenali">
            <div class="container">
                <div class="vote-section__intro">
                    <h2 id="kenali" class="section-title">Kenali paslon</h2>
                    <p>Sambil menunggu, buka amplop untuk membaca visi dan misi tiap pasangan calon.</p>
                </div>
                @include('partials.envelopes', ['candidates' => $candidates])
            </div>
        </section>
    @endif

    @php $cogito = \App\Support\CogitoLines::vote('prepare', $setting, $user); @endphp
    @include('partials.cogito', ['pose' => 'salute', 'variant' => 'corner', 'lines' => $cogito['lines']])
@endsection
