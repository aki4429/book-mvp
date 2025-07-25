<div>
  <div class="p-4">
    <!-- ヘッダー部分 -->
    <div class="flex justify-between items-center mb-4">
      <button wire:click="prevMonth" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded">←</button>
      <h2 class="text-lg font-bold">{{ $currentMonth->format('Y年n月') }}</h2>
      <button wire:click="nextMonth" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded">→</button>
    </div>

    <!-- 顧客ログイン/ダッシュボードリンク（管理者カレンダーでは非表示） -->
    @if (!$isAdmin)
      <div class="mb-4 text-right">
        @auth('customer')
          <div class="flex items-center justify-end space-x-4">
            <span class="text-sm text-gray-600">{{ Auth::guard('customer')->user()->name }}さん</span>
            <a href="{{ route('customer.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
              マイページ
            </a>
            <form method="POST" action="{{ route('customer.logout') }}" class="inline">
              @csrf
              <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                ログアウト
              </button>
            </form>
          </div>
        @else
          <a href="{{ route('customer.login') }}"
            class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">
            ログイン・新規登録
          </a>
        @endauth
      </div>
    @endif

    <div class="grid grid-cols-7 gap-2 text-center" wire:key="calendar-grid-{{ $year }}-{{ $month }}">
      @foreach (['日', '月', '火', '水', '木', '金', '土'] as $day)
        <div class="font-semibold">{{ $day }}</div>
      @endforeach

      @foreach ($weeks as $week)
        @foreach ($week as $day)
          @php
            $dateKey = $day->format('Y-m-d');
            $daySlots = $slots[$dateKey] ?? collect();
            $availableCount = $daySlots->where('available', true)->count();
            $totalSlots = $daySlots->count();

            // 予約可能日かどうかを判定
            $isReservationAllowed = $day->gte($reservationStartDate);

            // 満杯の時間枠数を計算（予約数 = 定員の時間枠）
            $fullSlots = $daySlots
                ->filter(function ($slot) {
                    return $slot->getCurrentReservationCount() >= $slot->capacity;
                })
                ->count();

            if ($isAdmin) {
                // 管理者向け詳細表示：満杯の時間枠数/総時間枠数
                if (!$isReservationAllowed) {
                    $mark = '×';
                    $detail = '予約不可';
                } elseif ($totalSlots === 0) {
                    $mark = '×';
                    $detail = '枠なし';
                } elseif ($availableCount === 0) {
                    $mark = '×';
                    $detail = $fullSlots . '/' . $totalSlots;
                } elseif ($availableCount <= 2) {
                    $mark = '△';
                    $detail = $fullSlots . '/' . $totalSlots;
                } else {
                    $mark = '○';
                    $detail = $fullSlots . '/' . $totalSlots;
                }
            } else {
                // 顧客向け表示：予約不可日は空白
                if (!$isReservationAllowed) {
                    $mark = '';
                } elseif ($totalSlots === 0) {
                    $mark = '';
                } elseif ($availableCount === 0) {
                    $mark = '満';
                } elseif ($availableCount <= 2) {
                    $mark = '△';
                } else {
                    $mark = '○';
                }
            }

            // セルの背景色とボーダー色を決定
            $cellClasses = 'border p-1 text-sm relative transition-colors duration-200';

            if ($pinnedDate === $dateKey) {
                $cellClasses .= ' bg-blue-100 border-blue-300';
            } else {
                // 予約可能日かどうかで表示を変更
                if (!$isReservationAllowed) {
                    // 予約不可日：グレー背景でクリック無効
                    $cellClasses .= ' bg-gray-200 border-gray-400 opacity-60';
                } elseif ($totalSlots === 0) {
                    // 枠なし：グレー背景
                    $cellClasses .= ' bg-gray-100 border-gray-300 hover:bg-gray-150';
                } elseif ($availableCount === 0) {
                    // 満員：薄い赤背景
                    $cellClasses .= ' bg-red-50 border-red-200 hover:bg-red-100';
                } elseif ($availableCount <= 2) {
                    // 残りわずか：薄いオレンジ背景
                    $cellClasses .= ' bg-orange-50 border-orange-200 hover:bg-orange-100';
                } else {
                    // 余裕あり：薄い青背景
                    $cellClasses .= ' bg-blue-50 border-blue-200 hover:bg-blue-100';
                }
            }
          @endphp

          <div class="{{ $cellClasses }}" wire:mouseenter="hoverDate('{{ $dateKey }}')"
            wire:mouseleave="unhoverDate()" wire:click="pinDate('{{ $dateKey }}')">
            <div>{{ $day->format('j') }}</div>

            @if ($isAdmin)
              {{-- 管理者向け詳細表示 --}}
              @if ($mark === '○' && $isReservationAllowed)
                <span class="text-blue-500 block cursor-pointer">{{ $mark }}</span>
              @elseif ($mark === '△' && $isReservationAllowed)
                <span class="text-orange-500 block cursor-pointer">{{ $mark }}</span>
              @else
                <span class="text-gray-400 block">{{ $mark }}</span>
              @endif
              <div class="text-xs text-gray-600 mt-1">{{ $detail }}</div>
            @else
              {{-- 顧客向け表示：予約不可日は空白 --}}
              @if ($mark === '○' && $isReservationAllowed)
                <span class="text-blue-500 block cursor-pointer">{{ $mark }}</span>
              @elseif ($mark === '△' && $isReservationAllowed)
                <span class="text-orange-500 block cursor-pointer">{{ $mark }}</span>
              @elseif ($mark === '満' && $isReservationAllowed)
                <span class="text-red-500 block">{{ $mark }}</span>
              @elseif ($mark !== '' && $isReservationAllowed)
                <span class="text-gray-400 block">{{ $mark }}</span>
              @endif
            @endif

            @if ($isAdmin)
              <button wire:click.stop="openTimeSlotManager('{{ $dateKey }}')"
                class="text-green-600 hover:text-green-800 underline text-xs block mt-1">
                時間枠管理
              </button>
            @endif
          </div>
        @endforeach
      @endforeach
    </div>

    {{-- ホバー時・固定時の予約可能時間表示 --}}
    @if ($hoveredDate)
      @php
        $hoveredSlots = $slots[$hoveredDate] ?? collect();
        $isPinned = $pinnedDate === $hoveredDate;
      @endphp
      <div
        class="mt-6 p-4 border rounded-lg shadow-sm {{ $isPinned ? 'bg-blue-100 border-blue-300' : 'bg-blue-50' }} transition-all duration-200"
        wire:mouseenter="hoverDate('{{ $hoveredDate }}')" wire:mouseleave="unhoverDate()">
        <div class="flex justify-between items-center mb-3">
          <h3 class="font-semibold text-gray-900">
            {{ \Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日') }}
            @if ($isAdmin)
              の時間枠詳細
            @else
              の予約可能時間
            @endif
          </h3>
          @if ($isPinned)
            <div class="flex items-center space-x-2">
              <span class="text-xs text-blue-600 bg-blue-200 px-2 py-1 rounded">固定表示中</span>
              <button wire:click="clearPin()" class="text-xs text-gray-500 hover:text-gray-700 underline">
                ✕ 閉じる
              </button>
            </div>
          @else
            <span class="text-xs text-gray-500">
              📌 クリックで固定表示
            </span>
          @endif
        </div>

        @if ($isAdmin)
          {{-- 管理者向け：すべての時間枠を表示 --}}
          @forelse ($hoveredSlots as $slot)
            @php
              $reservedCount = $slot->getCurrentReservationCount();
              $availableCount = $slot->capacity - $reservedCount;
              $isAvailable = $availableCount > 0;
            @endphp
            <div
              class="inline-block mr-2 mb-2 px-4 py-2 bg-white border rounded-lg text-sm {{ $isAvailable ? 'border-blue-200' : 'border-red-200' }}">
              <div class="font-medium {{ $isAvailable ? 'text-blue-700' : 'text-red-700' }}">
                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
              </div>
              <div class="text-xs text-gray-600">
                予約: {{ $reservedCount }}/{{ $slot->capacity }}名
              </div>
              @if ($isAvailable)
                <div class="text-xs text-blue-600">
                  空き: {{ $availableCount }}名
                </div>
              @else
                <div class="text-xs text-red-600">
                  満員
                </div>
              @endif
            </div>
          @empty
            <div class="text-gray-600 text-sm">この日は時間枠が設定されていません</div>
          @endforelse
        @else
          {{-- 顧客向け：予約可能な時間枠のみ表示 --}}
          @forelse ($hoveredSlots->where('available', true) as $slot)
            @auth('customer')
              <a href="{{ route('customer.reservations.create', ['slot_id' => $slot->id]) }}"
                class="inline-block mr-2 mb-2 px-4 py-2 bg-white border border-blue-200 hover:bg-blue-100 rounded-lg text-sm transition-colors duration-200 shadow-sm">
                <div class="font-medium text-blue-700">
                  {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                </div>
                <div class="text-xs text-gray-600">
                  空き: {{ $slot->capacity - $slot->getCurrentReservationCount() }}/{{ $slot->capacity }}名
                </div>
              </a>
            @else
              <a href="{{ route('customer.login') }}"
                class="inline-block mr-2 mb-2 px-4 py-2 bg-white border border-blue-200 hover:bg-blue-100 rounded-lg text-sm transition-colors duration-200 shadow-sm">
                <div class="font-medium text-blue-700">
                  {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                </div>
                <div class="text-xs text-gray-600">
                  空き: {{ $slot->capacity - $slot->getCurrentReservationCount() }}/{{ $slot->capacity }}名
                </div>
                <div class="text-xs text-orange-600 font-medium">
                  ログインが必要です
                </div>
              </a>
            @endauth
          @empty
            <div class="text-gray-600 text-sm">この日は予約可能な時間がありません</div>
          @endforelse
        @endif
      </div>
    @endif
  </div>

  @if ($isAdmin)
    <livewire:time-slot-form />
    <livewire:time-slot-manager />
  @endif
</div>
