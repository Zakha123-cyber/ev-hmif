<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Election vote</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Website pemilihan ketua dan wakil ketua umum HMIF" />
    <meta name="keywords" content="free website templates, HMIF, himpunan, Informatika, fakultas, ilmu, komputer" />
    <meta name="author" content="teknologi HMIF UNEJ" />

    <!-- Facebook and Twitter integration -->
    <meta property="og:title" content="" />
    <meta property="og:image" content="" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <meta property="og:description" content="" />
    <meta name="twitter:title" content="" />
    <meta name="twitter:image" content="" />
    <meta name="twitter:url" content="" />
    <meta name="twitter:card" content="" />

    <link rel="icon" href="/frontend/images/logo_election_vote.png" type="image/x-icon">

    <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400, 900" rel="stylesheet"> -->

    <link href='https://fonts.googleapis.com/css?family=Space Grotesk' rel='stylesheet'>
    <link rel="stylesheet" href="/frontend/css/animate.css">
    <link rel="stylesheet" href="/frontend/css/bootstrap.css">
    <link rel="stylesheet" href="/frontend/css/owl.theme.default.min.css">

    <!-- Theme style  -->
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/animation-custom.css">

    <!-- Modernizr JS -->
    <script src="/frontend/js/modernizr-2.6.2.min.js"></script>
    <!-- Chart -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>

</head>

<body>

    <div class="gtco-loader"></div>

    <div id="page">
        <nav class="gtco-nav" role="navigation">
            <div class="container">
                <div style="display: flex; justify-content: space-between;	">
                    <div>
                        <div id="gtco-logo">
                            <a href="#">
                                <img src="/frontend/images/logo_election_vote.png" class="logo-nav"
                                    alt="logo election vote HMIF">
                            </a>
                        </div>
                    </div>
                    <div style="display: flex; margin: auto 0;">
                        @if (Route::has('login'))
                            @auth
                                <a class="button-custom" style="margin-right: 1rem" href="{{ url('/vote') }}">Vote</a>
                                <a class="" href="#">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="button-custom" type="submit" href="#">
                                            log out
                                        </button>
                                    </form>
                                </a>
                            @else
                                @if ($results_visible)
                                    <a class="button-custom" style="padding:0.5rem 2.5rem" href="#">Pemilihan
                                        Selesai</a>
                                @else
                                    <a class="button-custom" style="padding:0.5rem 2.5rem"
                                        href="{{ route('login') }}">Login</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <section id="gtco-hero" class="gtco-cover"
            style="background-image: url('{{ asset('frontend/images/Desktop.png') }}'); background-position: center; background-attachment: fixed;
		">
            <div class="overlay"></div>
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-md-offset-0 text-center">
                        <div class="display-t">
                            <div class="display-tc">
                                <h1 class="animate-box" data-animate-effect="fadeIn"
                                    style="font-family: 'Space Grotesk'; font-weight:bold;">Ayo Gunakan Hak Pilihmu,
                                    Jadilah Pemilih Yang Cerdas !</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="gtco-our-team" data-section="our-team">
            <div class="container">
                @foreach ($candidates as $item)
                    <div class="" style="margin-top: 100px">
                        <div class="row row-pb-lg" style="margin-bottom: 0px">
                            <div class="col-md-8 col-md-offset-2 heading animate-box" data-animate-effect="fadeIn">
                                <p class="subtle-text animate-box" data-animate-effect="fadeIn">Paslon
                                    {{ $item->candidate_pair }}</p>
                            </div>
                        </div>
                        <div class="row team-item gtco-team-reverse">
                            <div class="col-md-6 col-md-push-7 animate-box" data-animate-effect="fadeInRight">
                                <div class="img-shadow">
                                    <img src="{{ asset('assets/upload/kandidat/' . $item->poster) }}"
                                        class="img-responsive radius-image floating" alt="Foto paslon">
                                </div>
                                <div class="circle-custom-1"></div>
                                <div class="circle-custom-1-2"></div>
                            </div>
                            <div class="col-md-6 col-md-pull-6 animate-box" data-animate-effect="fadeInRight">
                                <span>
                                    <h2>{{ $item->leader_name }} & {{ $item->vice_leader_name }}</h2>
                                    <div class="circle-text"></div>
                                </span>
                                <h3>Visi</h3>
                                <p>{{ $item->vision }}
                                </p>
                                <h3>Misi</h3>
                                <ul>
                                    <?php
                                    if (substr($item->mission, -1) === '.') {
                                        $mission = rtrim($item->mission, '.');
                                    } else {
                                        $mission = $item->mission;
                                    }
                                    $item_mission = explode('.', $mission);
                                    ?>
                                    @foreach ($item_mission as $mission_item)
                                        <li>{{ $mission_item }}.</li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </section>

        <section>
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2 animate-box text-center" data-animate-effect="fadeIn">
                        <h2>Hasil Perhitungan</h2>
                        <p class="subtle-text animate-box" data-animate-effect="fadeIn">Hasil perhitungan dari suara
                            yang masuk</p>
                    </div>
                </div>
                <div class="row" style="text-align: center;align-items:center">
                    @if ($results_visible)
                        <div class="col-md-8 col-md-offset-2 animate-box" data-animate-effect="fadeIn">
                            <canvas id="pie-chart" width="600" height="400"></canvas>
                        </div>
                    @else
                        <div class="container alert alert-info animate-box" data-animate-effect="fadeIn"
                            role="alert">
                            <p data-animate-effect="fadeIn">Hasil perhitungan suara akan ditampilkan ketika pemilihan
                                selesai &#128079;</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <footer id="gtco-footer" role="contentinfo">
            <div class="container">
                <div class="row copyright">
                    <div class="col-md-12">
                        <p class="pull-left">
                            <small class="block">&copy; {{ now()->year }} HMIF Copyright © | Made With
                                Love.</small>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <div class="gototop js-top">
        <a href="#" class="js-gotop" style="display: flex; justify-content: center; align-items: center"> ↑
            </i></a>
    </div>

    @if ($results_visible)
    <script>
        var label_candidates = @json($label_candidates);
        var value_candidates = @json($value_candidates);

        new Chart(document.getElementById("pie-chart"), {
            type: 'pie',
            data: {
                labels: label_candidates,
                datasets: [{
                    label: "Population (millions)",
                    backgroundColor: ["#f48c06", "#31BDAA", "#FF5733", "#6C3483", "#3498DB "],
                    data: value_candidates
                }]
            },
        });
    </script>
    @endif


    <!-- jQuery -->
    <script src="/frontend/js/jquery.min.js"></script>
    <!-- jQuery Easing -->
    <script src="/frontend/js/jquery.easing.1.3.js"></script>
    <!-- Bootstrap -->
    <script src="/frontend/js/bootstrap.min.js"></script>
    <!-- Waypoints -->
    <script src="/frontend/js/jquery.waypoints.min.js"></script>
    <!-- Stellar -->
    <script src="/frontend/js/jquery.stellar.min.js"></script>
    <!-- Magnific Popup -->
    <script src="/frontend/js/jquery.magnific-popup.min.js"></script>
    <script src="/frontend/js/magnific-popup-options.js"></script>
    <!-- Main -->
    <script src="/frontend/js/main.js"></script>

</body>

</html>
