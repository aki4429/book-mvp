<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>🔥🔥🔥 [18:52 TEST] 管理者カレンダー STANDALONE v5.0 - <?php echo e(date("H:i:s")); ?> - 時間枠・予約管理</title>
  
  <!-- VITE COMPLETELY DISABLED FOR DEBUGGING -->
  <script src="https://cdn.tailwindcss.com"></script>>🔥🔥🔥 [18:52 TEST] 管理者カレンダー STANDALONE v5.0 - <?php echo e(date('H:i:s')); ?> - 時間枠・予約管理</title>
  
  <!-- VITE COMPLETELY DISABLED FOR DEBUGGING -->
  <script src="https://cdn.tailwindcss.com"></script>tml>
  <html lang="ja">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>🔥🔥🔥 [18:52 TEST] 管理者カレンダー STANDALONE v5.0 - <?php echo e(date('H:i:s')); ?> - 時間枠・予約管理</title>
    
    <!-- VITE COMPLETELY DISABLED FOR DEBUGGING -->
    <link href="https://cdn.tailwindcss.com/3.3.0/tailwind.min.css" rel="stylesheet">
    <style>
      .tooltip-arrow {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-bottom: 6px solid #1f2937;
      }

      .calendar-day:hover .tooltip-trigger {
        opacity: 1;
      }

      .calendar-day {
        transition: all 0.2s ease-in-out;
      }

      .calendar-day:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      }

      .calendar-day.selected-date {
        background-color: #ddd6fe !important;
        /* 薄い紫色 */
        border-color: #7c3aed !important;
        /* 紫色のボーダー */
        border-width: 2px !important;
      }

      .calendar-day.selected-date:hover {
        background-color: #c4b5fd !important;
        /* ホバー時の少し濃い紫色 */
      }

      .calendar-day.clicked-date {
        background-color: #fef3c7 !important;
        /* 薄い黄色 */
        border-color: #f59e0b !important;
        /* オレンジ色のボーダー */
        border-width: 2px !important;
      }

      .calendar-day.clicked-date:hover {
        background-color: #fde68a !important;
        /* ホバー時の少し濃い黄色 */
      }

      .modal {
        display: none;
        transition: all 0.3s ease-in-out;
      }

      .modal.show {
        display: flex;
      }

      .modal-content {
        transform: scale(0.7);
        opacity: 0;
        transition: all 0.3s ease-in-out;
      }

      .modal.show .modal-content {
        transform: scale(1);
        opacity: 1;
      }
    </style>
  </head>

  <body class="bg-gray-100">
    <!-- 🔥🔥🔥 TEST MARKER 18:52 - IF YOU SEE THIS, NEW FILE IS LOADED 🔥🔥🔥 -->
    <div class="container mx-auto p-4">
      <!-- ヘッダー -->
      <div class="flex justify-between items-center mb-6 bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center space-x-4">
          <h1 class="text-2xl font-bold text-gray-800">管理者カレンダー</h1>
          <span class="text-sm text-gray-500 bg-purple-100 px-3 py-1 rounded-full">時間枠・予約管理</span>
        </div>

        <div class="flex items-center space-x-4">
          <!-- ユーザー情報 -->
          <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  <?php echo e(substr(auth()->user()->name, 0, 1)); ?>

                </span>
              </div>
              <div class="text-sm">
                <p class="font-medium text-gray-700"><?php echo e(auth()->user()->name); ?></p>
                <p class="text-purple-600 text-xs font-medium">管理者</p>
              </div>
            </div>

            <!-- カレンダー切り替えボタン -->
            <a href="<?php echo e(route('calendar.public')); ?>"
              class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <span>一般カレンダー</span>
            </a>

            <!-- ログアウトボタン -->
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
              <?php echo csrf_field(); ?>
              <button type="submit"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2"
                onclick="return confirm('ログアウトしますか？')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                  </path>
                </svg>
                <span>ログアウト</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- 成功・エラーメッセージ -->
      <div id="message-container" class="mb-6"></div>

      <!-- メインコンテンツ -->
      <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm">

          <!-- カレンダーヘッダー（月の選択） -->
          <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <button onclick="changeMonth(-1)" class="btn-prev p-2 rounded-lg hover:bg-gray-100">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>

            <h2 class="text-xl font-semibold text-gray-900" id="calendar-title">
              <?php echo e($calendarData['currentMonth']->format('Y年m月')); ?>

            </h2>

            <button onclick="changeMonth(1)" class="btn-next p-2 rounded-lg hover:bg-gray-100">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>

          <!-- カレンダーグリッド -->
          <div id="calendar-container">
            <?php echo $__env->make('admin.calendar.partials.calendar-grid-simple', $calendarData, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
          </div>
        </div>

        <!-- 選択日の詳細表示エリア -->
        <div id="day-details-container" class="mt-6 bg-white rounded-lg shadow-sm p-6" style="display: none;">
          <div class="flex justify-between items-center mb-4">
            <h3 id="day-details-title" class="text-lg font-medium text-gray-900">選択された日の管理</h3>
            <button onclick="addTimeSlot()"
              class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
              </svg>
              <span>時間枠追加</span>
            </button>
          </div>
          <div id="day-details-content"></div>
        </div>
      </main>

      <!-- ツールチップ -->
      <div id="tooltip"
        class="fixed z-50 bg-gray-900 text-white text-sm rounded-lg p-4 shadow-xl max-w-xs opacity-0 transition-all duration-200 transform scale-95"
        style="display: none;">
        <div id="tooltip-content"></div>
        <div class="tooltip-arrow"></div>
      </div>

      <!-- 時間枠編集モーダル -->
      <div id="timeslot-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white modal-content">
          <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900" id="timeslot-modal-title">時間枠編集</h3>
              <button onclick="closeTimeslotModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                  </path>
                </svg>
              </button>
            </div>

            <form id="timeslot-form" action="javascript:void(0);" method="get" onsubmit="return false;">
              <input type="hidden" id="timeslot-id" name="timeslot_id">
              <input type="hidden" id="selected-date" name="date">

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">開始時間</label>
                <input type="time" id="start-time" name="start_time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">終了時間</label>
                <input type="time" id="end-time" name="end_time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">定員</label>
                <input type="number" id="capacity" name="capacity" min="1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">サービスID</label>
                <input type="text" id="service-id" name="service_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div class="mb-6">
                <label class="flex items-center">
                  <input type="checkbox" id="available" name="available"
                    class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                  <span class="ml-2 text-sm text-gray-700">予約受付可能</span>
                </label>
              </div>

              <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeTimeslotModal()"
                  class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                  キャンセル
                </button>
                <button type="button" onclick="handleTimeslotFormSubmit()"
                  class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                  保存
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- 予約編集モーダル -->
      <div id="reservation-modal"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white modal-content">
          <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">予約編集</h3>
              <button onclick="closeReservationModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                  </path>
                </svg>
              </button>
            </div>

            <form id="reservation-form" action="javascript:void(0);" onsubmit="handleReservationSubmit(event)">
              <input type="hidden" id="reservation-id" name="reservation_id">

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">顧客名</label>
                <input type="text" id="customer-name" name="customer_name"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">メールアドレス</label>
                <input type="email" id="customer-email" name="customer_email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">電話番号</label>
                <input type="tel" id="customer-phone" name="customer_phone"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">ステータス</label>
                <select id="reservation-status" name="status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                  <option value="pending">待機中</option>
                  <option value="confirmed">確定</option>
                  <option value="cancelled">キャンセル</option>
                </select>
              </div>

              <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeReservationModal()"
                  class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                  キャンセル
                </button>
                <button type="submit"
                  class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                  保存
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- フッター -->
      <footer class="bg-white border-t mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="text-center text-sm text-gray-500">
            <p>管理者専用カレンダー - 時間枠と予約の管理</p>
          </div>
        </div>
      </footer>
    </div>

    <script>
      // === CONFIGURATION DATA FOR STANDALONE SCRIPT ===
      window.calendarData = {
        currentYear: <?php echo e($currentYear ?? date('Y')); ?>,
        currentMonth: <?php echo e($currentMonthNum ?? date('n')); ?>

      };

    window.routes = {
      changeMonth: '<?php echo e(route("admin.calendar.change-month")); ?>',
      daySlots: '<?php echo e(route("admin.calendar.day-slots")); ?>',
      timeslotBase: '<?php echo e(url("/admin/timeslots")); ?>',
      timeslotCreate: '<?php echo e(route("admin.calendar.timeslots.create")); ?>',
      reservationBase: '<?php echo e(url("/admin/reservations")); ?>'
    };      console.log('�🔥🔥 [18:52 TEST] Configuration loaded for standalone script');
    </script>

    <!-- STANDALONE JAVASCRIPT FILE - NO CACHE -->
    <script src="/admin-calendar-v5.js?v=<?php echo e(time()); ?>&nocache=<?php echo e(rand(1000, 9999)); ?>"></script>
  </body>

  </html>
<?php /**PATH /var/www/html/app_3/resources/views/admin/calendar/index.blade.php ENDPATH**/ ?>