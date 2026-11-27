@extends('layouts.vote')

@section('title', $user->status === 'voted' ? 'Suaramu sudah tercatat' : 'Surat suara')

@section('content')
    @if ($user->status === 'voted')
        @include('partials.vote-receipt', ['votedAt' => $votedAt, 'setting' => $setting, 'justVoted' => $justVoted])
    @else
        {{-- Surat suara: Task 4 --}}
    @endif
@endsection
