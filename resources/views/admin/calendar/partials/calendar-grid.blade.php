<div class="p-6">
  <!-- 曜日ヘッダー -->
  <div class="grid grid-cols-7 gap-1 mb-2">
    <div class="text-center text-sm font-medium text-gray-700 py-2">日</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">月</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">火</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">水</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">木</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">金</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">土</div>
  </div>

  <!-- カレンダーグリッド -->
  <div class="grid grid-cols-7 gap-1">
    @foreach ($calendar as $week)
      @foreach ($week as $day)
        @php
          $dateString = $day['date']->format('Y-m-d');
          $isCurrentMonth = $day['isCurrentMonth'];
          $isToday = $day['isToday'];
          $hasSlots = $day['hasSlots'];
          $totalSlots = $day['totalSlots'];
          $totalReservations = $day['totalReservations'];
          $availableSlots = $day['availableSlots'];
          
          // 管理者用データを準備
          $adminData = [
            'totalSlots' => $totalSlots,
            'totalReservations' => $totalReservations,
            'availableSlots' => $availableSlots,
            'slots' => isset($day['slots']) ? $day['slots']->map(function($slot) {
              return [
                'id' => $slot->id,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'capacity' => $slot->capacity,
                'service_id' => $slot->service_id,
                'available' => $slot->available,
                'reservations' => isset($slot->reservations) ? $slot->reservations->map(function($reservation) {
                  return [
                    'id' => $reservation->id,
                    'customer_name' => optional($reservation->customer)->name ?? '',
                    'customer_email' => optional($reservation->customer)->email ?? '',
                    'customer_phone' => optional($reservation->customer)->phone ?? '',
                    'status' => $reservation->status
                  ];
                }) : collect([])
              ];
            }) : collect([])
          ];
          
          // 基本的なCSクラス
          $dayClasses = ['calendar-day', 'relative', 'bg-white', 'border', 'border-gray-200', 'p-2', 'min-h-[100px]', 'cursor-pointer', 'transition-all', 'duration-200'];
          
          // 月の状態によるスタイル
          if (!$isCurrentMonth) {
            $dayClasses[] = 'text-gray-400';
            $dayClasses[] = 'bg-gray-50';
          }
          
          // 今日の日付
          if ($isToday) {
            $dayClasses[] = 'ring-2';
            $dayClasses[] = 'ring-blue-500';
          }
          
          // 時間枠がある日のスタイル
          if ($hasSlots) {
            $dayClasses[] = 'bg-purple-50';
            $dayClasses[] = 'border-purple-200';
          }
          
          // ホバー効果
          $dayClasses[] = 'hover:bg-purple-100';
          $dayClasses[] = 'hover:border-purple-300';
        @endphp
        
        <div 
          class="{{ implode(' ', $dayClasses) }}"
          data-date="{{ $dateString }}"
          data-admin-slots="{{ json_encode($adminData) }}"
          onclick="selectDate('{{ $dateString }}')"
          onmouseenter="showTooltip(event, '{{ $dateString }}')"
          onmouseleave="hideTooltip()"
          onmousedown="showTooltip(event, '{{ $dateString }}', true)"
        >
          <!-- 日付 -->
          <div class="flex justify-between items-start mb-2">
            <span class="text-sm font-medium {{ $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' }}">
              {{ $day['date']->format('j') }}
            </span>
            
            @if ($hasSlots)
              <div class="flex items-center space-x-1">
                <!-- 時間枠数インジケーター -->
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                  {{ $totalSlots }}
                </span>
              </div>
            @endif
          </div>
          
          <!-- 予約状況サマリー -->
          @if ($hasSlots)
            <div class="space-y-1">
              <!-- 予約件数 -->
              <div class="text-xs text-gray-600">
                予約: {{ $totalReservations }}件
              </div>
              
              <!-- 空き状況 -->
              @if ($availableSlots > 0)
                <div class="text-xs text-green-600 font-medium">
                  空き: {{ $availableSlots }}枠
                </div>
              @else
                <div class="text-xs text-red-600 font-medium">
                  満席
                </div>
              @endif
              
              <!-- 時間枠のドット表示 -->
              <div class="flex flex-wrap gap-1 mt-2">
                @if(isset($day['slots']))
                  @foreach ($day['slots']->take(6) as $slot)
                    @php
                      $reservationCount = isset($slot->reservations) ? $slot->reservations->count() : 0;
                      $capacity = $slot->capacity;
                      $isFull = $reservationCount >= $capacity;
                      $isAvailable = $slot->available;
                      
                      $dotClass = 'w-2 h-2 rounded-full ';
                      if (!$isAvailable) {
                        $dotClass .= 'bg-gray-400'; // 停止中
                      } elseif ($isFull) {
                        $dotClass .= 'bg-red-400'; // 満席
                      } elseif ($reservationCount > 0) {
                        $dotClass .= 'bg-yellow-400'; // 一部予約
                      } else {
                        $dotClass .= 'bg-green-400'; // 空き
                      }
                    @endphp
                    
                    <div class="{{ $dotClass }}" title="{{ $slot->start_time }} - {{ $slot->end_time }} ({{ $reservationCount }}/{{ $capacity }})"></div>
                  @endforeach
                  
                  <!-- 6個以上の場合は省略表示 -->
                  @if ($day['slots']->count() > 6)
                    <span class="text-xs text-gray-500">+{{ $day['slots']->count() - 6 }}</span>
                  @endif
                @endif
              </div>
            </div>
          @endif
        </div>
      @endforeach
    @endforeach
  </div>
</div>
