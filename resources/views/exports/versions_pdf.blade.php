<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Versions Export</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #333; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h1>Content Versions Export</h1>
    <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Version</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($versions as $version)
            <tr>
                <td>{{ $version->id }}</td>
                <td>{{ $version->content->title }}</td>
                <td>{{ $version->version_number }}</td>
                <td>{{ ucfirst($version->status) }}</td>
                <td>{{ $version->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>