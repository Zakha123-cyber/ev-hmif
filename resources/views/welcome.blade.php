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
    <link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}">
    <script src="{{ asset('frontend/js/home.js') }}" defer></script>
</head>

<body>
    <a class="skip-link" href="#paslon">Langsung ke daftar pasangan calon</a>

    <header class="site-header">
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
                <button type="button" class="slider-toggle" data-slider-toggle hidden>Jeda foto</button>
            </div>
        </section>

        <div class="container slip-row">
            <section class="slip" aria-labelledby="jadwal">
                <h2 id="jadwal" class="slip__label">Jadwal pemilihan</h2>

                @switch($status)
                    @case(ElectionSetting::STATUS_UPCOMING)
                        <p class="slip__status">Dibuka {{ $start->diffForHumans(now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 1]) }} lagi</p>
                        @if ($sameDay)
                            <p class="slip__when">{{ $dateLabel($start) }}<br>{{ $start->format('H.i') }}–{{ $end->format('H.i') }} WIB</p>
                        @else
                            <p class="slip__when">Dibuka {{ $dateLabel($start) }} pukul {{ $start->format('H.i') }} WIB<br>Ditutup {{ $dateLabel($end) }} pukul {{ $end->format('H.i') }} WIB</p>
                        @endif
                        @break

                    @case(ElectionSetting::STATUS_OPEN)
                        <p class="slip__status">Pemilihan sedang berlangsung</p>
                        <p class="slip__when">Ditutup {{ $dateLabel($end) }} pukul {{ $end->format('H.i') }} WIB.</p>
                        @auth
                            <a class="btn btn--primary" href="{{ $userLink }}">{{ $userLinkLabel }}</a>
                        @else
                            <a class="btn btn--primary" href="{{ route('login') }}">Masuk untuk memilih</a>
                        @endauth
                        @break

                    @case(ElectionSetting::STATUS_CLOSED)
                        <p class="slip__status">Pemilihan ditutup</p>
                        <p class="slip__when">{{ $dateLabel($end) }} pukul {{ $end->format('H.i') }} WIB.</p>
                        <a class="btn btn--primary" href="#hasil">Lihat hasil</a>
                        @break

                    @default
                        <p class="slip__status slip__status--quiet">Jadwal pemilihan belum diumumkan panitia.</p>
                        <p class="slip__when">Akun pemilihan dikirim ke email kampus masing-masing saat pemilihan dibuka.</p>
                @endswitch
            </section>

            @if ($mascotUrl)
                <model-viewer class="mascot" src="{{ $mascotUrl }}" alt="COGITO, maskot HMIF. Geser untuk memutar."
                    camera-controls disable-zoom disable-pan interaction-prompt="none" touch-action="pan-y"
                    shadow-intensity="0.6"></model-viewer>
            @endif
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
                    <ol class="envelopes envelopes--{{ min($candidates->count(), 3) }}">
                        @foreach ($candidates as $candidate)
                            @php
                                $letterId = 'surat-' . $candidate->id;
                                $posterPath = public_path('assets/upload/kandidat/' . $candidate->poster);
                                $hasPoster = $candidate->poster && file_exists($posterPath);
                                // Ukuran asli poster agar surat tidak bergeser saat gambar dimuat.
                                [$posterWidth, $posterHeight] = ($hasPoster ? @getimagesize($posterPath) : false) ?: [600, 800];
                                $missions = $candidate->missionItems();
                            @endphp
                            <li>
                                <button type="button" class="envelope" data-envelope commandfor="{{ $letterId }}" command="show-modal">
                                    <span class="envelope__flap" aria-hidden="true"></span>
                                    <img class="envelope__logo" src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="" loading="lazy">
                                    <span class="hex-number envelope__seal" aria-hidden="true">{{ $candidate->candidate_pair }}</span>
                                    <span class="envelope__label">Paslon {{ $candidate->candidate_pair }}</span>
                                    <span class="envelope__hint">Buka amplop</span>
                                </button>

                                <dialog id="{{ $letterId }}" class="letter" aria-labelledby="{{ $letterId }}-judul">
                                    <div class="letter__paper">
                                        <header class="letter__kop">
                                            <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="" loading="lazy">
                                            <p><strong>Himpunan Mahasiswa Informatika</strong><span>Universitas Jember</span></p>
                                        </header>

                                        <h3 id="{{ $letterId }}-judul" class="letter__title" tabindex="-1" autofocus>Pasangan Calon Nomor Urut {{ $candidate->candidate_pair }}</h3>

                                        @if ($hasPoster)
                                            <img class="letter__poster" src="{{ asset('assets/upload/kandidat/' . $candidate->poster) }}"
                                                width="{{ $posterWidth }}" height="{{ $posterHeight }}" loading="lazy"
                                                alt="Poster pasangan calon nomor {{ $candidate->candidate_pair }}, {{ $candidate->leader_name }} dan {{ $candidate->vice_leader_name }}">
                                        @else
                                            <div class="letter__poster letter__placeholder">Foto pasangan calon belum tersedia</div>
                                        @endif

                                        <dl class="letter__names">
                                            <div>
                                                <dt>Ketua</dt>
                                                <dd>{{ $candidate->leader_name }}</dd>
                                            </div>
                                            <div>
                                                <dt>Wakil ketua</dt>
                                                <dd>{{ $candidate->vice_leader_name }}</dd>
                                            </div>
                                        </dl>

                                        <h4 class="letter__heading">Visi</h4>
                                        <p class="letter__vision">{{ $candidate->vision }}</p>

                                        @if ($missions)
                                            <h4 class="letter__heading">Misi</h4>
                                            <ol class="letter__mission">
                                                @foreach ($missions as $mission)
                                                    <li>{{ $mission }}</li>
                                                @endforeach
                                            </ol>
                                        @endif

                                        <form method="dialog">
                                            <button class="letter__close">Tutup</button>
                                        </form>
                                    </div>
                                </dialog>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>

        <section id="hasil" class="results{{ $results !== null && $totalVotes > 0 ? '' : ' results--pending' }}" aria-labelledby="hasil-judul">
            <div class="container results__inner">
                <div class="results__intro">
                    <h2 id="hasil-judul" class="section-title">Hasil penghitungan</h2>
                    @if ($results === null && $end)
                        <p>Hasil penghitungan tampil di sini setelah pemilihan ditutup pada {{ $dateLabel($end) }} pukul {{ $end->format('H.i') }} WIB.</p>
                    @elseif ($results === null)
                        <p>Hasil penghitungan tampil di sini setelah pemilihan ditutup.</p>
                    @elseif ($totalVotes === 0)
                        <p>Belum ada suara tercatat.</p>
                    @else
                        <p>Total {{ $totalVotes }} suara masuk.</p>
                    @endif
                </div>

                @if ($results !== null && $totalVotes > 0)
                    <ol class="tally">
                        @foreach ($results as $row)
                            <li class="tally__row">
                                <p class="tally__name">
                                    <span class="tally__pair">Paslon {{ $row['candidate']->candidate_pair }}</span>
                                    {{ $row['candidate']->leader_name }} &amp; {{ $row['candidate']->vice_leader_name }}
                                </p>
                                <p class="tally__count"><strong>{{ $row['percent'] }}%</strong> {{ $row['votes'] }} suara</p>
                                <div class="tally__bar" aria-hidden="true"><span style="width: {{ $row['percent'] }}%"></span></div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <div class="site-footer__org">
                <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" loading="lazy"
                    alt="Logo Himpunan Mahasiswa Informatika">
                <p>Himpunan Mahasiswa Informatika<br>Universitas Jember</p>
            </div>
            <div class="site-footer__help">
                <h2 class="site-footer__title">Kendala login atau belum menerima email akun?</h2>
                <p>Hubungi panitia lewat WhatsApp:</p>
                <ul>
                    <li><a href="https://wa.me/6283823093973">Farda, 0838 2309 3973</a></li>
                    <li><a href="https://wa.me/6283852061105">Dewi, 0838 5206 1105</a></li>
                </ul>
            </div>
        </div>
        <p class="container site-footer__copy">&copy; {{ now()->year }} Election Vote HMIF</p>
    </footer>

    @if ($mascotUrl)
        <script type="module" src="https://cdn.jsdelivr.net/npm/@google/model-viewer@3.5.0/dist/model-viewer.min.js"></script>
    @endif
</body>

</html>
