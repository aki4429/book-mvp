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
        .tooltip {
            position: absolute;
            background: #1f2937;
            color: white;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            min-width: 400px;
            max-width: 600px;
        }
        .tooltip.pinned {
            border: 2px solid #3b82f6;
        }
        .tooltip-content {
            max-height: 400px;
            overflow-y: auto;
        }
        .time-slot-item {
            padding: 8px;
            margin: 4px 0;
            background: #374151;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .time-slot-item.available {
            border-left: 4px solid #10b981;
        }
        .time-slot-item.unavailable {
            border-left: 4px solid #ef4444;
        }
        .reservation-item {
            padding: 8px;
            margin: 4px 0;
            background: #1e40af;
            border-radius: 4px;
            border-left: 4px solid #60a5fa;
        }
        .btn {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            border: none;
            margin: 0 2px;
        }
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        .btn-create {
            background: #10b981;
            color: white;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
        }
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 24px;
            border-radius: 8px;
            min-width: 400px;
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
                <div class="flex space-x-4">
                    <a href="/admin/timeslots" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        時間枠管理
                    </a>
                    <a href="/admin/reservations" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        予約管理
                    </a>
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
            @include('admin.calendar.partials.calendar-grid', ['calendar' => $calendarData['calendar']])
        </div>

        <!-- 日時詳細表示エリア -->
        <div id="dayDetails" class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6" style="display: none;">
            <h3 id="selectedDate" class="text-lg font-semibold mb-4"></h3>
            <div id="daySlots"></div>
        </div>
    </div>

    <!-- 時間枠編集モーダル -->
    <div id="timeSlotModal" class="modal">
        <div class="modal-content">
            <h3 id="timeSlotModalTitle">時間枠編集</h3>
            <form id="timeSlotForm">
                <input type="hidden" id="timeSlotId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">開始時間</label>
                    <input type="time" id="startTime" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">終了時間</label>
                    <input type="time" id="endTime" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">定員</label>
                    <input type="number" id="capacity" min="1" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        <input type="checkbox" id="available"> 利用可能
                    </label>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelTimeSlot" class="bg-gray-500 text-white px-4 py-2 rounded">キャンセル</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">保存</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 予約編集モーダル -->
    <div id="reservationModal" class="modal">
        <div class="modal-content">
            <h3 id="reservationModalTitle">予約編集</h3>
            <form id="reservationForm">
                <input type="hidden" id="reservationId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">顧客名</label>
                    <input type="text" id="customerName" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">電話番号</label>
                    <input type="tel" id="customerPhone" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">メモ</label>
                    <textarea id="reservationNotes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">ステータス</label>
                    <select id="reservationStatus" class="mt-1 block w-full border-gray-300 rounded-md">
                        <option value="confirmed">確定</option>
                        <option value="pending">保留</option>
                        <option value="cancelled">キャンセル</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelReservation" class="bg-gray-500 text-white px-4 py-2 rounded">キャンセル</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">保存</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // グローバル変数
        let currentDate;
        let tooltip = null;
        let pinnedTooltip = null;
        let selectedDay = null;

        // 初期化
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin calendar initialized');
            const calendarData = @json($calendarData);
            currentDate = new Date(calendarData.currentMonth);
            
            setupEventListeners();
            updateCalendarDisplay();
        });

        // イベントリスナーの設定
        function setupEventListeners() {
            // 月変更ボタン
            document.getElementById('prevMonth').addEventListener('click', () => changeMonth(-1));
            document.getElementById('nextMonth').addEventListener('click', () => changeMonth(1));

            // カレンダー日付のクリック
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('calendar-day')) {
                    selectDay(e.target);
                }
            });

            // ツールチップの外側クリックで閉じる
            document.addEventListener('click', function(e) {
                if (tooltip && !tooltip.contains(e.target) && !e.target.classList.contains('calendar-day')) {
                    hideTooltip();
                }
            });

            // モーダル関連
            setupModalListeners();
        }

        // カレンダー表示の更新
        function updateCalendarDisplay() {
            document.getElementById('currentMonthYear').textContent = 
                currentDate.getFullYear() + '年' + (currentDate.getMonth() + 1) + '月';
        }

        // 月の変更
        function changeMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            
            fetch('/admin/calendar/change-month', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: new URLSearchParams({
                    year: currentDate.getFullYear(),
                    month: currentDate.getMonth() + 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.calendar) {
                    document.querySelector('.calendar-grid').innerHTML = data.calendar;
                    updateCalendarDisplay();
                }
            })
            .catch(error => {
                console.error('Error changing month:', error);
            });
        }

        // 日付選択
        function selectDay(dayElement) {
            // 前の選択をクリア
            if (selectedDay) {
                selectedDay.classList.remove('selected');
            }
            
            // 新しい選択
            selectedDay = dayElement;
            dayElement.classList.add('selected');
            
            const date = dayElement.dataset.date;
            if (date) {
                loadDaySlots(date);
            }
        }

        // 日付の時間枠とツールチップ表示
        function loadDaySlots(date) {
            fetch('/admin/calendar/day-slots?' + new URLSearchParams({ date: date }), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('selectedDate').textContent = data.date + 'の詳細';
                document.getElementById('daySlots').innerHTML = data.slots;
                document.getElementById('dayDetails').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading day slots:', error);
            });
        }

        // ツールチップ表示
        function showTooltip(element, content, x, y) {
            hideTooltip();
            
            tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.innerHTML = content;
            
            document.body.appendChild(tooltip);
            
            // 位置調整
            const rect = tooltip.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            if (x + rect.width > viewportWidth) {
                x = viewportWidth - rect.width - 10;
            }
            if (y + rect.height > viewportHeight) {
                y = y - rect.height - 10;
            }
            
            tooltip.style.left = x + 'px';
            tooltip.style.top = y + 'px';
        }

        // ツールチップ非表示
        function hideTooltip() {
            if (tooltip && tooltip !== pinnedTooltip) {
                document.body.removeChild(tooltip);
                tooltip = null;
            }
        }

        // モーダル関連の処理
        function setupModalListeners() {
            // 時間枠モーダル
            document.getElementById('cancelTimeSlot').addEventListener('click', () => {
                document.getElementById('timeSlotModal').style.display = 'none';
            });

            document.getElementById('timeSlotForm').addEventListener('submit', (e) => {
                e.preventDefault();
                saveTimeSlot();
            });

            // 予約モーダル
            document.getElementById('cancelReservation').addEventListener('click', () => {
                document.getElementById('reservationModal').style.display = 'none';
            });

            document.getElementById('reservationForm').addEventListener('submit', (e) => {
                e.preventDefault();
                saveReservation();
            });
        }

        // 時間枠の保存
        function saveTimeSlot() {
            const formData = new FormData(document.getElementById('timeSlotForm'));
            const id = document.getElementById('timeSlotId').value;
            const url = id ? `/admin/timeslots/${id}` : '/admin/timeslots';
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('timeSlotModal').style.display = 'none';
                    // カレンダーを再読み込み
                    if (selectedDay) {
                        loadDaySlots(selectedDay.dataset.date);
                    }
                } else {
                    alert('保存に失敗しました: ' + (data.message || '不明なエラー'));
                }
            })
            .catch(error => {
                console.error('Error saving time slot:', error);
                alert('保存中にエラーが発生しました');
            });
        }

        // 予約の保存
        function saveReservation() {
            const formData = new FormData(document.getElementById('reservationForm'));
            const id = document.getElementById('reservationId').value;

            fetch(`/admin/reservations/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('reservationModal').style.display = 'none';
                    // カレンダーを再読み込み
                    if (selectedDay) {
                        loadDaySlots(selectedDay.dataset.date);
                    }
                } else {
                    alert('保存に失敗しました: ' + (data.message || '不明なエラー'));
                }
            })
            .catch(error => {
                console.error('Error saving reservation:', error);
                alert('保存中にエラーが発生しました');
            });
        }

        // グローバル関数（パーシャルから呼び出される）
        window.editTimeSlot = function(id, startTime, endTime, capacity, available) {
            document.getElementById('timeSlotId').value = id;
            document.getElementById('startTime').value = startTime;
            document.getElementById('endTime').value = endTime;
            document.getElementById('capacity').value = capacity;
            document.getElementById('available').checked = available;
            document.getElementById('timeSlotModalTitle').textContent = '時間枠編集';
            document.getElementById('timeSlotModal').style.display = 'block';
        };

        window.editReservation = function(id, customerName, customerPhone, notes, status) {
            document.getElementById('reservationId').value = id;
            document.getElementById('customerName').value = customerName;
            document.getElementById('customerPhone').value = customerPhone;
            document.getElementById('reservationNotes').value = notes;
            document.getElementById('reservationStatus').value = status;
            document.getElementById('reservationModalTitle').textContent = '予約編集';
            document.getElementById('reservationModal').style.display = 'block';
        };

        window.deleteTimeSlot = function(id) {
            if (confirm('この時間枠を削除しますか？')) {
                fetch(`/admin/timeslots/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (selectedDay) {
                            loadDaySlots(selectedDay.dataset.date);
                        }
                    } else {
                        alert('削除に失敗しました: ' + (data.message || '不明なエラー'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting time slot:', error);
                    alert('削除中にエラーが発生しました');
                });
            }
        };

        window.deleteReservation = function(id) {
            if (confirm('この予約を削除しますか？')) {
                fetch(`/admin/reservations/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (selectedDay) {
                            loadDaySlots(selectedDay.dataset.date);
                        }
                    } else {
                        alert('削除に失敗しました: ' + (data.message || '不明なエラー'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting reservation:', error);
                    alert('削除中にエラーが発生しました');
                });
            }
        };
    </script>
</body>
</html>
