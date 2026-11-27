<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Admin Election Vote HMIF</title>
    <link rel="icon" href="{{ asset('frontend/images/logo_election_vote.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,500..900&family=Plus+Jakarta+Sans:wght@400;600&display=swap">
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}?v={{ filemtime(public_path('frontend/css/home.css')) }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/admin.css') }}?v={{ filemtime(public_path('frontend/css/admin.css')) }}">
    <script src="{{ asset('frontend/js/admin.js') }}?v={{ filemtime(public_path('frontend/js/admin.js')) }}" defer></script>
</head>

<body class="a-body">
    <a class="skip-link" href="#isi">Langsung ke isi halaman</a>

    <div class="a-shell">
        @include('admin.components.sidebar')
        <div class="a-overlay" data-drawer-overlay hidden></div>

        <div class="a-main">
            @include('admin.components.topbar')

            <main id="isi" class="a-content" tabindex="-1">
                <div class="a-pagehead">
                    <h1 class="a-pagehead__title">@yield('title')</h1>
                    @hasSection('actions')
                        <div class="a-pagehead__actions">@yield('actions')</div>
                    @endif
                </div>

                @if (session('status'))
                    <div class="a-flash a-flash--ok" role="status">
                        <p>{{ session('status') }}</p>
                        <button type="button" class="a-flash__close" data-flash-close>Tutup</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="a-flash a-flash--err" role="alert">
                        <p>{{ session('error') }}</p>
                        <button type="button" class="a-flash__close" data-flash-close>Tutup</button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Konfirmasi bersama untuk form[data-confirm] (hapus, kirim email massal) --}}
    <dialog class="a-dialog" id="a-confirm" aria-labelledby="a-confirm-title">
        <h2 id="a-confirm-title" class="a-dialog__title">Yakin?</h2>
        <p class="a-dialog__text" data-confirm-text></p>
        <div class="a-dialog__actions">
            <button type="button" class="a-btn a-btn--ghost" data-confirm-no autofocus>Batal</button>
            <button type="button" class="a-btn a-btn--danger" data-confirm-yes>Ya, lanjutkan</button>
        </div>
    </dialog>

    @stack('scripts')
</body>

</html>
