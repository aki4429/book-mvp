<!-- カレンダーグリッド -->
<div class="p-6">
  <!-- 曜日ヘッダー -->
  <div class="grid grid-cols-7 gap-px mb-2">
    <?php $__currentLoopData = ['日', '月', '火', '水', '木', '金', '土']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayOfWeek): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="bg-gray-50 p-2 text-center text-sm font-medium text-gray-700">
        <?php echo e($dayOfWeek); ?>

      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  <!-- カレンダー日付グリッド -->
  <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
    <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php $__currentLoopData = $week; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
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
        ?>
        
        <div class="calendar-day bg-white p-3 min-h-[80px] relative border border-gray-100 
                    <?php echo e($isCurrentMonth ? '' : 'text-gray-300'); ?>

                    <?php echo e($isToday ? 'bg-blue-50' : ''); ?>

                    <?php echo e($isReservable && $totalSlots > 0 ? 'cursor-pointer hover:bg-gray-50' : ''); ?>"
             <?php if($isReservable && $totalSlots > 0): ?>
               onclick="showTimeSlotTooltip(event, '<?php echo e($dateString); ?>', true)"
               onmouseenter="if (!isTooltipPinned) showTimeSlotTooltip(event, '<?php echo e($dateString); ?>')"
               onmouseleave="if (!isTooltipPinned) hideTimeSlotTooltip()"
               data-date="<?php echo e($dateString); ?>"
               data-slots="<?php echo e($daySlots->toJson()); ?>"
             <?php endif; ?>>
          
          <!-- 日付 -->
          <div class="text-sm font-medium <?php echo e($isToday ? 'text-blue-600' : ($isCurrentMonth ? 'text-gray-900' : 'text-gray-300')); ?>">
            <?php echo e($day->day); ?>

          </div>

          <!-- 予約状況インジケーター -->
          <?php if($isReservable && $totalSlots > 0): ?>
            <div class="absolute top-1 right-1">
              <?php if($reservationStatus === 'available'): ?>
                <div class="w-3 h-3 bg-green-500 rounded-full" title="空きあり"></div>
              <?php elseif($reservationStatus === 'limited'): ?>
                <div class="w-3 h-3 bg-yellow-500 rounded-full" title="残りわずか"></div>
              <?php elseif($reservationStatus === 'full'): ?>
                <div class="w-3 h-3 bg-red-500 rounded-full" title="満席"></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <!-- 予約枠数表示 -->
          <?php if($isReservable && $totalSlots > 0): ?>
            <div class="absolute bottom-1 left-1 text-xs text-gray-500">
              <?php echo e($availableSlots); ?>/<?php echo e($totalSlots); ?>

            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php /**PATH /var/www/html/app_3/resources/views/calendar/partials/calendar-grid.blade.php ENDPATH**/ ?>