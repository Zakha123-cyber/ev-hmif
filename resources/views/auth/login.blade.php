@php
    use App\Models\ElectionSetting;

    $when = fn ($date) => $date->copy()->locale('id')->translatedFormat('l, j F') . ' pukul ' . $date->format('H.i') . ' WIB';
    $title = match ($status) {
        ElectionSetting::STATUS_OPEN => 'Masuk untuk memilih',
        ElectionSetting::STATUS_CLOSED => 'Pemilihan sudah selesai',
        default => 'Masuk ke akunmu',
    };
    $error = implode(' ', $errors->all());
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | Election Vote HMIF</title>
    <link rel="icon" href="{{ asset('frontend/images/logo_election_vote.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,500..900&family=Plus+Jakarta+Sans:wght@400;600&display=swap">
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}?v={{ filemtime(public_path('frontend/css/home.css')) }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/login.css') }}?v={{ filemtime(public_path('frontend/css/login.css')) }}">
    <script src="{{ asset('frontend/js/login.js') }}?v={{ filemtime(public_path('frontend/js/login.js')) }}" defer></script>
</head>

<body class="login-body">
    <a class="skip-link" href="#username">Langsung ke kolom username</a>

    <main class="login">
        {{-- Foto pengurus HMIF: pintu masuk ke "bilik suara" --}}
        <section class="login__scene" aria-labelledby="judul">
            <img class="login__photo" src="{{ asset('frontend/images/home/foto-1-1200.webp') }}"
                srcset="{{ asset('frontend/images/home/foto-1-1200.webp') }} 1200w, {{ asset('frontend/images/home/foto-1-2400.webp') }} 2400w"
                sizes="(min-width: 960px) 55vw, 100vw" width="2400" height="1350" fetchpriority="high" alt="">
            <div class="login__scrim" aria-hidden="true"></div>

            <div class="login__intro">
                <a href="{{ url('/') }}" class="login__logo">
                    <img src="{{ asset('frontend/images/home/logo-election-vote.webp') }}" width="313" height="160" alt="Election Vote HMIF, ke halaman awal">
                </a>
                <div>
                    <h1 id="judul" class="login__title">{{ $title }}</h1>
                    <p class="login__status">
                        @switch($status)
                            @case(ElectionSetting::STATUS_OPEN)
                                <span class="live-dot" aria-hidden="true"></span>Sedang berlangsung sampai {{ $when($setting->vote_end) }}
                                @break
                            @case(ElectionSetting::STATUS_UPCOMING)
                                Dibuka {{ $when($setting->vote_start) }}
                                @break
                            @case(ElectionSetting::STATUS_CLOSED)
                                Ditutup {{ $when($setting->vote_end) }}
                                @break
                            @default
                                Jadwal pemilihan belum diumumkan
                        @endswitch
                    </p>
                    <p class="login__period">Pemilihan Ketua dan Wakil Ketua HMIF periode {{ $setting->periodLabel() }}</p>
                </div>
            </div>
        </section>

        <section class="login__panel" aria-label="Formulir masuk">
            <div class="login__panel-inner">
                <a class="login__back" href="{{ url('/') }}">Kembali ke halaman awal</a>

                {{-- Kartu pemilih: kop, isian, lalu sobekan berisi bantuan --}}
                <form class="pass" method="POST" action="{{ route('login') }}" novalidate data-login-form>
                    @csrf
                    <header class="pass__head">
                        <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="">
                        <p>
                            <strong>Kartu pemilih</strong>
                            <span>Masuk dengan akun dari email kampusmu</span>
                        </p>
                    </header>

                    <div class="pass__body">
                        @if ($status === ElectionSetting::STATUS_CLOSED)
                            <p class="pass__note">
                                Kamu tetap bisa masuk untuk melihat tanda terima suaramu.
                                <a href="{{ url('/') }}#hasil">Lihat hasil penghitungan</a>
                            </p>
                        @endif

                        @if ($error)
                            <p class="pass__error" id="login-error" role="alert">{{ $error }}</p>
                        @endif

                        <div class="pass__field">
                            <label for="username">Username</label>
                            <input id="username" name="username" type="text" value="{{ old('username') }}"
                                autocomplete="username" autocapitalize="none" spellcheck="false" required
                                @if ($error) aria-invalid="true" aria-describedby="login-error" autofocus @endif>
                        </div>

                        <div class="pass__field">
                            <label for="password">Password</label>
                            <div class="pass__password">
                                <input id="password" name="password" type="password" autocomplete="current-password" required
                                    @if ($error) aria-invalid="true" aria-describedby="login-error" @endif>
                                {{-- Satu-satunya tombol lihat password (ikon mata bawaan Edge disembunyikan di login.css) --}}
                                <button type="button" class="pass__toggle" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Tampilkan password" hidden>
                                    <svg data-eye viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg data-eye-off hidden viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.6 10.6 0 0 1 12 5c6.5 0 10 7 10 7a17.7 17.7 0 0 1-3.2 4.1M6.6 6.6C3.9 8.3 2 12 2 12s3.5 7 10 7c1.9 0 3.6-.6 5-1.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn--primary pass__submit" data-login-submit>Masuk</button>
                    </div>

                    @php $contacts = \App\Models\ContactPerson::ordered()->get(); @endphp
                    <footer class="pass__foot">
                        @if ($contacts->isNotEmpty())
                            <p>Akun dikirim ke email kampusmu (<strong>@mail.unej.ac.id</strong>). Belum menerima akun atau lupa password? Chat panitia:</p>
                            <ul>
                                @foreach ($contacts as $contact)
                                    <li><a href="{{ $contact->waLink() }}">{{ $contact->name }}, {{ $contact->displayPhone() }}</a></li>
                                @endforeach
                            </ul>
                        @else
                            <p>Akun dikirim ke email kampusmu (<strong>@mail.unej.ac.id</strong>).</p>
                        @endif
                    </footer>
                </form>
            </div>
        </section>
    </main>
</body>

</html>
