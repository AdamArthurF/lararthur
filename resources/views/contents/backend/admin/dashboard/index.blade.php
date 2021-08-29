@extends('layouts.backend.app')

@section('breadcumb')
<h3>@yield('title')</h3>
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('frontend.home.index') }}">Home</a>
    </li>
    <li class="breadcrumb-item">Backend</li>
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item"><a href="{{ route('backend.admin.dashboard.index') }}">Dashboard</a></li>
</ol>
@endsection

@section('content')
    <div class="card">
    <div class="card-header">
        <h5>Selamat Datang di Halaman Admin</h5>
        <div class="card-header-right">
            <ul class="list-unstyled card-option">
                <li><i class="fa fa-spin fa-cog"></i></li>
                <li><i class="icofont icofont-maximize full-card"></i></li>
                <li><i class="icofont icofont-minus minimize-card"></i></li>
                <li><i class="icofont icofont-refresh reload-card"></i></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div id="chart_dashboard"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let chart;
    // Document Ready
    $(() => {
        /**
         * Keperluan Chart dengan ApexCharts
         */
        // ================================================== //
        chart = new ApexCharts(document.querySelector("#chart_dashboard"), {
            chart: {
                type: 'area'
            },
            stroke: {
                curve: 'smooth'
            },
            series: [{
                name: 'series1',
                data: [6, 20, 15, 40, 18, 20, 18, 23, 18, 35, 30, 55, 0]
            }, {
                name: 'series2',
                data: [2, 22, 35, 32, 40, 25, 50, 38, 42, 28, 20, 45, 0]
            }],
            xaxis: {
                type: 'category',
                low: 0,
                offsetX: 0,
                offsetY: 0,
                // show: false,
                categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan"],
                labels: {
                    low: 0,
                    offsetX: 0,
                    // show: false,
                },
                axisBorder: {
                    low: 0,
                    offsetX: 0,
                    // show: false,
                },
            },
            markers: {
                strokeWidth: 3,
                colors: "#ffffff",
                strokeColors: [CubaAdminConfig.primary, CubaAdminConfig.secondary],
                hover: {
                    size: 6,
                }
            },
            yaxis: {
                low: 0,
                offsetX: 0,
                offsetY: 0,
                // show: false,
                labels: {
                    low: 0,
                    offsetX: 0,
                    // show: false,
                },
                axisBorder: {
                    low: 0,
                    offsetX: 0,
                    // show: false,
                },
            },
            grid: {
                // show: false,
                padding: {
                    left: 0,
                    right: 0,
                    bottom: -15,
                    top: -40
                }
            },
            colors: [CubaAdminConfig.primary, CubaAdminConfig.secondary],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.5,
                    stops: [0, 80, 100]
                }
            },
            legend: {
                // show: false,
            },
            tooltip: {
                x: {
                    format: 'MM'
                },
            },
        });
        chart.render();
    })
</script>
@endpush