<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Election Vote HMIF</title>
    <link rel="icon" href="{{ asset('frontend/images/logo_election_vote.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,500..900&family=Plus+Jakarta+Sans:wght@400;600&display=swap">
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/vote.css') }}">
    <script src="{{ asset('frontend/js/home.js') }}" defer></script>
    <script src="{{ asset('frontend/js/vote.js') }}" defer></script>
</head>

<body class="vote-body">
    <a class="skip-link" href="#isi">Langsung ke isi halaman</a>

    {{-- Tidak ada foto di belakangnya, jadi navbar selalu solid --}}
    <header class="site-header site-header--solid" data-site-header>
        <div class="container site-header__inner">
            <a href="{{ url('/') }}" class="site-header__logo">
                <img src="{{ asset('frontend/images/home/logo-election-vote.webp') }}" width="313" height="160"
                    alt="Election Vote HMIF, ke halaman awal">
            </a>
            <nav class="site-header__actions" aria-label="Akun">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn--on-photo">Keluar</button>
                </form>
            </nav>
        </div>
    </header>

    <main id="isi" class="vote-main" tabindex="-1">
        @yield('content')
    </main>

    @include('partials.site-footer')
</body>

</html>
