<div>
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <button wire:click="prevMonth">←</button>
      <h2 class="text-lg font-bold">{{ $currentMonth->format('Y年n月') }}</h2>
      <button wire:click="nextMonth">→</button>
    </div>

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

            if ($availableCount === 0) {
                $mark = '×';
            } elseif ($availableCount <= 2) {
                $mark = '△';
            } else {
                $mark = '○';
            }
          @endphp

          <div
            class="border p-1 text-sm relative hover:bg-blue-50 transition-colors duration-200 {{ $pinnedDate === $dateKey ? 'bg-blue-100 border-blue-300' : '' }}"
            wire:mouseenter="hoverDate('{{ $dateKey }}')" wire:mouseleave="unhoverDate()"
            wire:click="pinDate('{{ $dateKey }}')">
            <div>{{ $day->format('j') }}</div>

            @if ($mark === '○' || $mark === '△')
              <span class="text-blue-500 block cursor-pointer">{{ $mark }}</span>
            @else
              <span class="text-gray-400 block">{{ $mark }}</span>
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
            {{ \Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日') }} の予約可能時間
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

        @forelse ($hoveredSlots->where('available', true) as $slot)
          <a href="{{ route('reservations.create', ['slot_id' => $slot->id]) }}"
            class="inline-block mr-2 mb-2 px-4 py-2 bg-white border border-blue-200 hover:bg-blue-100 rounded-lg text-sm transition-colors duration-200 shadow-sm">
            <div class="font-medium text-blue-700">
              {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
            </div>
            <div class="text-xs text-gray-600">
              空き: {{ $slot->capacity - $slot->getCurrentReservationCount() }}/{{ $slot->capacity }}名
            </div>
          </a>
        @empty
          <div class="text-gray-600 text-sm">この日は予約可能な時間がありません</div>
        @endforelse
      </div>
    @endif
  </div>

  @if ($isAdmin)
    <livewire:time-slot-form />
    <livewire:time-slot-manager />
  @endif
</div>
