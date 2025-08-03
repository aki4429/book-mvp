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
          $availableSlots = $day['availableSlots'] ?? 0;
          
          // 管理者用のデータを準備
          $adminData = [
            'totalSlots' => $totalSlots,
            'totalReservations' => $totalReservations,
            'slots' => isset($day['slots']) ? $day['slots']->map(function($slot) {
              return [
                'id' => $slot->id,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'capacity' => $slot->capacity,
                'service_id' => $slot->service_id,
                'available' => $slot->available,
                'reservations' => isset($slot->reservations) ? $slot->reservations->toArray() : []
              ];
            })->toArray() : []
          ];
          
          // デバッグ: 8月3日の場合に情報を表示
          $isDebugDate = $dateString === '2025-08-03';
        @endphp
        
        <div 
          class="calendar-day relative bg-white border border-gray-200 p-2 min-h-[100px] cursor-pointer {{ !$isCurrentMonth ? 'text-gray-400 bg-gray-50' : '' }} {{ $isToday ? 'ring-2 ring-blue-500' : '' }} {{ $hasSlots ? 'bg-purple-50 border-purple-200' : '' }}"
          data-date="{{ $dateString }}"
          data-admin-slots="{{ json_encode($adminData) }}"
          onclick="selectDate('{{ $dateString }}')"
        >
          <!-- 日付 -->
          <div class="flex justify-between items-start mb-2">
            <span class="text-sm font-medium {{ $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' }}">
              {{ $day['date']->format('j') }}
            </span>
            
            @if ($hasSlots)
              <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                {{ $totalSlots }}
              </span>
            @endif
          </div>
          
          <!-- デバッグ情報（8月3日のみ） -->
          @if ($isDebugDate)
            <div class="text-xs bg-yellow-100 p-1 rounded mb-1">
              <div>hasSlots: {{ $hasSlots ? 'true' : 'false' }}</div>
              <div>totalSlots: {{ $totalSlots }}</div>
              <div>totalReservations: {{ $totalReservations }}</div>
              <div>availableSlots: {{ $availableSlots }}</div>
              <div>slots count: {{ isset($day['slots']) ? $day['slots']->count() : 'N/A' }}</div>
            </div>
          @endif
          
          <!-- 予約状況 -->
          @if ($hasSlots)
            <div class="space-y-1">
              <div class="text-xs text-gray-600">
                予約: {{ $totalReservations }}件
              </div>
              
              @if ($availableSlots > 0)
                <div class="text-xs text-green-600 font-medium">
                  空き: {{ $availableSlots }}枠
                </div>
              @else
                <div class="text-xs text-red-600 font-medium">
                  満席
                </div>
              @endif
              
              <!-- 時間枠の状態ドット -->
              @if(isset($day['slots']) && $day['slots']->count() > 0)
                <div class="flex flex-wrap gap-1 mt-2">
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
                  
                  @if ($day['slots']->count() > 6)
                    <span class="text-xs text-gray-500">+{{ $day['slots']->count() - 6 }}</span>
                  @endif
                </div>
              @endif
            </div>
          @endif
        </div>
      @endforeach
    @endforeach
  </div>
</div>
