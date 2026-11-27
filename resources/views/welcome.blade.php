@php
    use App\Models\ElectionSetting;

    $start = $setting->vote_start?->copy()->locale('id');
    $end = $setting->vote_end?->copy()->locale('id');
    $sameDay = $start && $end && $start->isSameDay($end);
    $dateLabel = fn ($date) => $date->translatedFormat('l, j F Y');

    $user = auth()->user();
    $userLink = url('/back-admin-logged/redirect-role');
    $userLinkLabel = $user?->hasRole('Admin') ? 'Panel admin' : 'Buka surat suara';
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemilihan Ketua HMIF 2025/2026 | Election Vote</title>
    <meta name="description" content="Jadwal, pasangan calon, dan hasil pemilihan Ketua dan Wakil Ketua Umum Himpunan Mahasiswa Informatika Universitas Jember periode 2025/2026.">
    <link rel="icon" href="{{ asset('frontend/images/logo_election_vote.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,500..900&family=Plus+Jakarta+Sans:wght@400;600&display=swap">
    {{-- Ditandai sebelum halaman tergambar: navbar tetap boleh transparan di atas foto hanya bila JS jalan --}}
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}">
    <script src="{{ asset('frontend/js/home.js') }}" defer></script>
</head>

<body>
    <a class="skip-link" href="#paslon">Langsung ke daftar pasangan calon</a>

    <header class="site-header" data-site-header>
        <div class="container site-header__inner">
            <a href="{{ url('/') }}" class="site-header__logo">
                <img src="{{ asset('frontend/images/home/logo-election-vote.webp') }}" width="313" height="160"
                    alt="Election Vote HMIF">
            </a>
            <nav class="site-header__actions" aria-label="Akun">
                @auth
                    <a class="btn btn--on-photo" href="{{ $userLink }}">{{ $userLinkLabel }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn--on-photo btn--quiet">Keluar</button>
                    </form>
                @else
                    <a class="btn btn--on-photo" href="{{ route('login') }}">Masuk</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <div class="first-screen">
            <section class="hero" aria-labelledby="judul">
                <div class="hero__slides" data-slider>
                    @foreach ([1, 2, 3] as $n)
                        <img class="hero__slide{{ $n === 1 ? ' is-active' : '' }}"
                            src="{{ asset("frontend/images/home/foto-{$n}-1200.webp") }}"
                            srcset="{{ asset("frontend/images/home/foto-{$n}-1200.webp") }} 1200w, {{ asset("frontend/images/home/foto-{$n}-2400.webp") }} 2400w"
                            sizes="100vw" width="2400" height="1350"
                            @if ($n === 1) fetchpriority="high" alt="Pengurus HMIF berfoto bersama di depan gerbang Universitas Jember"
                            @else loading="lazy" alt="" @endif>
                    @endforeach
                </div>
                <div class="hero__scrim" aria-hidden="true"></div>

                <div class="container hero__inner">
                    <h1 id="judul" class="hero__title">
                        Pemilihan Ketua dan Wakil Ketua Umum HMIF
                        <span class="hero__period">Periode 2025/2026</span>
                    </h1>
                </div>
            </section>

            @php
                // Hitung mundur ke pembukaan (akan dibuka) atau penutupan (sedang berlangsung).
                $countdownTarget = match ($status) {
                    ElectionSetting::STATUS_UPCOMING => $start,
                    ElectionSetting::STATUS_OPEN => $end,
                    default => null,
                };
            @endphp
            <div class="slip-band">
                <div class="container slip-band__inner">
                    <section class="slip slip--{{ $status }}" aria-labelledby="jadwal">
                        <div class="slip__stub">
                            <h2 id="jadwal" class="slip__label">Jadwal pemilihan</h2>
                            <p class="slip__status">
                                @switch($status)
                                    @case(ElectionSetting::STATUS_UPCOMING) Segera dibuka @break
                                    @case(ElectionSetting::STATUS_OPEN)<span class="live-dot" aria-hidden="true"></span>Sedang berlangsung @break
                                    @case(ElectionSetting::STATUS_CLOSED) Pemilihan ditutup @break
                                    @default Belum diumumkan
                                @endswitch
                            </p>
                        </div>

                        @if ($countdownTarget)
                            @include('partials.countdown', ['target' => $countdownTarget, 'caption' => $status === ElectionSetting::STATUS_OPEN ? 'Ditutup dalam' : 'Dibuka dalam'])
                        @endif

                        <div class="slip__body">
                            @switch($status)
                                @case(ElectionSetting::STATUS_UPCOMING)
                                    @if ($sameDay)
                                        <p class="slip__when"><strong>{{ $dateLabel($start) }}</strong><br>{{ $start->format('H.i') }}–{{ $end->format('H.i') }} WIB</p>
                                    @else
                                        <p class="slip__when">Dibuka <strong>{{ $dateLabel($start) }}</strong> pukul {{ $start->format('H.i') }} WIB<br>Ditutup <strong>{{ $dateLabel($end) }}</strong> pukul {{ $end->format('H.i') }} WIB</p>
                                    @endif
                                    @break

                                @case(ElectionSetting::STATUS_OPEN)
                                    <p class="slip__when">Ditutup <strong>{{ $dateLabel($end) }}</strong><br>pukul {{ $end->format('H.i') }} WIB</p>
                                    @break

                                @case(ElectionSetting::STATUS_CLOSED)
                                    <p class="slip__when">Ditutup <strong>{{ $dateLabel($end) }}</strong><br>pukul {{ $end->format('H.i') }} WIB</p>
                                    @break

                                @default
                                    <p class="slip__when"><strong>Jadwal pemilihan belum diumumkan panitia.</strong><br>Akun pemilihan dikirim ke email kampus masing-masing saat pemilihan dibuka.</p>
                            @endswitch
                        </div>

                        @if ($status === ElectionSetting::STATUS_OPEN)
                            @auth
                                <a class="btn btn--primary slip__action" href="{{ $userLink }}">{{ $userLinkLabel }}</a>
                            @else
                                <a class="btn btn--primary slip__action" href="{{ route('login') }}">Masuk untuk memilih</a>
                            @endauth
                        @elseif ($status === ElectionSetting::STATUS_CLOSED)
                            <a class="btn btn--primary slip__action" href="#hasil">Lihat hasil</a>
                        @endif
                    </section>

                </div>
            </div>
        </div>

        <section id="paslon" class="ballot-section" aria-labelledby="surat-suara">
            <div class="container ballot-section__inner">
                <div class="ballot-section__intro">
                    <h2 id="surat-suara" class="section-title">Surat suara</h2>
                    <p>Buka amplop untuk mengenal tiap pasangan calon. Suara hanya bisa dikirim satu kali dan tidak dapat diubah.</p>
                </div>

                @if ($candidates->isEmpty())
                    <p class="ballot__empty">Daftar pasangan calon belum diumumkan.</p>
                @else
                    @include('partials.envelopes', ['candidates' => $candidates])
                @endif
            </div>
        </section>

        <section id="hasil" class="results" aria-labelledby="hasil-judul">
            <div class="container results__inner">
                <div class="results__intro">
                    <h2 id="hasil-judul" class="section-title">Hasil penghitungan</h2>
                    @if ($results !== null && $totalVotes > 0)
                        <p class="results__total">Total {{ $totalVotes }} suara masuk.</p>
                    @endif
                </div>

                @if ($results !== null && $totalVotes > 0)
                    <ol class="tally">
                        @foreach ($results as $row)
                            <li class="tally__row">
                                <span class="hex-number tally__seal" aria-hidden="true">{{ $row['candidate']->candidate_pair }}</span>
                                <p class="tally__name">
                                    <span class="tally__pair">Paslon {{ $row['candidate']->candidate_pair }}</span>
                                    {{ $row['candidate']->leader_name }} &amp; {{ $row['candidate']->vice_leader_name }}
                                </p>
                                <p class="tally__count"><strong>{{ $row['percent'] }}%</strong> {{ $row['votes'] }} suara</p>
                                <div class="tally__bar" aria-hidden="true"><span style="width: {{ $row['percent'] }}%"></span></div>
                            </li>
                        @endforeach
                    </ol>
                @elseif ($results === null && $end)
                    <p class="results__note">Hasil penghitungan tampil di sini setelah pemilihan ditutup pada {{ $dateLabel($end) }} pukul {{ $end->format('H.i') }} WIB.</p>
                @elseif ($results === null)
                    <p class="results__note">Hasil penghitungan tampil di sini setelah pemilihan ditutup.</p>
                @else
                    <p class="results__note">Belum ada suara tercatat.</p>
                @endif
            </div>
        </section>
    </main>

    @include('partials.site-footer')

    @php($cogito = \App\Support\CogitoLines::home($setting, $user))
    @include('partials.cogito', ['pose' => 'salam', 'variant' => 'corner', 'key' => 'home', 'lines' => $cogito['lines'], 'action' => $cogito['action']])
</body>

</html>
