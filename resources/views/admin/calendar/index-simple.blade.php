<!DOCTYPE html>
<html>
<head>
    <title>管理者カレンダー</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>管理者カレンダー - テスト</h1>
    <p>現在の時刻: {{ now() }}</p>
    <p>カレンダーデータが存在する: {{ isset($calendarData) ? 'Yes' : 'No' }}</p>
    @if(isset($calendarData))
        <p>currentMonth: {{ $calendarData['currentMonth'] ?? 'N/A' }}</p>
    @endif
    
    <script>
        console.log('JavaScript loaded');
    </script>
</body>
</html>
