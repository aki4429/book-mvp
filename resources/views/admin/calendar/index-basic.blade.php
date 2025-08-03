<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者カレンダー</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- ヘッダー -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">管理者カレンダー</h1>
                    <p class="text-gray-600 mt-1">時間枠の管理と予約の編集ができます</p>
                </div>
            </div>
        </div>

        <!-- カレンダーコントロール -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center">
                <button id="prevMonth" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg">
                    ← 前の月
                </button>
                <h2 id="currentMonthYear" class="text-xl font-semibold">
                    {{ $calendarData['currentMonth']->format('Y年m月') }}
                </h2>
                <button id="nextMonth" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg">
                    次の月 →
                </button>
            </div>
        </div>

        <!-- カレンダーグリッド -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <p>カレンダーグリッドをここに表示</p>
            <p>現在の月: {{ $calendarData['currentMonth']->format('Y年m月') }}</p>
            <p>カレンダーデータ要素数: {{ count($calendarData['calendar'] ?? []) }}</p>
        </div>
    </div>

    <script>
        console.log('Admin calendar basic loaded');
        console.log('Calendar data:', @json($calendarData));
    </script>
</body>
</html>
