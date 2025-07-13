<div class="p-4">
  <div class="flex justify-between items-center mb-4">
    <button wire:click="prevMonth">←</button>
    <h2 class="text-lg font-bold">{{ $currentMonth->format('Y年n月') }}</h2>
    <button wire:click="nextMonth">→</button>
  </div>

  <div class="grid grid-cols-7 gap-2 text-center">
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
          $isSelected = $selectedDate === $dateKey;
        @endphp

        <div class="border p-1 text-sm {{ $isSelected ? 'bg-blue-100' : '' }}">
          <div>{{ $day->format('j') }}</div>

          @if ($mark === '○' || $mark === '△')
            <button wire:click="selectDate('{{ $dateKey }}')" class="text-blue-500 underline">
              {{ $mark }}
            </button>
          @else
            <span class="text-gray-400">{{ $mark }}</span>
          @endif
        </div>
      @endforeach
    @endforeach
  </div>

  {{-- 選択された日付のスロット一覧 --}}
  @if ($selectedDate)
    <div class="mt-6 p-4 border rounded shadow">
      <h3 class="font-semibold mb-2">{{ \Carbon\Carbon::parse($selectedDate)->format('Y年n月j日') }} の予約可能時間</h3>

      @foreach ($slots[$selectedDate] ?? [] as $slot)
        @if ($slot->available)
          <a href="{{ route('reservations.create', ['slot_id' => $slot->id]) }}"
            class="inline-block mr-2 mb-2 px-3 py-1 bg-blue-100 hover:bg-blue-300 rounded text-sm">
            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}
          </a>
        @endif
      @endforeach
    </div>
  @endif
</div>
