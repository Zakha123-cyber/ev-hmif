@extends('layouts.vote')

@section('title', $user->status === 'voted' ? 'Suaramu sudah tercatat' : 'Surat suara')

@section('content')
    @if ($user->status === 'voted')
        @include('partials.vote-receipt', ['votedAt' => $votedAt, 'setting' => $setting, 'justVoted' => $justVoted])
    @else
        @php
            $name = $user->details?->participant_name ?: $user->username;
            $columns = min($candidates->count(), 3);
        @endphp

        <section class="vote-hero vote-hero--ballot" aria-labelledby="judul">
            <div class="container vote-hero__inner">
                <h1 id="judul" class="vote-hero__title">Berikan suaramu</h1>
                <p class="vote-hero__lead">Halo, {{ $name }}. Ketuk foto paslon pilihanmu, lalu kirim. Suara hanya bisa dikirim satu kali.</p>
            </div>
        </section>

        <div class="ballot-area">
            <div class="container">
                @if ($candidates->isEmpty())
                    <p class="ballot__empty ballot__empty--paper">Daftar pasangan calon belum diumumkan.</p>
                @else
                    {{-- novalidate: pesan "belum memilih" ditampilkan sendiri oleh vote.js dalam bahasa Indonesia.
                         autocomplete="off": browser tidak memulihkan pilihan lama saat halaman dibuka ulang. --}}
                    <form class="ballot" data-ballot method="POST" action="{{ url('/vote-paslon') }}" autocomplete="off" novalidate>
                        @csrf
                        <div class="ballot__paper">
                            <header class="ballot__kop">
                                <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="">
                                <p>
                                    <strong>Surat suara</strong>
                                    <span>Pemilihan Ketua dan Wakil Ketua HMIF periode {{ config('election.period') }}</span>
                                </p>
                            </header>

                            <fieldset class="ballot__fieldset">
                                <legend class="visually-hidden">Pilih satu pasangan calon</legend>
                                <ol class="ballot__columns ballot__columns--{{ $columns }}">
                                    @foreach ($candidates as $candidate)
                                        @php [$posterWidth, $posterHeight] = $candidate->posterSize(); @endphp
                                        <li class="ballot__column">
                                            <label class="ballot__choice">
                                                <input type="radio" class="ballot__radio" name="candidate_id" value="{{ $candidate->id }}"
                                                    data-pair="{{ $candidate->candidate_pair }}"
                                                    data-names="{{ $candidate->leader_name }} dan {{ $candidate->vice_leader_name }}"
                                                    @checked(old('candidate_id') == $candidate->id)>
                                                <span class="ballot__face">
                                                    <span class="hex-number ballot__number" aria-hidden="true">{{ $candidate->candidate_pair }}</span>
                                                    <span class="ballot__photo">
                                                        @if ($candidate->hasPoster())
                                                            {{-- Nama sudah tertulis di bawah, jadi gambar dekoratif untuk pembaca layar --}}
                                                            <img src="{{ $candidate->posterUrl() }}" width="{{ $posterWidth }}" height="{{ $posterHeight }}" alt="">
                                                        @else
                                                            <span class="ballot__placeholder">Foto pasangan calon belum tersedia</span>
                                                        @endif
                                                        <svg class="ballot__punch" viewBox="0 0 100 100" aria-hidden="true" focusable="false">
                                                            <path d="M50 6l7 17 15-11-2 19 19-3-10 16 17 8-17 8 10 16-19-3 2 19-15-11-7 17-7-17-15 11 2-19-19 3 10-16-17-8 17-8-10-16 19 3-2-19 15 11z" />
                                                        </svg>
                                                    </span>
                                                    <span class="ballot__pair">Paslon {{ $candidate->candidate_pair }}</span>
                                                    <span class="ballot__names">{{ $candidate->leader_name }}<br>{{ $candidate->vice_leader_name }}</span>
                                                    <span class="ballot__picked">Pilihanmu</span>
                                                </span>
                                            </label>
                                            {{-- Di luar label: membuka surat tidak ikut mencoblos --}}
                                            <button type="button" class="ballot__info" data-letter-open="surat-{{ $candidate->id }}" commandfor="surat-{{ $candidate->id }}" command="show-modal">Visi &amp; misi</button>
                                        </li>
                                    @endforeach
                                </ol>
                            </fieldset>
                        </div>

                        <div class="ballot__send">
                            <p class="ballot__message" data-ballot-message role="alert">{{ $errors->first('candidate_id') }}</p>
                            <button type="submit" class="btn btn--primary ballot__submit" data-ballot-submit>Kirim suara</button>
                        </div>
                    </form>

                    {{-- Surat paslon di luar form: <form method="dialog"> di dalamnya tidak boleh bersarang di form surat suara --}}
                    @foreach ($candidates as $candidate)
                        @include('partials.candidate-letter', ['candidate' => $candidate])
                    @endforeach

                    <dialog id="konfirmasi" class="letter confirm" aria-labelledby="konfirmasi-judul">
                        <div class="letter__paper confirm__paper">
                            <span class="hex-number confirm__seal" data-confirm-number aria-hidden="true"></span>
                            <h2 id="konfirmasi-judul" class="letter__title" data-confirm-title tabindex="-1" autofocus>Kirim suara?</h2>
                            <p class="confirm__names" data-confirm-names></p>
                            <p class="confirm__note">Setelah dikirim, pilihanmu tidak bisa diubah.</p>
                            <div class="confirm__actions">
                                <button type="button" class="btn btn--primary" data-confirm-yes>Ya, kirim suara</button>
                                <button type="button" class="letter__close" data-confirm-no>Periksa lagi</button>
                            </div>
                        </div>
                    </dialog>

                    {{-- Kotak suara yang naik dari bawah layar saat surat dikirim (animasi lipat) --}}
                    <div class="ballot-box" aria-hidden="true">
                        <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="" loading="lazy">
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
