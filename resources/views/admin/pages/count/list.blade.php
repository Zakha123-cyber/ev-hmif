@extends('admin.layouts.app')

@section('title', 'Hasil suara')

@php
    $withAccount = $countVoted + $countNotVoted;
    $turnout = $withAccount > 0 ? (int) round($countVoted / $withAccount * 100) : 0;
@endphp

@section('actions')
    <a class="a-btn a-btn--ghost" href="{{ url('/back-admin-logged/live-count') }}">
        @include('admin.components.icon', ['name' => 'refresh'])
        Perbarui angka
    </a>
@endsection

@section('content')
    <div class="a-stats">
        <div class="a-stat">
            <p class="a-stat__value">{{ $totalVotes }}</p>
            <p class="a-stat__label">Suara masuk</p>
        </div>
        <div class="a-stat">
            <p class="a-stat__value">{{ $countNotVoted }}</p>
            <p class="a-stat__label">Belum memilih</p>
        </div>
        <div class="a-stat">
            <p class="a-stat__value">{{ $turnout }}%</p>
            <p class="a-stat__label">Partisipasi ({{ $countVoted }} dari {{ $withAccount }})</p>
        </div>
    </div>

    <section class="a-card" aria-labelledby="perolehan">
        <div class="a-card__head">
            <h2 id="perolehan" class="a-card__title">Perolehan suara</h2>
        </div>
        <div class="a-card__body">
            @include('admin.components.tally', ['results' => $results])
        </div>
    </section>

    <section class="a-card" aria-labelledby="partisipasi">
        <div class="a-card__head">
            <h2 id="partisipasi" class="a-card__title">Partisipasi pemilih</h2>
        </div>
        <div class="a-card__body">
            <p class="a-help" style="margin-bottom: 10px">{{ $countVoted }} dari {{ $withAccount }} pemilih yang punya akun sudah memilih.</p>
            <div class="a-bar" role="img" aria-label="{{ $turnout }} persen sudah memilih"><span style="width: {{ $turnout }}%"></span></div>
        </div>
    </section>
@endsection
