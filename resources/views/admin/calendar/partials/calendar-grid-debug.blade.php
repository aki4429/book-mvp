<div class="p-6">
  <!-- デバッグ情報 -->
  <div class="mb-4 p-4 bg-gray-100 rounded">
    <h3 class="font-bold">デバッグ情報:</h3>
    <p>カレンダー配列の型: {{ gettype($calendar) }}</p>
    <p>カレンダー配列の要素数: {{ is_array($calendar) ? count($calendar) : 'N/A' }}</p>
    @if(is_array($calendar) && count($calendar) > 0)
      <p>最初の週の要素数: {{ count($calendar[0]) }}</p>
      @if(count($calendar[0]) > 0)
        <p>最初の日のデータ構造:</p>
        <pre>{{ print_r(array_keys($calendar[0][0]), true) }}</pre>
      @endif
    @endif
  </div>

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
    @if(is_array($calendar))
      @foreach ($calendar as $week)
        @if(is_array($week))
          @foreach ($week as $day)
            @php
              $dateString = isset($day['date']) ? $day['date']->format('Y-m-d') : 'N/A';
              $isCurrentMonth = $day['isCurrentMonth'] ?? false;
              $isToday = $day['isToday'] ?? false;
              $hasSlots = $day['hasSlots'] ?? false;
              $totalSlots = $day['totalSlots'] ?? 0;
              $totalReservations = $day['totalReservations'] ?? 0;
            @endphp
            
            <div 
              class="calendar-day relative bg-white border border-gray-200 p-2 min-h-[100px] cursor-pointer {{ !$isCurrentMonth ? 'text-gray-400 bg-gray-50' : '' }} {{ $isToday ? 'ring-2 ring-blue-500' : '' }}"
              data-date="{{ $dateString }}"
            >
              <!-- 日付 -->
              <div class="flex justify-between items-start mb-2">
                <span class="text-sm font-medium">
                  {{ isset($day['date']) ? $day['date']->format('j') : '?' }}
                </span>
                
                @if ($hasSlots)
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $totalSlots }}
                  </span>
                @endif
              </div>
              
              <!-- 予約状況 -->
              @if ($hasSlots)
                <div class="text-xs text-gray-600">
                  予約: {{ $totalReservations }}件
                </div>
              @endif
            </div>
          @endforeach
        @else
          <div class="col-span-7 text-red-500">週データが配列ではありません</div>
        @endif
      @endforeach
    @else
      <div class="col-span-7 text-red-500">カレンダーデータが配列ではありません</div>
    @endif
  </div>
</div>
