<!-- カレンダーグリッド -->
<div class="p-6">
  <!-- 曜日ヘッダー -->
  <div class="grid grid-cols-7 gap-px mb-2">
    @foreach(['日', '月', '火', '水', '木', '金', '土'] as $dayOfWeek)
      <div class="bg-gray-50 p-2 text-center text-sm font-medium text-gray-700">
        {{ $dayOfWeek }}
      </div>
    @endforeach
  </div>

  <!-- カレンダー日付グリッド -->
  <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
    @foreach($weeks as $week)
      @foreach($week as $day)
        @php
          $isCurrentMonth = $day->month == $currentMonth->month;
          $isToday = $day->isToday();
          $dateString = $day->format('Y-m-d');
          $daySlots = $slots->get($dateString, collect());
          $isReservable = $day->gte($reservationStartDate) && $isCurrentMonth;
          
          // 予約状況の判定
          $availableSlots = $daySlots->where('available', true)->count();
          $totalSlots = $daySlots->count();
          $reservationStatus = '';
          
          if ($totalSlots > 0 && $isReservable) {
            if ($availableSlots == 0) {
              $reservationStatus = 'full'; // 満席
            } elseif ($availableSlots <= $totalSlots * 0.3) {
              $reservationStatus = 'limited'; // 残りわずか
            } else {
              $reservationStatus = 'available'; // 空きあり
            }
          }
        @endphp
        
        <div class="calendar-day bg-white p-3 min-h-[80px] relative border border-gray-100 
                    {{ $isCurrentMonth ? '' : 'text-gray-300' }}
                    {{ $isToday ? 'bg-blue-50' : '' }}
                    {{ $isReservable && $totalSlots > 0 ? 'cursor-pointer hover:bg-gray-50' : '' }}"
             @if($isReservable && $totalSlots > 0)
               onclick="selectDate('{{ $dateString }}')"
               onmouseenter="if (!isTooltipPinned) showTimeSlotTooltip(event, '{{ $dateString }}')"
               onmouseleave="if (!isTooltipPinned) hideTimeSlotTooltip()"
               data-date="{{ $dateString }}"
               data-slots="{{ $daySlots->toJson() }}"
             @endif>
          
          <!-- 日付 -->
          <div class="text-sm font-medium {{ $isToday ? 'text-blue-600' : ($isCurrentMonth ? 'text-gray-900' : 'text-gray-300') }}">
            {{ $day->day }}
          </div>

          <!-- 予約状況インジケーター -->
          @if($isReservable && $totalSlots > 0)
            <div class="absolute top-1 right-1">
              @if($reservationStatus === 'available')
                <div class="w-3 h-3 bg-green-500 rounded-full" title="空きあり"></div>
              @elseif($reservationStatus === 'limited')
                <div class="w-3 h-3 bg-yellow-500 rounded-full" title="残りわずか"></div>
              @elseif($reservationStatus === 'full')
                <div class="w-3 h-3 bg-red-500 rounded-full" title="満席"></div>
              @endif
            </div>
          @endif

          <!-- 予約枠数表示 -->
          @if($isReservable && $totalSlots > 0)
            <div class="absolute bottom-1 left-1 text-xs text-gray-500">
              {{ $availableSlots }}/{{ $totalSlots }}
            </div>
          @endif
        </div>
      @endforeach
    @endforeach
  </div>
</div>
