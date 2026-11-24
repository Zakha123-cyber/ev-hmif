@extends('admin.layouts.app')
@section('content')
<div>
    <div class="grid grid-cols-4 gap-6 xl:grid-cols-1">
        <div class="report-card">
            <div class="card">
                <div class="card-body flex flex-col">
                    <div class="flex flex-row justify-between items-center">
                        <div class="h6 text-indigo-700 fad fa-users"></div>
                    </div>
                    <div class="mt-8">
                        <h1 class="h5">{{$countAdmin}} Administrator</h1>
                        <p>Total Administrator Election</p>
                    </div>            
                </div>
            </div>
            <div class="footer bg-teal-500 p-1 mx-4 border border-t-0 rounded rounded-t-none"></div>
        </div>
        <div class="report-card">
            <div class="card">
                <div class="card-body flex flex-col">
                    <div class="flex flex-row justify-between items-center">
                        <div class="h6 text-red-700 fad fa-sitemap"></div>
                    </div>
                    <div class="mt-8">
                        <h1 class="h5">{{$countParticipant - 2}} Participant</h1>
                        <p>Total Pemilih Election</p>
                    </div>
                </div>
            </div>
            <div class="footer bg-teal-500 p-1 mx-4 border border-t-0 rounded rounded-t-none"></div>
        </div>
        <div class="report-card">
            <div class="card">
                <div class="card-body flex flex-col">
                    <div class="flex flex-row justify-between items-center">
                        <div class="h6 text-yellow-600 fad fa-clipboard"></div>
                    </div>
                    <div class="mt-8">
                        <h1 class="h5">{{$countCandidate}} Candidate</h1>
                        <p>Total Kandidat Election</p>
                    </div>
                </div>
            </div>
            <div class="footer bg-teal-500 p-1 mx-4 border border-t-0 rounded rounded-t-none"></div>
        </div>
        <div class="report-card">
            <div class="card">
                <div class="card-body flex flex-col">
                    <div class="flex flex-row justify-between items-center">
                        <div class="h6 text-green-700 fad fa-newspaper"></div>
                    </div>
                    <div class="mt-8">
                        <h1 class="h5">{{$countVoted - 2}}/{{$countParticipant - 2}} Voted</h1>
                        <p>Total Participant Voted.</p>
                    </div>
                </div>
            </div>
            <div class="footer bg-teal-500 p-1 mx-4 border border-t-0 rounded rounded-t-none"></div>
        </div>
    </div>

    <div class="card bg-teal-400 border-teal-400 shadow-md text-white my-6">
        <div class="card-body flex flex-row">
            <div class="img-wrapper w-40 h-40 flex justify-center items-center">
                <img src="{{asset('assets/img-admin/good.png')}}" alt="img title">
            </div>
            <div class="py-2 ml-10">
                <h1 class="h6">Kerja Bagus, {{Auth::user()->name;}} !</h1>
                <p class="text-white text-xs">Pengurus periode kalian telah menyelesaikan beberapa proker belakangan ini.</p>

                <ul class="mt-4">
                    <li class="text-sm font-light"><i class="fad fa-check-double mr-2 mb-2"></i> Semangat</li>
                    <li class="text-sm font-light"><i class="fad fa-check-double mr-2 mb-2"></i> Kompak</li>
                    <li class="text-sm font-light"><i class="fad fa-check-double mr-2"></i> Kuat dan Tangguh</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-3 gap-6 xl:grid-cols-1">
        <div class="shadow-lg rounded-lg overflow-hidden border">
            <div class="pt-5 px-5 bg-gray-50">
                <h1 class="h5">Data Perhitungan Sementara</h1>
            </div>
            <div class="flex justify-center items-center h-full">
                <canvas class="p-5 mb-10" id="chartBar"></canvas>
            </div>
        </div>

        <div class="flex flex-col">
            <div class="alert alert-dark">
                <span class="normal-case">
                    Total traffic kunjungan website <a target="_blank" href="#">election-vote</a>
                </span>
            </div>

            <div class="card my-6">
                <div class="py-3 px-4 flex flex-row justify-between">
                    <h1 class="h6">
                        <span class="num-4"></span>k
                        <p>Halaman Dilihat</p>
                    </h1>

                    <div class="bg-teal-200 text-teal-700 border-teal-300 border w-10 h-10 rounded-full flex justify-center items-center">
                        <i class="fad fa-eye"></i>
                    </div>
                </div>                
                <div class="analytics_1"></div>
            </div>

            <div class="card">
                <div class="py-3 px-4 flex flex-row justify-between">                    
                    <h1 class="h6">
                        <span class="num-2"></span>k
                        <p>Pengunjung Baru</p>
                    </h1>

                    <div class="bg-indigo-200 text-indigo-700 border-indigo-300 border w-10 h-10 rounded-full flex justify-center items-center">
                        <i class="fad fa-users-crown"></i>
                    </div>
                </div>
                <div class="analytics_1"></div>
            </div>
        </div>
        
        <div class="shadow-lg rounded-lg overflow-hidden border">
            <div class="pt-5 px-5 bg-gray-50">
                <h1 class="h5">Data Participant Vote</h1>
            </div>
            <canvas class="p-10" id="chartPie"></canvas>
        </div>
    </div>
</div>
@endsection
@section('extraJS')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
{{-- bar --}}
<script>
    const labelsBarChart = [
        @foreach ($getLiveCount as $c)
            "{{$c->nama_ketua}}",
        @endforeach
    ];
    const dataBarChart = {
        labels: labelsBarChart,
        datasets: [
            {
                label: "Live Count",
                backgroundColor: "hsl(160, 84%, 39%)",
                borderColor: "hsl(160°, 85%, 21%)",
                data: [
                @foreach ($getLiveCount as $c)
                    "{{$c->total_suara}}",
                @endforeach],
            },
        ],
    };

    const configBarChart = {
        type: "bar",
        data: dataBarChart,
        options: {},
    };

    var chartBar = new Chart(
        document.getElementById("chartBar"),
        configBarChart
    );
</script>
{{-- pie --}}
<script>
    const dataPie = {
    labels: ["Sudah Memilih", "Belum Memilih"],
    datasets: [
        {
        label: "Total Pemilih",
        data: [{{$countVoted - 2}}, {{$countNotVoted}}],
        backgroundColor: [
            "rgb(16, 185, 129)",
            "rgb(188, 209, 202)",
        ],
        hoverOffset: 4,
        },
    ],
    };

    const configPie = {
    type: "pie",
    data: dataPie,
    options: {},
    };

    var chartBar = new Chart(document.getElementById("chartPie"), configPie);
</script>
@endsection