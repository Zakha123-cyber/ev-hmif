@extends('admin.layouts.app')
@section('content')
<div>
    <div class="mt-6 grid grid-cols-2 gap-6 xl:grid-cols-1">
        <div class="shadow-lg rounded-lg overflow-hidden border">
            <div class="pt-5 px-5 bg-gray-50">
                <h1 class="h5">Data Perhitungan Sementara</h1>
            </div>
            <div class="flex justify-center items-center h-full">
                <canvas class="p-5 mb-10" id="chartBar"></canvas>
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