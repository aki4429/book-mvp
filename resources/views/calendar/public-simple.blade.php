<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>予約カレンダー</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
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
      background-color: #dcfce7 !important; /* 薄い緑色 */
      border-color: #16a34a !important; /* 緑色のボーダー */
      border-width: 2px !important;
    }
    
    .calendar-day.selected-date:hover {
      background-color: #bbf7d0 !important; /* ホバー時の少し濃い緑色 */
    }

    .calendar-day.clicked-date {
      background-color: #dbeafe !important; /* 薄い水色 */
      border-color: #3b82f6 !important; /* 青色のボーダー */
      border-width: 2px !important;
    }
    
    .calendar-day.clicked-date:hover {
      background-color: #bfdbfe !important; /* ホバー時の少し濃い水色 */
    }
  </style>
</head>

<body class="bg-gray-100">
  <div class="container mx-auto p-4">
    <!-- ヘッダー -->
    <div class="flex justify-between items-center mb-6 bg-white rounded-lg shadow-sm p-4">
      <div class="flex items-center space-x-4">
        <h1 class="text-2xl font-bold text-gray-800">予約カレンダー</h1>
      </div>
      
      <div class="flex items-center space-x-4">
        @auth
          <!-- 管理者ログイン中 -->
          <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  {{ substr(auth()->user()->name, 0, 1) }}
                </span>
              </div>
              <div class="text-sm">
                <p class="font-medium text-gray-700">{{ auth()->user()->name }}</p>
                <p class="text-gray-500">{{ auth()->user()->email }}</p>
                @if(auth()->user()->isAdmin())
                  <p class="text-blue-600 text-xs font-medium">管理者</p>
                @endif
              </div>
            </div>
            
            <!-- 管理者の場合は管理カレンダーリンクを表示 -->
            @if(auth()->user()->isAdmin())
              <a href="{{ route('admin.calendar.index') }}" 
                 class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>管理カレンダー</span>
              </a>
            @endif
            
            <!-- ログアウトボタン -->
            <form method="POST" action="{{ route('logout') }}" class="inline">
              @csrf
              <button type="submit" 
                      class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2"
                      onclick="return confirm('ログアウトしますか？')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                <span>ログアウト</span>
              </button>
            </form>
          </div>
        @elseif(auth('customer')->check())
          <!-- 顧客ログイン中 -->
          <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  {{ substr(auth('customer')->user()->name, 0, 1) }}
                </span>
              </div>
              <div class="text-sm">
                <p class="font-medium text-gray-700">{{ auth('customer')->user()->name }}</p>
                <p class="text-gray-500">{{ auth('customer')->user()->email }}</p>
                <p class="text-green-600 text-xs font-medium">顧客</p>
              </div>
            </div>
            
            <!-- マイページリンク -->
            <a href="{{ route('customer.dashboard') }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
              </svg>
              <span>マイページ</span>
            </a>
            
            <!-- プロフィールリンク -->
            <a href="{{ route('customer.profile.show') }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <span>プロフィール</span>
            </a>
            
            <!-- 予約管理リンク -->
            <a href="{{ route('customer.reservations.index') }}" 
               class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <span>予約管理</span>
            </a>
            
            <!-- ログアウトボタン -->
            <form method="POST" action="{{ route('customer.logout') }}" class="inline">
              @csrf
              <button type="submit" 
                      class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2"
                      onclick="return confirm('ログアウトしますか？')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                <span>ログアウト</span>
              </button>
            </form>
          </div>
        @else
          <!-- 未ログインの場合 -->
          <div class="flex items-center space-x-3">
            <a href="{{ route('customer.login') }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              ログイン
            </a>
            <a href="{{ route('register') }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              新規登録
            </a>
          </div>
        @endauth
      </div>
    </div>

    <!-- 成功メッセージ -->
    @if (session('success'))
      <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 mx-auto max-w-md">
        <div class="flex items-center">
          <div class="text-green-400 mr-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <p class="text-green-700 font-medium">{{ session('success') }}</p>
        </div>
      </div>
    @endif

    <!-- エラーメッセージ -->
    @if (session('error'))
      <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 mx-auto max-w-md">
        <div class="flex items-center">
          <div class="text-red-400 mr-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <p class="text-red-700 font-medium">{{ session('error') }}</p>
        </div>
      </div>
    @endif

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
            {{ $calendarData['currentMonth']->format('Y年m月') }}
          </h2>
          
          <button onclick="changeMonth(1)" class="btn-next p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>

        <!-- カレンダーグリッド -->
        <div id="calendar-container">
          @include('calendar.partials.calendar-grid', $calendarData)
        </div>
      </div>

      <!-- タイムスロット表示エリア -->
      <div id="timeslot-container" class="mt-6 bg-white rounded-lg shadow-sm p-6" style="display: none;">
        <div class="flex justify-between items-center mb-4">
          <h3 id="timeslot-title" class="text-lg font-medium text-gray-900">選択された日の予約枠</h3>
          <button onclick="hideTimeslotDetails()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div id="timeslot-content"></div>
      </div>
    </main>

    <!-- ツールチップ -->
    <div id="tooltip" class="fixed z-50 bg-gray-900 text-white text-sm rounded-lg p-4 shadow-xl max-w-xs opacity-0 transition-all duration-200 transform scale-95" style="display: none;">
      <div id="tooltip-content"></div>
      <div class="tooltip-arrow"></div>
    </div>

    <!-- フッター -->
    <footer class="bg-white border-t mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="text-center text-sm text-gray-500">
          <p>予約に関するお問い合わせは、お電話またはメールにてお気軽にご連絡ください。</p>
        </div>
      </div>
    </footer>
  </div>

  <script>
    let currentYear = {{ $currentYear }};
    let currentMonth = {{ $currentMonth }};
    let selectedDate = null;
    let tooltipTimeout;
    let isTooltipPinned = false; // ツールチップが固定されているかどうか

    // CSRFトークンをAjaxリクエストに含める
    document.addEventListener('DOMContentLoaded', function() {
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      
      // 全てのAjaxリクエストにCSRFトークンを含める
      if (window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
      }

      // ESCキーでツールチップを閉じる
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isTooltipPinned) {
          hideTimeSlotTooltip(true); // 強制的に非表示
        }
      });

      // ツールチップ外をクリックしたときに閉じる
      document.addEventListener('click', function(e) {
        const tooltip = document.getElementById('tooltip');
        const isTooltipClick = tooltip.contains(e.target);
        const isCalendarDayClick = e.target.closest('.calendar-day');
        
        if (isTooltipPinned && !isTooltipClick && !isCalendarDayClick) {
          hideTimeSlotTooltip(true); // 強制的に非表示
        }
      });
    });

    function changeMonth(direction) {
      currentMonth += direction;
      
      if (currentMonth > 12) {
        currentMonth = 1;
        currentYear++;
      } else if (currentMonth < 1) {
        currentMonth = 12;
        currentYear--;
      }

      // Ajaxリクエストでカレンダーを更新
      fetch(`{{ route('calendar.change-month') }}?year=${currentYear}&month=${currentMonth}`, {
        method: 'GET',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('calendar-container').innerHTML = data.html;
          document.getElementById('calendar-title').textContent = `${data.year}年${data.month}月`;
          // タイムスロット表示をリセット
          document.getElementById('timeslot-container').style.display = 'none';
          document.getElementById('timeslot-title').textContent = '選択された日の予約枠';
          hideTimeSlotTooltip();
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
    }

    function selectDate(date) {
      // 選択された日のタイムスロットを取得
      fetch(`{{ route('calendar.day-slots') }}?date=${date}`, {
        method: 'GET',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('timeslot-content').innerHTML = data.html;
          document.getElementById('timeslot-container').style.display = 'block';
          
          // 日付を日本語形式でフォーマット
          const selectedDate = new Date(date + 'T00:00:00');
          const month = selectedDate.getMonth() + 1;
          const day = selectedDate.getDate();
          const title = `${month}月${day}日の予約枠`;
          document.getElementById('timeslot-title').textContent = title;
          
          // 日付セルの選択状態を更新（以前の選択を解除）
          document.querySelectorAll('.calendar-day').forEach(day => {
            day.classList.remove('selected-date');
          });
          
          // 新しく選択された日付に選択状態を追加
          const selectedDateCell = document.querySelector(`[data-date="${date}"]`);
          if (selectedDateCell) {
            selectedDateCell.classList.add('selected-date');
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
    }

    function hideTimeslotDetails() {
      const container = document.getElementById('timeslot-container');
      container.style.display = 'none';
      
      // 選択状態をリセット
      document.querySelectorAll('.calendar-day').forEach(day => {
        day.classList.remove('selected-date');
      });
      
      selectedDate = null;
    }

    function showTimeSlotTooltip(event, date, isPinned = false) {
      clearTimeout(tooltipTimeout);
      
      const element = event.currentTarget;
      const slotsData = element.getAttribute('data-slots');
      
      if (!slotsData) return;
      
      let slots;
      try {
        slots = JSON.parse(slotsData);
      } catch (e) {
        console.error('Failed to parse slots data:', e);
        return;
      }

      if (slots.length === 0) return;

      // 固定状態を設定
      isTooltipPinned = isPinned;

      // クリックされた場合、日付セルの色を変更
      if (isPinned) {
        // 以前のクリック状態をリセット
        document.querySelectorAll('.calendar-day').forEach(day => {
          day.classList.remove('clicked-date');
        });
        
        // 新しくクリックされた日付に水色のスタイルを追加
        const clickedDateCell = document.querySelector(`[data-date="${date}"]`);
        if (clickedDateCell) {
          clickedDateCell.classList.add('clicked-date');
        }
      }

      // ツールチップの内容を生成
      const tooltip = document.getElementById('tooltip');
      const tooltipContent = document.getElementById('tooltip-content');
      
      let content = `<div class="font-semibold mb-2 flex justify-between items-center">
        <span>${formatDate(date)}</span>
        ${isPinned ? '<button onclick="hideTimeSlotTooltip(true)" class="text-gray-400 hover:text-white ml-2">✕</button>' : ''}
      </div>`;
      
      slots.forEach(slot => {
        const startTime = slot.start_time ? slot.start_time.substring(0, 5) : '';
        const endTime = slot.end_time ? slot.end_time.substring(0, 5) : '';
        const available = slot.available;
        const serviceId = slot.service_id || '';
        
        const statusClass = available ? 'text-green-300' : 'text-red-300';
        const statusText = available ? '空きあり' : '満席';
        
        // クリック可能な行として作成（予約可能な場合のみ）
        const clickable = available ? 'cursor-pointer hover:bg-gray-700 transition-colors duration-200 rounded' : '';
        const clickHandler = available ? `onclick="redirectToReservationFromTooltip(${slot.id})"` : '';
        
        content += `
          <div class="flex justify-between items-center py-2 px-2 rounded border-b border-gray-700 last:border-b-0 ${clickable}" ${clickHandler}>
            <div>
              <div class="font-medium">${startTime} - ${endTime}</div>
              ${serviceId ? `<div class="text-xs text-gray-300">サービスID: ${serviceId}</div>` : ''}
            </div>
            <div class="text-xs ${statusClass}">${statusText}</div>
          </div>
        `;
      });
      
      tooltipContent.innerHTML = content;

      // ツールチップの位置を計算
      const rect = element.getBoundingClientRect();
      const tooltipRect = tooltip.getBoundingClientRect();
      
      let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
      let top = rect.top - tooltipRect.height - 10;
      
      // 画面端の調整
      if (left < 10) left = 10;
      if (left + tooltipRect.width > window.innerWidth - 10) {
        left = window.innerWidth - tooltipRect.width - 10;
      }
      if (top < 10) {
        top = rect.bottom + 10;
      }

      tooltip.style.left = `${left}px`;
      tooltip.style.top = `${top}px`;
      tooltip.style.display = 'block';
      
      // フェードイン効果とスケール
      setTimeout(() => {
        tooltip.classList.remove('opacity-0', 'scale-95');
        tooltip.classList.add('opacity-100', 'scale-100');
        // ツールチップを表示したときにクリック可能にする
        tooltip.style.pointerEvents = 'auto';
      }, 10);
    }

    function hideTimeSlotTooltip(force = false) {
      const tooltip = document.getElementById('tooltip');
      
      // ツールチップが固定されている場合は強制的でない限り非表示にしない
      if (isTooltipPinned && !force) {
        return;
      }
      
      tooltipTimeout = setTimeout(() => {
        tooltip.classList.remove('opacity-100', 'scale-100');
        tooltip.classList.add('opacity-0', 'scale-95');
        // ツールチップを非表示にするときにクリックを無効にする
        tooltip.style.pointerEvents = 'none';
        isTooltipPinned = false; // 固定状態をリセット
        
        // クリック状態もリセット
        document.querySelectorAll('.calendar-day').forEach(day => {
          day.classList.remove('clicked-date');
        });
        
        setTimeout(() => {
          tooltip.style.display = 'none';
        }, 200);
      }, force ? 0 : 300); // 強制的な場合は即座に、そうでなければ300ms の遅延
    }

    function formatDate(dateString) {
      const date = new Date(dateString);
      const months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
      const days = ['日', '月', '火', '水', '木', '金', '土'];
      
      return `${date.getMonth() + 1}月${date.getDate()}日 (${days[date.getDay()]})`;
    }

    // 予約画面にリダイレクトするグローバル関数
    window.redirectToReservation = function(slotId) {
      console.log('Redirecting to reservation with slot ID:', slotId);
      const url = `{{ route('reservations.create') }}?slot_id=${slotId}`;
      console.log('URL:', url);
      window.location.href = url;
    };

    // ツールチップから予約画面にリダイレクトする関数
    window.redirectToReservationFromTooltip = function(slotId) {
      console.log('Redirecting to reservation from tooltip with slot ID:', slotId);
      // ツールチップを非表示にする
      const tooltip = document.getElementById('tooltip');
      tooltip.classList.remove('opacity-100', 'scale-100');
      tooltip.classList.add('opacity-0', 'scale-95');
      
      setTimeout(() => {
        tooltip.style.display = 'none';
        // 予約画面にリダイレクト
        window.location.href = `{{ route('reservations.create') }}?slot_id=${slotId}`;
      }, 200);
    };

    // ツールチップ自体にマウスが入った時は非表示にしない
    document.getElementById('tooltip').addEventListener('mouseenter', function() {
      if (!isTooltipPinned) {
        clearTimeout(tooltipTimeout);
      }
    });

    document.getElementById('tooltip').addEventListener('mouseleave', function() {
      if (!isTooltipPinned) {
        hideTimeSlotTooltip();
      }
    });
  </script>
</body>

</html>
