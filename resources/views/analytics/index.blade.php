@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Content Analytics Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Top Viewed Content (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Content</th>
                                <th>Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topContent as $content)
                            <tr>
                                <td>{{ $content->title }}</td>
                                <td>{{ $content->views_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>View Trends (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="viewTrendsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Content Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="contentTypeChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // View Trends Chart
    new Chart(document.getElementById('viewTrendsChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($viewTrends->pluck('date')) !!},
            datasets: [{
                label: 'Views',
                data: {!! json_encode($viewTrends->pluck('view_count')) !!},
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    // Content Type Chart
    new Chart(document.getElementById('contentTypeChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($contentTypes->pluck('type.name')) !!},
            datasets: [{
                data: {!! json_encode($contentTypes->pluck('count')) !!},
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                    '#9966FF', '#FF9F40', '#8AC249', '#EA5F89'
                ]
            }]
        }
    });
</script>
@endpush
@endsection