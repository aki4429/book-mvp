<div>
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <button wire:click="prevMonth" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
        ← 前月
      </button>
      <h2 class="text-lg font-bold">{{ $currentMonth->format('Y年n月') }} 予約管理</h2>
      <button wire:click="nextMonth" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
        次月 →
      </button>
    </div>

    <!-- カレンダーグリッド -->
    <div class="grid grid-cols-7 gap-2 text-center"
      wire:key="reservation-calendar-{{ $year }}-{{ $month }}">
      @foreach (['日', '月', '火', '水', '木', '金', '土'] as $day)
        <div class="font-semibold text-gray-600 py-2">{{ $day }}</div>
      @endforeach

      @foreach ($weeks as $week)
        @foreach ($week as $day)
          @php
            $dateKey = $day->format('Y-m-d');
            $daySlots = $timeSlots[$dateKey] ?? collect();
            $dayReservations = $reservations[$dateKey] ?? collect();
            $totalSlots = $daySlots->count();
            $reservationCount = $dayReservations->count();

            // 空き状況による表示の決定
            if ($totalSlots === 0) {
                $indicator = '';
                $bgColor = 'bg-gray-50';
                $statusText = '時間枠なし';
            } else {
                $availableSlots = 0;
                foreach ($daySlots as $slot) {
                    $slotReservations = $slot->reservations->count();
                    if ($slotReservations < $slot->capacity) {
                        $availableSlots++;
                    }
                }

                if ($availableSlots === 0) {
                    $indicator = '満';
                    $bgColor = 'bg-red-50 border-red-200';
                    $statusText = '満席';
                } elseif ($availableSlots <= 2) {
                    $indicator = '△';
                    $bgColor = 'bg-yellow-50 border-yellow-200';
                    $statusText = '残りわずか';
                } else {
                    $indicator = '○';
                    $bgColor = 'bg-green-50 border-green-200';
                    $statusText = '空きあり';
                }
            }
          @endphp

          <div
            class="border min-h-[100px] p-2 text-sm relative hover:bg-blue-100 transition-colors duration-200 cursor-pointer {{ $pinnedDate === $dateKey ? 'bg-blue-200 border-blue-400' : $bgColor }}"
            wire:mouseenter="hoverDate('{{ $dateKey }}')" wire:mouseleave="unhoverDate()"
            wire:click="pinDate('{{ $dateKey }}')">

            <div class="font-medium {{ $day->month !== $month ? 'text-gray-400' : 'text-gray-900' }}">
              {{ $day->format('j') }}
            </div>

            @if ($totalSlots > 0)
              <div class="mt-1 flex items-center justify-between">
                <span
                  class="text-xs px-1 py-0.5 rounded {{ $indicator === '満'
                      ? 'bg-red-500 text-white'
                      : ($indicator === '△'
                          ? 'bg-yellow-500 text-white'
                          : 'bg-green-500 text-white') }}">
                  {{ $indicator }}
                </span>
                <span class="text-xs text-gray-600">
                  {{ $totalSlots }}枠
                </span>
              </div>

              @if ($reservationCount > 0)
                <div class="mt-1">
                  <span class="inline-block px-2 py-1 bg-blue-500 text-white text-xs rounded-full">
                    {{ $reservationCount }}件予約
                  </span>
                </div>
              @endif

              <!-- 時間枠の簡易表示（最大2枠まで） -->
              <div class="mt-1 space-y-1">
                @foreach ($daySlots->take(2) as $slot)
                  @php
                    $slotReservations = $slot->reservations->count();
                    $available = $slotReservations < $slot->capacity;
                  @endphp
                  <div class="text-xs p-1 rounded border {{ $available ? 'bg-white' : 'bg-gray-100' }}">
                    <div class="truncate">
                      {{ $slot->start_time_as_object->format('H:i') }}
                      @if ($slotReservations > 0)
                        <span class="text-blue-600">({{ $slotReservations }}件)</span>
                      @endif
                    </div>
                  </div>
                @endforeach

                @if ($totalSlots > 2)
                  <div class="text-xs text-blue-600 font-medium">
                    +{{ $totalSlots - 2 }}枠
                  </div>
                @endif
              </div>
            @endif
          </div>
        @endforeach
      @endforeach
    </div>

    <!-- ホバー時・固定時の時間枠と予約詳細表示 -->
    @if ($hoveredDate && ($hoveredSlots->isNotEmpty() || $hoveredReservations->isNotEmpty()))
      @php
        $isPinned = $pinnedDate === $hoveredDate;
      @endphp
      <div
        class="mt-6 p-4 border rounded-lg shadow-sm {{ $isPinned ? 'bg-blue-100 border-blue-300' : 'bg-blue-50' }} transition-all duration-200"
        wire:mouseenter="hoverDate('{{ $hoveredDate }}')" wire:mouseleave="unhoverDate()">

        <div class="flex justify-between items-center mb-3">
          <h3 class="font-semibold text-gray-900">
            {{ \Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日') }} の時間枠と予約
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

        @if ($hoveredSlots->isNotEmpty())
          <!-- 時間枠一覧 -->
          <div class="space-y-3">
            @foreach ($hoveredSlots->sortBy(['date', 'start_time']) as $index => $slot)
              @php
                $slotReservations = $slot->reservations;
                $availableCount = $slot->capacity - $slotReservations->count();
                $isAvailable = $availableCount > 0;
              @endphp
              <div
                class="bg-white p-4 rounded-lg border {{ $isAvailable ? 'border-green-200' : 'border-red-200' }} hover:shadow-sm transition-shadow"
                wire:key="slot-{{ $slot->id }}">
                <div class="flex justify-between items-start mb-3">
                  <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-2">
                      <div class="font-medium text-blue-700 text-lg">
                        {{ $slot->start_time_as_object->format('H:i') }} -
                        {{ $slot->end_time_as_object->format('H:i') }}
                      </div>
                      <div
                        class="px-2 py-1 text-xs rounded-full {{ $isAvailable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $isAvailable ? '空きあり' : '満席' }}
                      </div>
                      <div class="text-sm text-gray-600">
                        空き: {{ $availableCount }}/{{ $slot->capacity }}名
                      </div>
                      @if ($isAvailable)
                        <button wire:click="createReservationForSlot({{ $slot->id }})"
                          wire:key="book-btn-{{ $slot->id }}-{{ $index }}"
                          data-slot-id="{{ $slot->id }}"
                          class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                          ＋ 予約
                        </button>
                      @endif
                    </div>

                    @if ($slotReservations->isNotEmpty())
                      <div class="space-y-2">
                        <h4 class="text-sm font-medium text-gray-700">予約一覧:</h4>
                        @foreach ($slotReservations as $reservation)
                          <div class="p-2 bg-gray-50 rounded" wire:key="reservation-{{ $reservation->id }}">
                            <div class="flex items-center justify-between">
                              <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">
                                  {{ $reservation->customer->name }}
                                </div>
                                @if ($reservation->customer->email)
                                  <div class="text-xs text-gray-600">
                                    📧 {{ $reservation->customer->email }}
                                  </div>
                                @endif
                                @if ($reservation->customer->phone)
                                  <div class="text-xs text-gray-600">
                                    📞 {{ $reservation->customer->phone }}
                                  </div>
                                @endif
                                @if ($reservation->notes)
                                  <div class="text-xs text-gray-600 mt-1 p-1 bg-white rounded">
                                    備考: {{ $reservation->notes }}
                                  </div>
                                @endif
                              </div>

                              <div class="flex items-center space-x-2 ml-2">
                                <div
                                  class="px-2 py-1 text-xs rounded {{ $reservation->status === 'confirmed'
                                      ? 'bg-green-100 text-green-800'
                                      : ($reservation->status === 'pending'
                                          ? 'bg-yellow-100 text-yellow-800'
                                          : ($reservation->status === 'canceled'
                                              ? 'bg-red-100 text-red-800'
                                              : 'bg-gray-100 text-gray-800')) }}">
                                  {{ $reservation->status === 'confirmed'
                                      ? '確定'
                                      : ($reservation->status === 'pending'
                                          ? '保留'
                                          : ($reservation->status === 'canceled'
                                              ? 'キャンセル'
                                              : $reservation->status)) }}
                                </div>
                              </div>
                            </div>

                            <div class="flex space-x-2 mt-2">
                              <button wire:click="showReservationDetail({{ $reservation->id }})"
                                wire:key="detail-btn-{{ $reservation->id }}"
                                class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                                詳細
                              </button>
                              <button wire:click="editReservation({{ $reservation->id }})"
                                wire:key="edit-btn-{{ $reservation->id }}"
                                class="px-2 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-colors">
                                編集
                              </button>
                              <button wire:click="deleteReservation({{ $reservation->id }})"
                                wire:key="delete-btn-{{ $reservation->id }}" wire:confirm="この予約を削除してもよろしいですか？"
                                class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition-colors">
                                削除
                              </button>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    @else
                      <div class="text-sm text-gray-500 italic">
                        この時間枠には予約がありません
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center text-gray-500 py-4">
            {{ \Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日') }} には時間枠が設定されていません
          </div>
        @endif
      </div>
    @elseif ($hoveredDate)
      <div class="mt-6 p-4 border rounded-lg bg-gray-50 text-center text-gray-500">
        {{ \Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日') }} には時間枠が設定されていません
      </div>
    @endif

    <!-- 予約詳細モーダル -->
    @if ($showReservationDetails && $selectedReservation)
      <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        wire:click="$set('showReservationDetails', false)">
        <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4" wire:click.stop>
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">予約詳細</h3>
            <button wire:click="$set('showReservationDetails', false)" class="text-gray-400 hover:text-gray-600">
              ✕
            </button>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700">日時</label>
              <p class="text-sm text-gray-900">
                {{ $selectedReservation->timeSlot->date->format('Y年n月j日') }}
                {{ $selectedReservation->timeSlot->start_time_as_object->format('H:i') }} -
                {{ $selectedReservation->timeSlot->end_time_as_object->format('H:i') }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">お客様</label>
              <p class="text-sm text-gray-900">{{ $selectedReservation->customer->name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">ステータス</label>
              <p class="text-sm text-gray-900">{{ $selectedReservation->status }}</p>
            </div>

            @if ($selectedReservation->notes)
              <div>
                <label class="block text-sm font-medium text-gray-700">備考</label>
                <p class="text-sm text-gray-900">{{ $selectedReservation->notes }}</p>
              </div>
            @endif
          </div>

          <div class="flex justify-end space-x-2 mt-6">
            <button wire:click="editReservation({{ $selectedReservation->id }})"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
              編集
            </button>
            <button wire:click="$set('showReservationDetails', false)"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
              閉じる
            </button>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
