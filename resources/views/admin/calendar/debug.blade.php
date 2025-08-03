<!DOCTYPE html>
<html>
<head>
    <title>管理者カレンダー - デバッグ</title>
</head>
<body>
    <h1>管理者カレンダー - デバッグページ</h1>
    <p>現在の時刻: {{ now() }}</p>
    <p>カレンダーデータが存在する: {{ isset($calendarData) ? 'Yes' : 'No' }}</p>
    @if(isset($calendarData))
        <p>currentMonth: {{ $calendarData['currentMonth'] ?? 'N/A' }}</p>
        <p>calendar配列の要素数: {{ count($calendarData['calendar'] ?? []) }}</p>
    @endif
    
    <hr>
    <h2>Raw Calendar Data:</h2>
    <pre>{{ print_r($calendarData ?? 'calendarData not set', true) }}</pre>
</body>
</html>
