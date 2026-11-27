@php
    // Tanda terima suara. Sengaja tidak menyebut paslon yang dipilih: suara rahasia.
    $votedAtLocal = $votedAt?->copy()->locale('id');
    $end = $setting->vote_end?->copy()->locale('id');
    $closed = $setting->status() === \App\Models\ElectionSetting::STATUS_CLOSED;
@endphp
<section class="vote-hero vote-hero--center receipt" aria-labelledby="judul" @if ($justVoted) data-just-voted @endif>
    <div class="container vote-hero__inner">
        <div class="receipt__scene">
            <div class="receipt__art" aria-hidden="true">
                <span class="receipt__paper"><span class="hex-number receipt__seal"></span></span>
                <span class="receipt__box">
                    <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="">
                </span>
            </div>
            @php $cogito = \App\Support\CogitoLines::vote('receipt', $setting, auth()->user()); @endphp
            @include('partials.cogito', ['pose' => 'salute', 'variant' => 'inline', 'key' => 'vote-receipt', 'lines' => $cogito['lines'], 'action' => $cogito['action']])
        </div>

        <h1 id="judul" class="vote-hero__title">Suaramu sudah tercatat</h1>

        @if ($votedAtLocal)
            <p class="receipt__time">{{ $votedAtLocal->translatedFormat('l, j F Y') }} pukul {{ $votedAtLocal->format('H.i') }} WIB</p>
        @endif

        @if ($closed)
            <p class="vote-hero__lead">Pemilihan sudah ditutup. Hasil penghitungan bisa dilihat di halaman awal.</p>
            <p class="vote-hero__actions">
                <a class="btn btn--primary" href="{{ url('/') }}#hasil">Lihat hasil</a>
            </p>
        @else
            <p class="vote-hero__lead">
                {{-- "Terima kasih sudah memilih!" diucapkan COGITO di atas --}}
                @if ($end)
                    Hasil diumumkan setelah pemilihan ditutup {{ $end->translatedFormat('l, j F Y') }} pukul {{ $end->format('H.i') }} WIB.
                @endif
            </p>
            <p class="vote-hero__actions">
                <a class="btn btn--ink" href="{{ url('/') }}">Kembali ke halaman awal</a>
            </p>
        @endif
    </div>
</section>
