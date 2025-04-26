@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Analytics: {{ $content->title }}</h1>
        <div>
            <div class="dropdown d-inline-block">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                    id="periodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $period === '7d' ? 'Last 7 Days' : 
                       ($period === '30d' ? 'Last 30 Days' : 
                       ($period === '90d' ? 'Last 90 Days' : 'Custom Range')) }}
                </button>
                <ul class="dropdown-menu" aria-labelledby="periodDropdown">
                    <li><a class="dropdown-item" href="?period=7d">Last 7 Days</a></li>
                    <li><a class="dropdown-item" href="?period=30d">Last 30 Days</a></li>
                    <li><a class="dropdown-item" href="?period=90d">Last 90 Days</a></li>
                </ul>
            </div>
            <a href="{{ route('contents.show', $content) }}" class="btn btn-secondary">
                Back to Content
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Views Over Time</h5>
                </div>
                <div class="card-body">
                    <canvas id="viewsChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Engagement Metrics</h5>
                </div>
                <div class="card-body">
                    <canvas id="engagementChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Export Analytics Data</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('analytics.export', $content) }}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Format</label>
                            <select name="format" class="form-control" required>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                                <option value="xlsx">Excel</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Views Chart
    new Chart(document.getElementById('viewsChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($viewsData)) !!},
            datasets: [{
                label: 'Views',
                data: {!! json_encode(array_values($viewsData)) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Engagement Chart
    new Chart(document.getElementById('engagementChart'), {
        type: 'bar',
        data: {
            labels: ['Avg. Time', 'Bounce Rate', 'Interactions'],
            datasets: [{
                label: 'Engagement',
                data: {!! json_encode(array_values($engagementData)) !!},
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(153, 102, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection