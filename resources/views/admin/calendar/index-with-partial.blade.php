<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者カレンダー</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .calendar-day {
            min-height: 120px;
            border: 1px solid #e2e8f0;
            position: relative;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .calendar-day:hover {
            background-color: #f8fafc;
        }
        .calendar-day.other-month {
            color: #cbd5e0;
            background-color: #f7fafc;
        }
        .calendar-day.selected {
            background-color: #e6f3ff;
            border-color: #3b82f6;
        }
        .slot-indicator {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .reservation-count {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: #f59e0b;
            color: white;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 11px;
        }
    </style>
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
            @if(isset($calendarData['calendar']) && is_array($calendarData['calendar']))
                @include('admin.calendar.partials.calendar-grid-simple', ['calendar' => $calendarData['calendar']])
            @else
                <p class="text-red-500">カレンダーデータが正しく読み込まれませんでした。</p>
                <p>デバッグ情報: カレンダーデータ要素数: {{ count($calendarData['calendar'] ?? []) }}</p>
            @endif
        </div>
    </div>

    <script>
        console.log('Admin calendar with partial loaded');
        console.log('Calendar data:', @json($calendarData));
    </script>
</body>
</html>
