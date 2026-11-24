<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="/frontend/css/animation-custom.css">
    <link rel="stylesheet" href="/frontend/css/vote.css">
    <link rel="icon" href="/frontend/images/logo_election_vote.png" type="image/x-icon">
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.min.css'>

    <title>Vote Page</title>
</head>

<body>
    <nav class="gtco-nav" role="navigation" style="background-color:#43908F;position:sticky;top:0;z-index:10">
        <div class="container">
            <div class="d-flex justify-content-between">
                <div id="gtco-logo">
                    <a href="/">
                        <img src="/frontend/images/logo_election_vote.png" class="logo-nav"
                            alt="logo election vote HMIF">
                    </a>
                </div>
                <div class="my-auto">
                    @if (Route::has('login'))
                        @auth
                            <div class="d-flex">
                                {{-- <a class="button-custom" href="{{ url('/vote') }}">Vote</a> --}}
                                <form class="ml-3" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="button-custom" style="padding:0.5rem 1rem" type="submit" href="#">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                                            fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd"
                                                d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                                            <path fill-rule="evenodd"
                                                d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @else
                            <a class="button-custom" style="padding:0.5rem 1rem" href="{{ route('login') }}">Login</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>
    <center class="py-5">
        <h1>Pilih Calon Ketua dan Wakil Ketua HMIF 2024/2025</h1>
    </center>

    <center>
        <div class="container alert alert-success mb-5" role="alert">
            <h2>Pemilihan Belum Dibuka</h2>
        </div>
    </center>

    <div class="container-full mx-5">
        <div class="row" style="display: flex; justify-content: center">
            @foreach ($candidates as $item)
                <div class="col-md-4 p-4 mb-5 text-center">
                    <div class="img-shadow">
                        <img src="{{ asset('assets/upload/kandidat/' . $item->poster) }}"
                            class="img-responsive radius-image floating" alt="{{ $item->poster }}">
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous">
    </script>

</body>

</html>
