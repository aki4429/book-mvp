<div>
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <button wire:click="prevMonth" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
        ← 前月
      </button>
      <h2 class="text-lg font-bold"><?php echo e($currentMonth->format('Y年n月')); ?> 予約管理</h2>
      <button wire:click="nextMonth" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
        次月 →
      </button>
    </div>

    <!-- カレンダーグリッド -->
    <div class="grid grid-cols-7 gap-2 text-center"
      wire:key="reservation-calendar-<?php echo e($year); ?>-<?php echo e($month); ?>">
      <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ['日', '月', '火', '水', '木', '金', '土']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="font-semibold text-gray-600 py-2"><?php echo e($day); ?></div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

      <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $week; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
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
          ?>

          <div
            class="border min-h-[100px] p-2 text-sm relative hover:bg-blue-100 transition-colors duration-200 cursor-pointer <?php echo e($pinnedDate === $dateKey ? 'bg-blue-200 border-blue-400' : $bgColor); ?>"
            wire:click="pinDate('<?php echo e($dateKey); ?>')">

            <div class="font-medium <?php echo e($day->month !== $month ? 'text-gray-400' : 'text-gray-900'); ?>">
              <?php echo e($day->format('j')); ?>

            </div>

            <!--[if BLOCK]><![endif]--><?php if($totalSlots > 0): ?>
              <div class="mt-1 flex items-center justify-between">
                <span
                  class="text-xs px-1 py-0.5 rounded <?php echo e($indicator === '満'
                      ? 'bg-red-500 text-white'
                      : ($indicator === '△'
                          ? 'bg-yellow-500 text-white'
                          : 'bg-green-500 text-white')); ?>">
                  <?php echo e($indicator); ?>

                </span>
                <span class="text-xs text-gray-600">
                  <?php echo e($totalSlots); ?>枠
                </span>
              </div>

              <!--[if BLOCK]><![endif]--><?php if($reservationCount > 0): ?>
                <div class="mt-1">
                  <span class="inline-block px-2 py-1 bg-blue-500 text-white text-xs rounded-full">
                    <?php echo e($reservationCount); ?>件予約
                  </span>
                </div>
              <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

              <!-- 時間枠の簡易表示（最大2枠まで） -->
              <div class="mt-1 space-y-1">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $daySlots->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php
                    $slotReservations = $slot->reservations->count();
                    $available = $slotReservations < $slot->capacity;
                  ?>
                  <div class="text-xs p-1 rounded border <?php echo e($available ? 'bg-white' : 'bg-gray-100'); ?>">
                    <div class="truncate">
                      <?php echo e($slot->start_time_as_object->format('H:i')); ?>

                      <!--[if BLOCK]><![endif]--><?php if($slotReservations > 0): ?>
                        <span class="text-blue-600">(<?php echo e($slotReservations); ?>件)</span>
                      <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if($totalSlots > 2): ?>
                  <div class="text-xs text-blue-600 font-medium">
                    +<?php echo e($totalSlots - 2); ?>枠
                  </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
              </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!-- ホバー時・固定時の時間枠と予約詳細表示 -->
    <!--[if BLOCK]><![endif]--><?php if($hoveredDate && ($hoveredSlots->isNotEmpty() || $hoveredReservations->isNotEmpty())): ?>
      <?php
        $isPinned = $pinnedDate === $hoveredDate;
      ?>
      <div
        class="mt-6 p-4 border rounded-lg shadow-sm <?php echo e($isPinned ? 'bg-blue-100 border-blue-300' : 'bg-blue-50'); ?> transition-all duration-200">

        <div class="flex justify-between items-center mb-3">
          <h3 class="font-semibold text-gray-900">
            <?php echo e(\Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日')); ?> の時間枠と予約
          </h3>
          <!--[if BLOCK]><![endif]--><?php if($isPinned): ?>
            <div class="flex items-center space-x-2">
              <span class="text-xs text-blue-600 bg-blue-200 px-2 py-1 rounded">固定表示中</span>
              <button wire:click="clearPin()" class="text-xs text-gray-500 hover:text-gray-700 underline">
                ✕ 閉じる
              </button>
            </div>
          <?php else: ?>
            <span class="text-xs text-gray-500">
              📌 クリックで固定表示
            </span>
          <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!--[if BLOCK]><![endif]--><?php if($hoveredSlots->isNotEmpty()): ?>
          <!-- 時間枠一覧 -->
          <div class="space-y-3">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $hoveredSlots->sortBy(['date', 'start_time']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $slotReservations = $slot->reservations;
                $availableCount = $slot->capacity - $slotReservations->count();
                $isAvailable = $availableCount > 0;
              ?>
              <div
                class="bg-white p-4 rounded-lg border <?php echo e($isAvailable ? 'border-green-200' : 'border-red-200'); ?> hover:shadow-sm transition-shadow"
                wire:key="slot-<?php echo e($slot->id); ?>">
                <div class="flex justify-between items-start mb-3">
                  <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-2">
                      <div class="font-medium text-blue-700 text-lg">
                        <?php echo e($slot->start_time_as_object->format('H:i')); ?> -
                        <?php echo e($slot->end_time_as_object->format('H:i')); ?>

                      </div>
                      <div
                        class="px-2 py-1 text-xs rounded-full <?php echo e($isAvailable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                        <?php echo e($isAvailable ? '空きあり' : '満席'); ?>

                      </div>
                      <div class="text-sm text-gray-600">
                        空き: <?php echo e($availableCount); ?>/<?php echo e($slot->capacity); ?>名
                      </div>
                      <!--[if BLOCK]><![endif]--><?php if($isAvailable): ?>
                        <button wire:click="createReservationForSlot(<?php echo e($slot->id); ?>)"
                          wire:key="book-btn-<?php echo e($slot->id); ?>-<?php echo e($index); ?>"
                          data-slot-id="<?php echo e($slot->id); ?>"
                          class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                          ＋ 予約
                        </button>
                      <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if($slotReservations->isNotEmpty()): ?>
                      <div class="space-y-2">
                        <h4 class="text-sm font-medium text-gray-700">予約一覧:</h4>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $slotReservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <div class="p-2 bg-gray-50 rounded" wire:key="reservation-<?php echo e($reservation->id); ?>">
                            <div class="flex items-center justify-between">
                              <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">
                                  <?php echo e($reservation->customer->name); ?>

                                </div>
                                <!--[if BLOCK]><![endif]--><?php if($reservation->customer->email): ?>
                                  <div class="text-xs text-gray-600">
                                    📧 <?php echo e($reservation->customer->email); ?>

                                  </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <!--[if BLOCK]><![endif]--><?php if($reservation->customer->phone): ?>
                                  <div class="text-xs text-gray-600">
                                    📞 <?php echo e($reservation->customer->phone); ?>

                                  </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <!--[if BLOCK]><![endif]--><?php if($reservation->notes): ?>
                                  <div class="text-xs text-gray-600 mt-1 p-1 bg-white rounded">
                                    備考: <?php echo e($reservation->notes); ?>

                                  </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                              </div>

                              <div class="flex items-center space-x-2 ml-2">
                                <div
                                  class="px-2 py-1 text-xs rounded <?php echo e($reservation->status === 'confirmed'
                                      ? 'bg-green-100 text-green-800'
                                      : ($reservation->status === 'pending'
                                          ? 'bg-yellow-100 text-yellow-800'
                                          : ($reservation->status === 'canceled'
                                              ? 'bg-red-100 text-red-800'
                                              : 'bg-gray-100 text-gray-800'))); ?>">
                                  <?php echo e($reservation->status === 'confirmed'
                                      ? '確定'
                                      : ($reservation->status === 'pending'
                                          ? '保留'
                                          : ($reservation->status === 'canceled'
                                              ? 'キャンセル'
                                              : $reservation->status))); ?>

                                </div>
                              </div>
                            </div>

                            <div class="flex space-x-2 mt-2">
                              <button wire:click="showReservationDetail(<?php echo e($reservation->id); ?>)"
                                wire:key="detail-btn-<?php echo e($reservation->id); ?>"
                                class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                                詳細
                              </button>
                              <button wire:click="editReservation(<?php echo e($reservation->id); ?>)"
                                wire:key="edit-btn-<?php echo e($reservation->id); ?>"
                                class="px-2 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-colors">
                                編集
                              </button>
                              <button wire:click="deleteReservation(<?php echo e($reservation->id); ?>)"
                                wire:key="delete-btn-<?php echo e($reservation->id); ?>" wire:confirm="この予約を削除してもよろしいですか？"
                                class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition-colors">
                                削除
                              </button>
                            </div>
                          </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                      </div>
                    <?php else: ?>
                      <div class="text-sm text-gray-500 italic">
                        この時間枠には予約がありません
                      </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                  </div>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
          </div>
        <?php else: ?>
          <div class="text-center text-gray-500 py-4">
            <?php echo e(\Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日')); ?> には時間枠が設定されていません
          </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
      </div>
    <?php elseif($hoveredDate): ?>
      <div class="mt-6 p-4 border rounded-lg bg-gray-50 text-center text-gray-500">
        <?php echo e(\Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日')); ?> には時間枠が設定されていません
      </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- 予約詳細モーダル -->
    <!--[if BLOCK]><![endif]--><?php if($showReservationDetails && $selectedReservation): ?>
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
                <?php echo e($selectedReservation->timeSlot->date->format('Y年n月j日')); ?>

                <?php echo e($selectedReservation->timeSlot->start_time_as_object->format('H:i')); ?> -
                <?php echo e($selectedReservation->timeSlot->end_time_as_object->format('H:i')); ?>

              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">お客様</label>
              <p class="text-sm text-gray-900"><?php echo e($selectedReservation->customer->name); ?></p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">ステータス</label>
              <p class="text-sm text-gray-900"><?php echo e($selectedReservation->status); ?></p>
            </div>

            <!--[if BLOCK]><![endif]--><?php if($selectedReservation->notes): ?>
              <div>
                <label class="block text-sm font-medium text-gray-700">備考</label>
                <p class="text-sm text-gray-900"><?php echo e($selectedReservation->notes); ?></p>
              </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
          </div>

          <div class="flex justify-end space-x-2 mt-6">
            <button wire:click="editReservation(<?php echo e($selectedReservation->id); ?>)"
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
  </div>
</div>
<?php /**PATH /var/www/html/app_3/resources/views/livewire/reservation-calendar.blade.php ENDPATH**/ ?>