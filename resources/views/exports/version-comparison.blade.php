<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Version Comparison Export - {{ $content->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { color: #333; font-size: 24px; }
        h2 { color: #555; font-size: 18px; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .added { color: green; }
        .removed { color: red; text-decoration: line-through; }
        .meta { margin-bottom: 30px; }
    </style>
</head>
<body>
    <h1>Version Comparison Report</h1>
    <div class="meta">
        <p><strong>Content:</strong> {{ $content->title }}</p>
        <p><strong>Generated:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
        <p><strong>Total Comparisons:</strong> {{ count($comparisons) }}</p>
    </div>

    @foreach($comparisons as $comparison)
    <div class="comparison">
        <h2>Comparison: Version {{ $comparison['from_version'] }} → Version {{ $comparison['to_version'] }}</h2>
        <p><strong>Date:</strong> {{ $comparison['timestamp']->format('Y-m-d H:i:s') }}</p>
        <p><strong>Author:</strong> {{ $comparison['author'] }}</p>
        
        <table>
            <tr>
                <th>Changes</th>
                <th>Added</th>
                <th>Removed</th>
            </tr>
            <tr>
                <td>{{ $comparison['changes'] }}</td>
                <td class="added">{{ $comparison['added'] }}</td>
                <td class="removed">{{ $comparison['removed'] }}</td>
            </tr>
        </table>
    </div>
    @endforeach
</body>
</html>