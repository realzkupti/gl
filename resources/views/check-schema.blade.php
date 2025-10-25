<!DOCTYPE html>
<html>
<head>
    <title>Check Database Schema</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h1 { color: #4ec9b0; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #3e3e3e; }
        th { background: #2d2d2d; color: #4ec9b0; }
        tr:nth-child(even) { background: #252525; }
    </style>
</head>
<body>
    <h1>Database Schema Check</h1>

    @php
        $conn = DB::connection('pgsql');

        // Get actual columns from menus table
        $columns = $conn->select("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_name = 'menus'
            ORDER BY ordinal_position
        ");
    @endphp

    <h2>Columns in 'menus' table:</h2>
    <table>
        <tr>
            <th>Column Name</th>
            <th>Data Type</th>
            <th>Nullable</th>
            <th>Default</th>
        </tr>
        @foreach($columns as $col)
            <tr>
                <td>{{ $col->column_name }}</td>
                <td>{{ $col->data_type }}</td>
                <td>{{ $col->is_nullable }}</td>
                <td>{{ $col->column_default ?? 'NULL' }}</td>
            </tr>
        @endforeach
    </table>

    @php
        // Sample data from menus
        $sampleMenus = $conn->table('menus')->limit(5)->get();
    @endphp

    <h2>Sample Menu Records (first 5):</h2>
    <pre>{{ json_encode($sampleMenus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</body>
</html>
