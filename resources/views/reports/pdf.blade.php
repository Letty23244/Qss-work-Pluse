<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
  h1 { font-size: 18px; margin-bottom: 2px; }
  .muted { color: #777; margin-bottom: 14px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: right; }
  th:first-child, td:first-child { text-align: left; }
  th { background: #f0f0f5; }
  tfoot td { font-weight: bold; background: #fafafa; }
</style>
</head>
<body>
  <h1>{{ $title }}</h1>
  <div class="muted">Period: {{ $filters['from'] ?? '—' }} to {{ $filters['to'] ?? '—' }}</div>
  <table>
    <thead><tr><th>Group</th><th>Target</th><th>Produced</th><th>Pending</th><th>Remaining</th><th>Achievement</th></tr></thead>
    <tbody>
    @foreach ($rows as $r)
      <tr>
        <td>{{ $r['label'] }}</td><td>{{ $r['target'] }}</td><td>{{ $r['produced'] }}</td>
        <td>{{ $r['pending'] }}</td><td>{{ $r['remaining'] }}</td>
        <td>{{ $r['achievement'] !== null ? $r['achievement'].'%' : '—' }}</td>
      </tr>
    @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td>TOTAL</td><td>{{ $summary['target'] }}</td><td>{{ $summary['produced'] }}</td>
        <td>{{ $summary['pending'] }}</td><td>{{ $summary['remaining'] }}</td>
        <td>{{ $summary['achievement'] !== null ? $summary['achievement'].'%' : '—' }}</td>
      </tr>
    </tfoot>
  </table>
</body>
</html>
