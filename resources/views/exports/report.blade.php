<!DOCTYPE html>
<html>
<head>
    <title>Report: {{ $template->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f5f5f5; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .header { margin-bottom: 20px; }
        .footer { margin-top: 20px; font-size: 0.8em; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $template->name }}</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($template->fields as $field)
                <th>{{ $field['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                @foreach($template->fields as $field)
                <td>{{ $row[$field['name']] ?? '' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Template ID: {{ $template->id }}</p>
    </div>
</body>
</html>