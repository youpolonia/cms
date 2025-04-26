@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Content Analytics Dashboard</h1>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Views Last 30 Days</h3>
                </div>
                <div class="card-body">
                    <canvas id="viewsChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Content by Status</h3>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Top Viewed Content</h3>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($topContent as $content)
                        <a href="{{ route('content-analytics.detail', $content) }}" 
                           class="list-group-item list-group-item-action">
                            {{ $content->title }}
                            <span class="badge bg-primary rounded-pill float-end">
                                {{ $content->views_count }} views
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Views by Hour (Average)</h3>
                </div>
                <div class="card-body">
                    <canvas id="hourlyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Views by day chart
    new Chart(document.getElementById('viewsChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($viewsByDay->pluck('date')) !!},
            datasets: [{
                label: 'Views',
                data: {!! json_encode($viewsByDay->pluck('views')) !!},
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    // Content by status chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($contentByStatus->pluck('status')) !!},
            datasets: [{
                data: {!! json_encode($contentByStatus->pluck('count')) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)'
                ]
            }]
        }
    });

    // Views by hour chart
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($viewsByHour->pluck('hour')) !!},
            datasets: [{
                label: 'Views',
                data: {!! json_encode($viewsByHour->pluck('views')) !!},
                backgroundColor: 'rgba(153, 102, 255, 0.7)'
            }]
        }
    });
</script>
@endpush
@endsection